<?php
require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = "SELECT * FROM pods WHERE adminrating <> -1 AND hidden <> 'yes' AND uptimelast7 > 95 AND masterversion = shortversion AND signup = 1 ORDER BY RANDOM() LIMIT 1";
$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

$row = pg_fetch_all($result);
$scheme = $row[0]['secure'] === 'true' ? 'https://' : 'http://';
header('Location: http' . $scheme . $row[0]['domain'] . '/users/sign_up');
