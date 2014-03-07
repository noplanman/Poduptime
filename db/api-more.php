<?php
error_reporting(E_ALL);
/**
 * Copyright (c) 2011, David Morley. 
 * This file is licensed under the Affero General Public License version 3 or later. 
 * See the COPYRIGHT file.
 */

//this is just a single api for a pod for the android app to get data
require_once 'config.inc.php';
require_once 'db.class.php';

if (isset($_GET['url'])) {
	$dbConnection = DB::connectDB();
	if (!$dbConnection) {
		die("Error in connection: " . $dbConnection->errorInfo()[2]);
	}  
	
	$sql = "SELECT * FROM pods WHERE domain = ".$dbConnection->quote($_GET['url']);
	$result = $dbConnection->query($sql);
	if (!$result) {
		die("Error in SQL query: " . $dbConnection->errorInfo()[2]);
	}
	
	foreach ($result->fetchAll() as $row) {
		echo "Status: " . $row["status"] . "<br>";
	    echo "Last Git Pull: " . $row["hgitdate"] . "<br>";
	    echo "Uptime This Month " . $row["uptimelast7"] . "<br>";
	    echo "Months Monitored: " . $row["monthsmonitored"] . "<br>";
	    echo "Response Time: " . $row["responsetimelast7"] . "<br>";
	    echo "User Rating: ". $row["userrating"] . "<br>";
	    echo "Server Location: ". $row["country"] . "<br>";
	}
	unset($dbConnection);
} else {
	echo "url parameter is missing";
}
?>
