<?php
	
	$x= strval($_GET['x']);
	$parameter = strval($_GET['y']);

    $con=mysqli_connect("localhost", "testaaja", "salasana", "dht");
    
    if (mysqli_connect_errno())
    {
      echo "MySQL-yhteys epäonnistui: " . mysqli_connect_error();
    }
	
	
	if ($x === "time") {
		
		$result = mysqli_query($con, "SELECT * FROM data_table ORDER BY idDataTable DESC LIMIT 1");

		if ($result->num_rows === 0) {
			$curr_time = "(odottaa mittausta)";
		}

		else {
			$row = mysqli_fetch_array($result);
			$curr_time = $row['date'] . " " . $row['time'];
		}

		echo "<b>" . $curr_time . "</b>";
		mysqli_close($con);
	}

	if ($x === "parameter") {
		
		$text = "(default)";

		$query = "SELECT value FROM parameters WHERE name = '" . $parameter . "'";
		$result = mysqli_query($con, $query);

		if ($result->num_rows === 0) {
			$text = "(parametria " .$parameter. " ei löydetty)";
		}

		else {
			$row = mysqli_fetch_array($result);
			$text = $row['value'];
		}

		echo "$text";
		mysqli_close($con);
	}
	
	else if ($x === "records") {
		$result = mysqli_query($con, "SELECT * FROM data_table ORDER BY idDataTable DESC LIMIT 10");

		if ($result->num_rows === 0) {
			echo "";
		}

		else {
			echo '<table border = "1">';
			echo "<tr><th>Pvm</th><th>Aika</th><th>Lämpötila (°C)</th><th>Kosteus (%)</th></tr>";
			while($row = mysqli_fetch_array($result))
			{
				echo "<tr><td>" . $row['date'] . "</td><td> " . $row['time'] . "</td><td>" . $row['temperature'] . "</td><td>" . $row['humidity'] . "</td></tr>";
			}
			echo "</table>";
			mysqli_close($con);
		}
	}
	
	else if ($x === "latest") {
		
		$result = mysqli_query($con, "SELECT * FROM data_table ORDER BY idDataTable DESC LIMIT 1");
		if($result->num_rows === 0) {
			$temperature = "0";
			$humidity = "0";
		}
		
		else {
			$row = mysqli_fetch_array($result);
		
			$temperature = $row['temperature'];
			$humidity = $row['humidity'];

		}

		$temp = array('temperature' => $temperature, 'humidity' => $humidity);
		echo json_encode($temp);
		mysqli_close($con);
	}
	
	else if ($x === "delete") {
		
		$result = mysqli_query($con, "DELETE FROM data_table");
		
		mysqli_close($con);
	}
	
	else {
		echo "";
	}
	
?>