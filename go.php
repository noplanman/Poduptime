<?php

// Other parameters.
$_url = $_GET['url'] ?? '';

require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

if ($_url) {
  $host   = parse_url($_url, PHP_URL_HOST);
  $sql    = 'SELECT domain FROM pods WHERE domain LIKE $1';
  $result = pg_query_params($dbh, $sql, [$host]);
  $result || die('Error in SQL query: ' . pg_last_error());

  $row = pg_fetch_all($result);
  $row || die('unknown url');

  $sql    = 'INSERT INTO clicks (domain, manualclick) VALUES ($1, $2)';
  $result = pg_query_params($dbh, $sql, [$host, '1']);
  $result || die('Error in SQL query: ' . pg_last_error());
  
  header('Location:' . $_url);
} else {
  $sql    = 'SELECT domain FROM pods WHERE score > 90 AND masterversion = shortversion AND signup ORDER BY RANDOM() LIMIT 1';
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  $row    = pg_fetch_all($result);
  
  $sql    = 'INSERT INTO clicks (domain, autoclick) VALUES ($1, $2)';
  $result = pg_query_params($dbh, $sql, [$row[0]['domain'], '1']);
  $result || die('Error in SQL query: ' . pg_last_error());
  
  header('Location:https://' . $row[0]['domain'] . '/users/sign_up');
}
