<?php

  class player
  {
      protected static $count = 0;

      public static function getCount(){return self::$count;}

      public function __construct(){
        self::$count++;
        $this -> name = "player" . self::$count;
      }

      public $team = NULL;

      public $previous_opponent = NULL;

      public $heads_tails = NULL;

      public $name = NULL;

      public $selected = False;
  }

  class team
  {
      public $colour = NULL;

      public $players = [];

      public $number_of_players = 0;

      public $players_left = 0;

      public function __construct($colour){$this -> colour = $colour;}

      public function assign_player($p)
      {
        $p -> team = $this -> colour;
        $this -> players[] = $p; 
        $this -> number_of_players++;
        $this -> players_left = $this -> number_of_players;
      }

      public function new_round()
      {
        foreach($this -> players as $p) $p -> selected = False;
        $this -> players_left = $this -> number_of_players;
      }

      public function get_available_players()
      {
        $available_players = [];
        foreach($this -> players as $p)
        {
          if(!$p -> selected) $available_players[] = $p;
        }

        return $available_players;
      }

      public function select_player($p2 = NULL)
      {

        $available_players = $this -> get_available_players();
        if($p2 !== NULL)
        {
          $key = NULL;
          foreach($available_players as $p1)
          {
            if($p1 -> previous_opponent == $p2 -> name)
            {
              $key = array_search($p1, $available_players);
              break;
            }
          }
          if($key !== NULL)
          {
            unset($available_players[$key]);
            $available_players = array_values($available_players);
          }
        }
        $i = rand(0, sizeof($available_players) - 1);

        $p = $available_players[$i];
        $p -> selected = True;
        $this -> players_left--;

        return $p;
      }

      public function pairs_with($other_team)
      {
        if($other_team == $this) return False;
        if($other_team -> players_left <= 0 or $this -> players_left <= 0) return False;

        $available_players = $this -> get_available_players();
        $opponents = $other_team -> get_available_players();

/*        echo "current team is: \n";*/
        //foreach($available_players as $p1)
        //{
          //echo $p1 -> name . "\t" . " played with " . $p1 -> previous_opponent . "\n";
        //}
        //echo "opponent team is: \n";
        //foreach($other_team -> as $p1)
        //{
          //echo $p1 -> name . "\t" . " played with " . $p1 -> previous_opponent . "\n";
        /*}*/
        
        foreach($available_players as $p1)
        {
          foreach($opponents as $p2)
          {
            if($p1 -> previous_opponent != $p2 -> name)  return True;
          }
        }

        return False;

      }
  }
?>
