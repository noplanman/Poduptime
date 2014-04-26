<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
//this is just a single api for a pod for the android app to get data
 include('config.php');
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
 if (!$dbh) {
     die("Error in connection: " . pg_last_error());
 }  
 $sql = "SELECT * FROM pods WHERE domain = $1";
 $result = pg_query_params($dbh, $sql, array($_GET['url']));
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }   
 while ($row = pg_fetch_array($result)) {
     echo "Status: " . $row["status"] . "<br>";
     echo "Last Git Pull: " . $row["hgitdate"] . "<br>";
     echo "Uptime This Month " . $row["uptimelast7"] . "<br>";
     echo "Months Monitored: " . $row["monthsmonitored"] . "<br>";
     echo "Response Time: " . $row["responsetimelast7"] . "<br>";
     echo "User Rating: ". $row["userrating"] . "<br>";
     echo "Server Location: ". $row["country"] . "<br>";
 }
 pg_free_result($result);       
 pg_close($dbh);
?>
