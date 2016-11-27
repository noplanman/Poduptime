<?php
include('db/config.php');
$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
if (!$dbh) {
  die("Error in connection: " . pg_last_error());
}
$sql = "SELECT * FROM pods WHERE adminrating <> -1 AND hidden <> 'yes' AND uptimelast7 > 95 AND masterversion = shortversion AND signup = 1 ORDER BY RANDOM() LIMIT 1";
$result = pg_query($dbh, $sql);
if (!$result) {
  die("Error in SQL query1: " . pg_last_error());
}
$row = pg_fetch_all($result);
if ($row[0]['secure'] == "true") {$ssl="s";} else {$ssl="";}
header("Location: http" . $ssl . "://" . $row[0]['domain'] . "/users/sign_up");
?>
