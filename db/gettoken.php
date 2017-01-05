<?php
$systemTimeZone = system('date +%Z');

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
  if ($_email) {
    $row['email'] === $_email || die('email not a match');

    $uuid   = md5(uniqid($_domain, true));
    $expire = date('Y-m-d H:i:s', time() + 2700);
    $sql    = 'UPDATE pods SET token = $1, tokenexpire = $2 WHERE domain = $3';
    $result = pg_query_params($dbh, $sql, [$uuid, $expire, $_domain]);
    $result || die('Error in SQL query: ' . pg_last_error());

    $to      = $_email;
    $subject = 'Temporary edit key for podupti.me';
    $message = 'Link: https://podupti.me/db/edit.php?domain=' . $_domain . '&token=' . $uuid . ' Expires: ' . $expire . ' ' . $systemTimeZone . "\n\n"; 
    $headers = "From: " . $adminemail . "\r\nBcc: " . $adminemail . "\r\n";
    @mail($to, $subject, $message, $headers);
    echo 'Link sent to your email';
  } else {
    $uuid   = md5(uniqid($_domain, true));
    $expire = date('Y-m-d H:i:s', time() + 9700);
    $sql    = 'UPDATE pods SET token = $1, tokenexpire = $2 WHERE domain = $3';
    $result = pg_query_params($dbh, $sql, [$uuid, $expire, $_domain]);
    $result || die('Error in SQL query: ' . pg_last_error());

    $to      = 'support@diasp.org';
    $subject = 'FORWARD REQUEST: Temporary edit key for podupti.me';
    $message = 'User trying to edit pod without email address. Email found: ' . $row['email'] . ' Link: https://podupti.me/db/edit.php?domain=' . $_domain . '&token=' . $uuid . ' Expires: ' . $expire . ' ' . $systemTimeZone . "\n\n"; 
    $headers = "From: " . $adminemail . "\r\nBcc: " . $adminemail . "\r\n";
    @mail($to, $subject, $message, $headers);
    echo 'Link sent to administrator to review and verify, if approved they will forward the edit key to you.';
  }

  pg_free_result($result);
}

pg_close($dbh);
