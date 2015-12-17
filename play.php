<html>
  <body>
    <h1>Coin toss tournament</h1>
    <form action="play.php" method="post">
      <table>
        <tr><td>Number of players</td><td><input value=51></td></tr>
        
        <th>Teams</th>
          <?php
            $colours = ["red" => '#fa2323', "green" => '#23fa23',
            "yellow" => '#fafa23', "blue" => '#2323fa', "orange" => '#fa9923',
            "pink" => '#fa9999', "black" => '#060606', "white" => '#fafafa'];
            foreach($colours as $c => $rgb)
            {
              $id = 0;
              echo "<tr>";
                echo "<td><div>Team</div></td>";
                echo "<td><input value='$c' class='team' id='team$id'></input></td>";
                echo "<td><input value='$rgb' class='colour' id='colour$id'></input></td>";
              echo "</tr>";
            }
          ?>
        <tr><td><input type='submit' value='submit' ></td></tr>
      <table>
    <form>
    <div class='results'>
    <?php
            var_dump($_POST);
    ?>
    </div>
  </body>
</html>
