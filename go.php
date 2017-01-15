<?php

// Other parameters.
$_domain = $_GET['domain'] ?? '';

require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

if ($_domain) {
  $sql    = 'SELECT domain FROM pods WHERE domain LIKE $1';
  $result = pg_query_params($dbh, $sql, [$_domain]);
  $result || die('Error in SQL query: ' . pg_last_error());

  $row = pg_fetch_all($result);
  $row || die('unknown domain');

  $sql    = 'INSERT INTO clicks (domain, manualclick) VALUES ($1, $2)';
  $result = pg_query_params($dbh, $sql, [$_domain, '1']);
  $result || die('Error in SQL query: ' . pg_last_error());
  
  header('Location: https://' . $_domain);
} else {
  $sql    = 'SELECT domain FROM pods WHERE score > 90 AND masterversion = shortversion AND signup ORDER BY RANDOM() LIMIT 1';
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  $row    = pg_fetch_all($result);
  
  $sql    = 'INSERT INTO clicks (domain, autoclick) VALUES ($1, $2)';
  $result = pg_query_params($dbh, $sql, [$row[0]['domain'], '1']);
  $result || die('Error in SQL query: ' . pg_last_error());
  
  header('Location: https://' . $row[0]['domain'] . '/users/sign_up');
}
