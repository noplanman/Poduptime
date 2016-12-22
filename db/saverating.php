<?php
$_POST['username'] || die('no username given');
$_POST['userurl'] || die('no userurl given');
$_POST['domain'] || die('no pod domain given');
$_POST['comment'] || die('no comment');
$_POST['rating'] || die('no rating given');

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = "INSERT INTO rating_comments (domain, comment, rating, username, userurl) VALUES($1, $2, $3, $4, $5)";
$result = pg_query_params($dbh, $sql, [$_POST['domain'], $_POST['comment'], $_POST['rating'], $_POST['username'], $_POST['userurl']]);
$result || die('Error in SQL query: ' . pg_last_error());

$to      = $adminemail;
$subject = 'New rating added to poduptime ';
$message = 'Pod:' . $_POST['domain'] . $_POST['domain'] . $_POST['username'] . $_POST['userurl'] . $_POST['comment'] . $_POST['rating'] . "\n\n";
$headers = 'From: ' . $_POST['email'] . "\r\n";
@mail($to, $subject, $message, $headers);
echo 'Comment posted!';
pg_free_result($result);
pg_close($dbh);
