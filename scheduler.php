<?php
  require_once("team.php");

  class scheduler
  {
    public $teams=[];

    public $number_of_teams = 0;

    public $number_of_players = 0;

    protected $rounds = [];

    protected $current_round = 0;

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

    private function assign_players($players)
    {
      foreach($players as $p)
      {
        $i = rand(0, $this -> number_of_teams - 1);
        $this -> teams[$i] -> assign_player($p);
      }
    }

    private function get_available_teams($limit)
    {
      $avail_t = [];
      foreach($this -> teams as $t)
      {
        if($t -> players_left > $limit) $avail_t[] = $t;
      }
      return $avail_t;
    }

    public function select_most_populated_team($teams)
    {
      $max_players = 0;
      foreach($teams as $t) $max_players = max($max_players, $t -> number_of_players);

      $temp_t = [];
      foreach($teams as $t)
      { 
        if($max_players == $t -> number_of_players)
        {
          $temp_t[] = $t;
        }
      }
      if(sizeof($temp_t) == 0) return False;

      $i = rand(0, sizeof($temp_t) - 1);
      return $temp_t[$i];
    }

    public function select_second_team($teams, $t)
    {
      $teams_left = $teams;
      $key = array_search($t, $teams_left);
      unset($teams_left[$key]);
      $teams_left = array_values($teams_left);

      $unpaired_teams = [];
      foreach($teams_left as $t2)
      {
        if(!$t -> pairs_with($t2)) 
        {
          $unpaired_teams[] = array_search($t2, $teams_left);
        }
      }
      foreach($unpaired_teams as $i) unset($teams_left[$i]);
      $teams_left = array_values($teams_left);

      $n = sizeof($teams_left);

      if($n > 0)
      {
        $i = rand(0, $n - 1);
        return $teams_left[$i];
      }
      else
      {
        return False;
      }
    }

    public function schedule_next_round()
    {
      $pairs = [];
      foreach($this -> teams as $t) $t -> new_round();

      for($i = 1; $i >=0 ; $i--)
      {
        while(true)
        {
          $teams = $this -> get_available_teams($i);

          $first_team = $this -> select_most_populated_team($teams);
          if($first_team === False) break;

          $second_team = $this -> select_second_team($teams, $first_team);
          if($second_team === False) break;

          $p2 = $second_team -> select_player();
          $p1 = $first_team -> select_player($p2);

          $p1 -> previous_opponent = $p2 -> name;
          $p2 -> previous_opponent = $p1 -> name;

          $toss = rand(0, 1);
          $p1 -> heads_tails = $toss ? "heads" : "tails";
          $p2 -> heads_tails = !$toss ? "heads" : "tails";

          $pairs[] = array($p1, $p2);
        }
      }

      $teams = $this -> get_available_teams(0);

      $unpaired_players = [];
      foreach($teams as $t)
      {
        foreach($t -> get_available_players() as $p)
        {
          $p -> previous_opponent = NULL;
          $unpaired_players[] = $p;
        }
      } 

      $this -> rounds[] = array("pairs" => $pairs, "unpaired_players" => $unpaired_players);
    }

    public function get_fixtures()
    {
      return $this -> rounds;
    }

    public function schedule_fixtures($n)
    {
      for($i=0; $i < $n; $i++)
      {
        $this -> schedule_next_round();
      }
    }

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
            
            $x = ($toss == $p1 -> heads_tails) ? True : False;
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

  } // end class scheduler
?>
