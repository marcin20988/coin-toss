<?php
  require_once("./team.php");
  require_once("./scheduler.php");
  
  $colours = ["red", "green", "yellow", "blue", "orange", "pink", "black", "white"];
  $S = new scheduler(51, $colours);
  $S -> schedule_fixtures(6);
  $S -> play_rounds(6);

  foreach($S -> teams as $t)
  {
    echo "team " . $t -> colour . " has " . $t -> number_of_players . " players\n";
  }
  echo "--------------------\n";

  $fixtures = $S -> get_fixtures();

  $cnt = 1;
  foreach($fixtures as $f)
  {
    echo "Fixture $cnt:\n";

    echo"\t-----\n";
    echo "\tPairs (" . sizeof($f['pairs']) . "):\n";
    $i = 1;
    foreach($f['pairs'] as $p)
    {
      $status1 = $p[0] -> results[$cnt - 1] ? "Winner" : "Loser";
      $status2 = $p[1] -> results[$cnt - 1] ? "Winner" : "Loser";
      echo "\t" . $p[0] -> name . " (" . $p[0] -> team . ", $status1) \t\t vs\t\t";
      echo $p[1] -> name . " (" . $p[1] -> team . ", $status2) \n";
    }
    echo"\t-----\n";
    echo "\tUnpaired players:\n";
    foreach($f['unpaired_players'] as $p) echo "\t" . $p -> name . " (" . $p -> team . ") \n";


    $cnt++;
  }
?>
