<?php

function db_connect($hostname, $user, $pass, $database){
	$mysqli = mysqli_connect($hostname, $user, $pass, $database);
	if ($mysqli->connect_errno) {
	    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
	    return false;
	}
	
	return $mysqli;
}




/*
$res = $mysqli->query("SELECT 'choices to please everybody.' AS _msg FROM DUAL");
$row = $res->fetch_assoc();
echo $row['_msg'];
*/


?>