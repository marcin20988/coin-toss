<html>
  <head>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/play.js"></script>
    <link rel="stylesheet" type="text/css" href="css/play.css">
  </head>
  <body>
    <h1>Coin toss tournament</h1>
    <form action="play.php" method="post">
      <table id='setup'>
        <tr><td>Number of players</td><td><input value='51' name='players'></td></tr>
        <tr><td>Number of rounds</td><td><input value='6' name='rounds'></td></tr>
        
        <th onclick='add_row();'>Teams (+)</th>
          <?php
            $colours = ["red" => '#fa2323', "green" => '#23fa23',
            "yellow" => '#fafa23', "blue" => '#2323fa', "orange" => '#fa9923',
            "pink" => '#fa9999', "black" => '#888888', "white" => '#e0e0e0'];
            $id = 0;
            foreach($colours as $c => $rgb)
            {
              echo "<tr id='row$id'>";
                echo "<td onclick='remove_row($id);'>(-)</td>";
                echo "<td>Team</td>";
                echo "<td><input value='$c' class='team' name='team$id'></input></td>";
                echo "<td><input value='$rgb' class='colour' name='colour$id'></input></td>";
              echo "</tr>";
              $id++;
            }
          ?>
        <tr id='submit'><td><input type='submit' value='Schedule this tournament' ></td></tr>
      <table>
    <form>
    <div class='results'>
    <?php
            if(isset($_POST['players'])) $N = (int)($_POST['players']);
            if(isset($_POST['rounds'])) $M = (int)($_POST['rounds']);
            $id = 0;
            $teams = [];
            $team_colours = [];
            while(isset($_POST["team$id"]))
            {
              $name = $_POST["team$id"];
              $colour = $_POST["colour$id"];
              $teams[] = $name;
              $team_colours["$name"] = $colour;
              $id++;
            }
            require_once("scheduler.php");

            if(sizeof($teams) == sizeof($team_colours) && sizeof($teams) > 0 && $N > 0)
            {
              $S = new scheduler($N, $teams);
              $S -> schedule_fixtures($M);
              $S -> play_rounds($M);
              $data = $S -> get_fixtures();
              echo "<table>";
                echo "<th>Teams</th>";
                foreach($S -> teams as $t)
                { 
                  echo "<tr style='background-color: " . $team_colours[$t -> colour] . "'>";
                  echo "<td>" . $t -> colour . "</td>";
                  echo "<td>" . $t -> number_of_players . " players</td>";
                  echo "<td>List of players: ";
                  foreach($t -> players as $pl) echo $pl -> name . '  ';
                  echo "</td>";
                  echo "</tr>";
                }
              echo "</table>";

              echo "<table id='results'>";
              $cnt = 1;
              foreach($data as $fixture)
              {
                echo "<th>Fixture $cnt</th>";
                $pair_cnt = 0;
                foreach($fixture['pairs'] as $p)
                {
                  $status1 = $p[0] -> results[$cnt - 1] ? "Winner" : "Loser";
                  $status2 = $p[1] -> results[$cnt - 1] ? "Winner" : "Loser";
                  $pair_cnt++;
                  echo "<tr>";
                  echo "<td style='background-color: " . $team_colours[$p[0] -> team]; 
                  if($status1 == "Winner") echo "; border: solid 3px black";
                  echo "'>" . $p[0] -> name . "</td>";
                  echo "<td> vs </td>";
                  echo "<td style='background-color: " . $team_colours[$p[1] -> team];
                  if($status2 == "Winner") echo "; border: solid 3px black";
                  echo  "'>" . $p[1] -> name . "</td>";
                  echo "</tr>";
                }
                echo "<tr><td>$pair_cnt pairs</td>";

                echo "</tr>";
                //echo"\t-----\n";
                //echo "\tUnpaired players:\n";
                echo "<tr><td>unpaired players: </td>";
                foreach($fixture['unpaired_players'] as $p)
                {
                  echo "<td style='background-color: " . $team_colours[$p -> team] . "'>" . $p -> name . "</td>";
                }
                echo "</tr>";
                $cnt++;

              }
              echo "</table>";
            }
    ?>
    </div>
  </body>
</html>
