<?php
  require_once("team.php");

  /**
   * \brief Class handling match scheduling
   *
   * Class handles division of players into classes, match scheduling and 
   * simulates coin toss games between scheduled pairs of players.
   *
   * Assignment of players into teams is completely random.
   *
   * Matchups are created in a way allowing maximum number of matches between 
   * players at the same time aiming for maximum level of randomness to the 
   * process and allowing for matchups between each individual pair of 
   * players.
   *
   * To achieve this the match assignment process is divided in two steps:
   *
   * 1) Most populated team is selected first and then any other team with 
   * more than one available player is selected and a matchup is created 
   * between two randomly selected players from these teams. This approach 
   * allows every pair of players to create a matchup at the same time 
   * ensuring that at the end of this stage each class retains a single player 
   * allowed for selection for the next step of the process.
   *
   * 2) This stage starts with all teams having one player available for 
   * selection therefore the pairs are created entirely randomly between 
   * ramaining players.
   */
  class scheduler
  {
    /** \var array of teams $teams
     *
     * \brief Stores are teams created for the tournament
     *
     * Array of teams created in the contructor
     */
    public $teams=[];

    /**
     * \var int $number_of_teams
     * 
     * \brief number of teams in the game
     *
     * number of teams created for the tournament
     */
    public $number_of_teams = 0;

    /**
     * \var int $number_of_players
     * 
     * \brief number of players in the game
     *
     * number of players created for the tournament
     */
    public $number_of_players = 0;

    /**
     * \var associative_array $rounds
     *
     * \brief Pairs created for each round of matches
     *
     * Each entry in this array represent a round of mathes scheduled.
     *
     * For each round of matched an associative 
     * array
     * =
     * (
     *  "pairs" => array(player1, player2), 
     *  "unpaired_players" => array(player1, player2, ...)
     * )
     * stores all pairs created and possible left unpaired players who will no 
     * play any match in this round.
     */
    protected $rounds = [];

    /**
     * \var int $current_round
     *
     * \brief Round counter
     *
     * Increased every time a new round of games is scheduled.
     */
    protected $current_round = 0;

    /**
     * \param N number of players to be created for the tournament
     *
     * \param team_colours Array of string with teams colours eg. ["red", 
     * "blue"]
     *
     * \brief Creates $N players assigned to teams defined with $team_colours
     *
     * Constructor create an instance of team class for each value fo $team_colours
     * and $N players are constructed and randomly assigned to one of the 
     * teams
     */
    public function __construct($N, $team_colours)
    {
      $this -> number_of_players = $N;

      foreach($team_colours as $colour) 
      {
        $this -> teams[] = new team($colour);
        $this -> number_of_teams++;
      }

      $players = [];
      while(player::getCount() < $N) $players[] = new player();

      $this -> assign_players($players);
    }

    /**
     * \return void
     *
     * \param players list of players to be assigned between teams
     *
     * \brief Assigns array of players into teams created for the tournament
     *
     * No restrictions are applied in the process so it stays completely 
     * random.
     */
    private function assign_players($players)
    {
      foreach($players as $p)
      {
        $i = rand(0, $this -> number_of_teams - 1);
        $this -> teams[$i] -> assign_player($p);
      }
    }

    /**
     * \return array Array of teams available for player selection process
     *
     * \param limit Threshold value for number of players
     *
     * \briefs Returns array of teams with number of players available greater 
     * than $limit
     *
     * The match making process consists of two part as descried in the class 
     * description. In the first step teams are still available if the number 
     * of available players is greater than 1 (the function is called with 
     * $limit=1) and in the second process the teams are considered available 
     * for player selection if they have any non-zero number of players 
     * (function called with $limit=0)
     */
    private function get_available_teams($limit)
    {
      $avail_t = [];
      foreach($this -> teams as $t)
      {
        if($t -> players_left > $limit) $avail_t[] = $t;
      }
      return $avail_t;
    }

    /**
     * \return team Team with highest number of players still available for 
     * selection
     *
     * \param teams List of teams to search through
     *
     * \brief Takes array of teams and return the one with highest number of 
     * players
     *
     * If more than one team has the highest number of available players 
     * functions choses one of them randomly.
     *
     * If no team maches the criteria than return False
     */
    public function select_most_populated_team($teams)
    {
      $max_players = 0;
      foreach($teams as $t) $max_players = max($max_players, $t -> players_left);

      $temp_t = [];
      foreach($teams as $t)
      { 
        if($max_players == $t -> players_left)
        {
          $temp_t[] = $t;
        }
      }
      if(sizeof($temp_t) == 0) return False;

      $i = rand(0, sizeof($temp_t) - 1);
      return $temp_t[$i];
    }

    /**
     * \param teams Array of teams to select from
     *
     * \param t Team already selected for the matchup
     *
     * \return Instance of team. Random team that can create a matchup with 
     * the first selected team $t
     *
     * In the match making process first the most populated team is chosen and 
     * the second team must be able to create a matchup with the first team. 
     *
     * This function removes the first selected team from the list of 
     * available teams and also removes any other team that cannot create a 
     * matchup with team $t.
     *
     * For example if the only available pair of players between teams was 
     * chosen in the previous round there is no possible pair of players to 
     * form a matchup and the other team is removed from the list of available 
     * teams.
     */
    public function select_second_team($teams, $t)
    {
      // teams left for selection
      $teams_left = $teams;

      // remove the team $t from the list. This is the team that has been 
      // already selected and matchup between players from the same teams are 
      // not allowed
      $key = array_search($t, $teams_left);
      unset($teams_left[$key]);
      // update indices in the array
      $teams_left = array_values($teams_left);

      // this is a list of teams that cannot create and allowed pair of 
      // players with team $t
      $unpaired_teams = [];
      foreach($teams_left as $t2)
      {
        // if teams cannot for any allowed matchup are scheduled to be deleted
        if(!$t -> pairs_with($t2)) 
        {
          $unpaired_teams[] = array_search($t2, $teams_left);
        }
      }
      // delete all "unpaired" teams and restructure the array
      foreach($unpaired_teams as $i) unset($teams_left[$i]);
      $teams_left = array_values($teams_left);

      $n = sizeof($teams_left);

      if($n > 0)
      {
        $i = rand(0, $n - 1);
        return $teams_left[$i];
      }
      // if no teams are available return False
      else
      {
        return False;
      }
    }

    /**
     * \return void
     *
     * \brief Schedule a single round of matched
     *
     * First all teams are reset for the round with all round-specific values 
     * set to defaults. Than the scheduling algorithm is invoked in two stages 
     * described in details in the class description.
     */
    public function schedule_next_round()
    {
      // matchups created in this round are stored here
      $pairs = [];

      // reset all round-specific values
      foreach($this -> teams as $t) $t -> new_round();

      // the algorithm has two steps and $i is the threshold value used for 
      // teams selection. In the first step we select players from teams that 
      // have more than one player (threhold $i = 1) and in the second step we 
      // take all teams with positive number of players (threshold $i=0)
      for($i = 1; $i >=0 ; $i--)
      {
        // the loop breaks when there are no possible matchups left
        while(true)
        {
          // teams having enough players to select from
          $teams = $this -> get_available_teams($i);

          // first team is the one that has most unselected players left
          $first_team = $this -> select_most_populated_team($teams);
          if($first_team === False) break;

          // second team is selected randomly (with some restrictions - please 
          // look at the method comments)
          $second_team = $this -> select_second_team($teams, $first_team);
          if($second_team === False) break;

          // choose a pair of players from selected teams
          $temp_pair = $this -> match_pair($first_team, $second_team);
          $p1 = $temp_pair[0];
          $p2 = $temp_pair[1];

          // save the current opponent for next round
          $p1 -> previous_opponent = $p2 -> name;
          $p2 -> previous_opponent = $p1 -> name;

          // assign heads or tails for each player randomly
          $toss = rand(0, 1);
          $p1 -> heads_tails[] = $toss ? "heads" : "tails";
          $p2 -> heads_tails[] = !$toss ? "heads" : "tails";

          $pairs[] = array($p1, $p2);
        }
      }

      // check are there any players left unasigned to any match
      $teams = $this -> get_available_teams(0);
      $unpaired_players = [];
      foreach($teams as $t)
      {
        foreach($t -> get_available_players() as $p)
        {
          // set some values to NULL so the length of heads_tails array is 
          // correct and clear the value of previous opponent
          $p -> previous_opponent = NULL;
          $p -> heads_tails[] = NULL;
          $unpaired_players[] = $p;
        }
      } 

      $this -> rounds[] = array("pairs" => $pairs, "unpaired_players" => $unpaired_players);
    }

    /**
     * \return rounds[] copy of the protected rounds variable
     *
     * \brief Simple getter of $rounds protected variable
     *
     * Prevents accidental overwritting
     */
    public function get_fixtures()
    {
      return $this -> rounds;
    }

    /**
     * \return void
     *
     * \params n Number of rounds to schedule
     *
     * \brief Schedules multiple rounds at once
     *
     * Calls schedule_next_round function $n times
     */
    public function schedule_fixtures($n)
    {
      for($i=0; $i < $n; $i++)
      {
        $this -> schedule_next_round();
      }
    }

    /**
     * \return void
     *
     * \params n number of round to play
     *
     * \brief Play a single round of games
     *
     * Iterated over all scheduled pairs of players, tosses a coin and assigns 
     * winner/loser for each pair.
     */
    public function play_rounds($n = 1)
    {
      for($i = 0; $i < $n; $i++)
      {
        if($this -> current_round < sizeof($this -> rounds))
        {
          foreach($this -> rounds[$this -> current_round]['pairs'] as $p)
          {
            $toss = rand(0, 1);
            $toss =  $toss ? "heads" : "tails";
            $p1 = $p[0];
            $p2 = $p[1];
            
            $x = ($toss == $p1 -> heads_tails[$this -> current_round]) ? True : False;
            $p1 -> results[] = $x;
            $p2 -> results[] = !$x;
          }
          foreach($this -> rounds[$this -> current_round]['unpaired_players'] as $p)
          {
            $p -> results[] = NULL;
          }
        }
        $this -> current_round++;
      }
    }

    /**
     * \return Array(player1, player2) pair of players scheduled to play with 
     * each other in a round
     *
     * \param team1 Instance of team. First team to chose a player from
     *
     * \param team2 Instance of team. Second team to chose a player from
     *
     * \brief Returns a random pair of players than can play with each other
     *
     * Takes two teams as argumetns and returns a pair of players scheduled to 
     * play with each other.
     */
    protected function match_pair($team1, $team2)
    {
      $players1 = $team1 -> get_available_players();
      $players2 = $team2 -> get_available_players();
      $team1 -> players_left--;
      $team2 -> players_left--;

      $pairs = [];

      foreach($players1 as $p1)
      {
        foreach($players2 as $p2)
        {
          if($p1 -> name != $p2 -> previous_opponent)
          {
            $pairs[] = array($p1, $p2);
          }
        }
      }

      $i = rand(0, sizeof($pairs) - 1);
      $temp_pair = $pairs[$i];
      $temp_pair[0] -> selected = True;
      $temp_pair[1] -> selected = True;

      return $temp_pair;
    }

  } // end class scheduler
?>
