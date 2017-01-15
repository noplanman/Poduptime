<?php

// Required parameters.
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');
($_adminkey = $_POST['adminkey'] ?? null) || die('no admin key given');
($_action = $_POST['action'] ?? null) || die('no action selected');

// Other parameters.
$_comments = $_POST['comments'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT email FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('one Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  $adminkey === $_adminkey || die('admin key mismatch');

  //save and exit
  if ($_action === 'delete') {
    $sql    = 'DELETE FROM pods WHERE domain = $1';
    $result = pg_query_params($dbh, $sql, [$_domain]);
    $result || die('two Error in SQL query: ' . pg_last_error());

    if ($row['email']) {
      $to      = $row['email'];
      $subject = 'Pod deleted from ' . $_SERVER['HTTP_HOST'];
      $message = 'Pod ' . $_domain . ' was deleted from ' . $_SERVER['HTTP_HOST'] . ' as it was dead on the list. ' . $_comments . " Feel free to add back at any time. \n\n";
      $headers = "From: " . $adminemail ."\r\nCc:" . $adminemail . "," . $row['email'] . "\r\n";
      @mail($to, $subject, $message, $headers);
    }
  } elseif ($_action === 'warn') {
    if ($row['email']) {
      $to      = $row['email'];
      $subject = 'Pod removal warning from ' . $_SERVER['HTTP_HOST'];
      $message = 'Pod ' . $_domain . ' is on the list to be deleted now because:  ' . $_comments . ". \n\n Please let me know if you need help fixing before it is removed. \n\n";
      $headers = "From: " . $adminemail ."\r\nCc:" . $adminemail . "," . $row['email'] . "\r\n";
      @mail($to, $subject, $message, $headers);
    }
  }

  echo $result;
}
