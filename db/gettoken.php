<?php

// Required parameters.
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');

// Other parameters.
$_email = $_POST['email'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT email FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());

$rows = pg_num_rows($result);
$rows > 0 || die('domain not found');

while ($row = pg_fetch_array($result)) {
  // Set up common variables.
  $uuid          = md5(uniqid($_domain, true));
  $link          = sprintf('https://%1$s/db/edit.php?domain=%2$s&token=%3$s', $_SERVER['HTTP_HOST'], $_domain, $uuid);
  $headers       = ['From: ' . $adminemail];
  $message_lines = [];

  if ($_email) {
    $row['email'] === $_email || die('email mismatch');

    $to        = $_email;
    $subject   = 'Temporary edit key for ' . $_SERVER['HTTP_HOST'];
    $headers[] = 'Bcc: ' . $adminemail;
    $expire    = time() + 2700;
    $output    = 'Link sent to your email';
  } elseif (!$row['email']) {
      echo "domain is registered but no email associated, to add an email use the add a pod feature";die;  
    } else {
    $to              = $adminemail;
    $subject         = 'FORWARD REQUEST: Temporary edit key for ' . $_SERVER['HTTP_HOST'];
    $message_lines[] = 'User trying to edit pod without email address.';
    $message_lines[] = 'Email found: ' . $row['email'];
    $expire          = time() + 9700;
    $output          = 'Link sent to administrator to review and verify, if approved they will forward the edit key to you.';
  }

    $sql    = 'UPDATE pods SET token = $1, tokenexpire = $2 WHERE domain = $3';
    $result = pg_query_params($dbh, $sql, [$uuid, date('Y-m-d H:i:s', $expire), $_domain]);
    $result || die('Error in SQL query: ' . pg_last_error());

  $message_lines[] = 'Link: ' . $link;
  $message_lines[] = 'Expires: ' . date('Y-m-d H:i:s T', $expire);

  @mail($to, $subject, implode("\r\n", $message_lines), implode("\r\n", $headers));

  echo $output;
}
