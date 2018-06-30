<?php
    
    
	$x= strval($_GET['x']);
	$y = strval($_GET['y']);
	

    $con=mysqli_connect("localhost", "testaaja", "salasana", "dht");
    
    if (mysqli_connect_errno())
    {
      echo "MySQL-yhteys epäonnistui: " . mysqli_connect_error();
    }
	
	
	if ($x === "interval" or $x === "enabled") {
		
		$query = "UPDATE parameters SET value = " . $y . " WHERE name = '" . $x . "'";
		$result = mysqli_query($con, $query);
		mysqli_close($con);

		echo $y;

	}

	
	
	
	
	else {
		echo "unknown input: x = $x , y = $y";
	}
    
   
?>