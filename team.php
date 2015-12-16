<?php

  /**
   * \brief Class player is a simple storage class containing data specific to each player.
   *
   * The class only own a single protected member to store number of class instances.
   * Otherwise all methods and members are public.
   */
  class player
  {
      // ---------------------protected / private data section
      /// Static counter for number of objects created.
      protected static $count = 0;

      // ---------------------public data section
      /**
       * \var string $team
       * \brief Team colour to identify the player's team
       *
       * Stores colour name as string.
       */
      public $team = NULL;

      /**
       * \var string $previous_opponent
       * \brief Name of the previous opponent
       * 
       * Stores name of the opponent (as string) from the previous round to 
       * prevent having the same matchup in the next round.
       */
      public $previous_opponent = NULL;

      /**
       * \var array of strings $heads_tails
       * \brief Value of heads or tails assigned for each round
       *
       * Stores "heads" or "tails" string for each round of the game. 
       * If player was left unpaired in a round it is assigned a NULL value
       */
      public $heads_tails = [];

      /**
       * \var string $name
       * \brief Name of a player
       *
       * Each player is assigned a name (as string) in the form: playerX
       * where X is the current value of players counter like: player1, 
       * player2, etc
       */
      public $name = NULL;

      /**
       * \var bool $selected
       * \brief States was the player already selected in this round
       *
       * In each round players start with $selected set to False and as soon 
       * as the player is selected for a matchup the value becomes True to 
       * prevent selecting the same layer twice in any given round.
       */
      public $selected = False;

      /**
       * \var array of bools $results
       * \brief Stores results of each game
       *
       * For each round takes one of three possible values: True if player 
       * won, False if lost, and NULL if player was not assigned to any match 
       * during any particular round
       */
      public $results = [];

      // ---------------------interface methods
      /**
       * \return int number of player class instances
       *
       * \brief Static function to access the counter of instances
       *
       * To prevent having public static counter (and its possible overwrite from 
       * outside of the class) this simple getter function is introduced.
       */
      public static function getCount(){return self::$count;}

      /**
       * \brief Simple constructor - increases the counter and sets a name
       *
       * First the number of instances is increased and than a simple name to 
       * identify the player is assigned.
       */
      public function __construct()
      {
        self::$count++;
        $this -> name = "player" . self::$count;
      }

      public function __destruct()
      {
        self::$count--;
      }
  }

  /**
   * \brief Class grouping players together.
   *
   * This not only groups several players together but also provides methods 
   * to select players still available for selection in a round.
   */
  class team
  {
      /**
       * \var string $colour
       * \brief Team colour uniquely identifies the team.
       *
       * Stores value of a colour (as string) that works as an unique ID for 
       * each team.
       */
      public $colour = NULL;

      /**
       * \var array of players $players
       * \brief All players belonging to this team.
       *
       * Simple array of all players assigned to this team.
       */
      public $players = [];

      /**
       * \var int $number_of_players
       * \brief Total number of players in the team
       *
       * Two integers help to schedule maximum possible number of matches in 
       * each round : total number of players and number of players still left 
       * to be scheduled in the current round
       */
      public $number_of_players = 0;

      /**
       * \var int $players_left
       * \brief Players still left for matches in the current round.
       *
       * Two integers help to schedule maximum possible number of matches in 
       * each round : total number of players and number of players still left 
       * to be scheduled in the current round.
       * In each round $players_left is first set equal to $number_of_players 
       * and than decreases with every player from the team assigned for 
       * matchup
       */
      public $players_left = 0;

      /**
       * \brief Simple constructor just to set the team colour.
       */
      public function __construct($colour){$this -> colour = $colour;}

      /**
       * \return void 
       *
       * \param p Instance of player. Player to be assigned to the team.
       *
       * \brief Function adds another player to the team
       *
       * Each time a player is assigned to team both $number_of_players and 
       * $players_left values are also adjusted
       */
      public function assign_player($p)
      {
        $p -> team = $this -> colour;
        $this -> players[] = $p; 
        $this -> number_of_players++;
        $this -> players_left = $this -> number_of_players;
      }

      /**
       * \param void
       *
       * \return void
       *
       * \brief Reset round-specific values
       *
       * Function should be called at the beginning of each round to reset 
       * round-specific values like number of players left to be assigned
       * and 'already selected' flag
       */
      public function new_round()
      {
        foreach($this -> players as $p) $p -> selected = False;
        $this -> players_left = $this -> number_of_players;
      }

      /**
       * \param void
       *
       * \return array player[]
       * 
       * \brief Get an array of players still available for matches
       *
       * Simple getter function. Loops over all players checking their 
       * selection status. If they were not selected yet, adds them to list of 
       * available players.
       */
      public function get_available_players()
      {
        $available_players = [];
        foreach($this -> players as $p)
        {
          if(!$p -> selected) $available_players[] = $p;
        }

        return $available_players;
      }

      /**
       * \return player Random player from the team
       *
       * \param p2 Instance of player. Player already selected for matchup.
       *
       * \brief Returns a random player for a matchup
       *
       * Two different functions stacked here together. If no argument is 
       * given to the function it returns a random player not yet selected for 
       * any match in this round.
       * If another player $p2 is given through function argument it return a 
       * random player from the team who was not yet selected for any other 
       * match AND can create a matchup with the player $p2
       */
      public function select_player($p2 = NULL)
      {

        $available_players = $this -> get_available_players();
        if($p2 !== NULL)
        {
          $key = NULL;
          foreach($available_players as $p1)
          {
            // if $p2 was an opponent of any player int the team ($p1) this 
            // player is excluded from the possble matchups
            if($p1 -> previous_opponent == $p2 -> name)
            {
              $key = array_search($p1, $available_players);
              // $p2 played just one match in the previous round therefore 
              // there is no need to continue the loop
              break;
            }
          }
          if($key !== NULL)
          {
            // if a player that was an opponent of $p2 is found it must be 
            // deleted from list of available players
            unset($available_players[$key]);
            // to update the indices in $available_players array this 
            // functions is called:
            $available_players = array_values($available_players);
          }
        }
        // select one of the available players randomly
        $i = rand(0, sizeof($available_players) - 1);
        $p = $available_players[$i];

        // update selected flag for the player and number of player left for 
        // selection
        $p -> selected = True;
        $this -> players_left--;

        return $p;
      }

      /**
       * \param other_team Instance of team
       *
       * \return bool Is there a possible match between two teems?
       *
       * \brief Establishes is there a possible matchup between two teams
       *
       * Function takes instance of team class and checks is there a possible 
       * to schedule match between the two teams. 
       */
      public function pairs_with($other_team)
      {
        // a match cannot be assigned between players of the same team
        if($other_team == $this) return False;
        // if no players left in a team no matchup can be created
        if($other_team -> players_left <= 0 or $this -> players_left <= 0) return False;

        // list of players in both teams
        $available_players = $this -> get_available_players();
        $opponents = $other_team -> get_available_players();

        foreach($available_players as $p1)
        {
          foreach($opponents as $p2)
          {
            // if a single matchup is possible teams can compete against each 
            // other
            if($p1 -> previous_opponent != $p2 -> name)  return True;
          }
        }

        // if no possible pair was found retrun False
        return False;

      }
  }
?>
