<?php
require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$url = $_GET['url'];

if ($url) {
  $host = parse_url($url, PHP_URL_HOST);
  $sql    = "SELECT domain FROM pods WHERE domain LIKE '$host'";
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());
  $row = pg_fetch_all($result);
  if ($row) {
    //Add click counter +1 for $row[0]['domain'] clicks in future, seperate click table
    header('Location:' .$url);
  } else {
    die('unknown url');
  }
} else {
  $sql    = "SELECT secure,domain FROM pods WHERE score > 90 AND masterversion = shortversion AND signup = 1 ORDER BY RANDOM() LIMIT 1";
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());
  $row = pg_fetch_all($result);
  $scheme = $row[0]['secure'] === 'true' ? 'https://' : 'http://';
  header('Location:' . $scheme . $row[0]['domain'] . '/users/sign_up');
}
