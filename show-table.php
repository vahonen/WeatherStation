<?php
	
	$start_date = date('Y-m-d', strtotime($_POST['dateFrom']));
	$stop_date = date('Y-m-d', strtotime($_POST['dateTo']));
	
    $con=mysqli_connect("localhost", "testaaja", "salasana", "dht");
    
    if (mysqli_connect_errno())
    {
      echo "MySQL-yhteys epäonnistui: " . mysqli_connect_error();
    }
	
	$sql_query = "SELECT * FROM data_table WHERE date >= '" . $start_date . "' AND date <= '" . $stop_date . "' ORDER BY idDataTable DESC;";
	//echo $sql_query;
	$result = mysqli_query($con, $sql_query);

	echo '<link rel="stylesheet" type="text/css" href="./style.css">';
	echo "<table>";
	echo "<tr><th>Pvm</th><th>Aika</th><th>Lämpötila (°C)</th><th>Kosteus (%)</th></tr>";
	while($row = mysqli_fetch_array($result))
	{
		echo "<tr><td>" . $row['date'] . "</td><td> " . $row['time'] . "</td><td>" . $row['temperature'] . "</td><td>" . $row['humidity'] . "</td></tr>";
	}
	echo "</table>";
	
	
	mysqli_close($con);
?>