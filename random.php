<?php
 include('db/config.php');
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
    if (!$dbh) {
         die("Error in connection: " . pg_last_error());
     }
	 $sql = "SELECT domain,pingdomurl,score,datecreated,adminrating FROM pods ORDER BY RANDOM() LIMIT 1";
	 $result = pg_query($dbh, $sql);
	 if (!$result) {
	     die("Error in SQL query1: " . pg_last_error());
	 }
$row = pg_fetch_all($result);
header("Location: http://" . $row[0]['domain'] . "/users/sign_up");
?>
