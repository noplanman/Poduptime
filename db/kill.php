<?php

// Required parameters.
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');
($_adminkey = $_POST['adminkey'] ?? null) || die('no admin key given');
$adminkey === $_adminkey || die('admin key mismatch');
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
  $email = $row['email'] ?? null;

  if ($_action === 'delete') {
    $sql        = 'DELETE FROM pods WHERE domain = $1';
    $res_delete = pg_query_params($dbh, $sql, [$_domain]);
    $res_delete || die('two Error in SQL query: ' . pg_last_error());

    if ($email) {
      $to      = $email;
      $headers = ['From: ' . $adminemail, 'Cc: ' . $adminemail];
      $subject = 'Pod deleted from ' . $_SERVER['HTTP_HOST'];
      $message = 'Pod ' . $_domain . ' was deleted from ' . $_SERVER['HTTP_HOST'] . ' as it was dead on the list. ' . $_comments . ' Feel free to add back at any time.';
      @mail($to, $subject, $message, implode("\r\n", $headers));
    }
  } elseif ($_action === 'warn') {
    if ($email) {
      $to      = $email;
      $headers = ['From: ' . $adminemail, 'Cc: ' . $adminemail];
      $subject = 'Pod removal warning from ' . $_SERVER['HTTP_HOST'];
      $message = 'Pod ' . $_domain . ' is on the list to be deleted now because:  ' . $_comments . ".\r\nPlease let me know if you need help fixing before it is removed.";
      @mail($to, $subject, $message, implode("\r\n", $headers));
    }
  }

  echo $result;
}
