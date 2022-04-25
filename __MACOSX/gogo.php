<?php
	$db = new SQLite3('db/gogo22.db');
	$query = "SELECT name, gercRiderID, points, GetGOingPoints, OfficialRidePoints FROM gogo WHERE gercRiderID=9;";
	
	// if we want to put values directly in
	//$query = "SELECT name, gercRiderID, points, GetGOingPoints, OfficialRidePoints FROM gogo WHERE gercRiderID=863;";
	
	$queryResult = $db->querySingle($query,true);
	//$rowResult = $queryResult->fetchArray(SQLITE3_ASSOC);
	error_log(print_r($queryResult,true));

?>
