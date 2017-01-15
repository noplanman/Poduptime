<?php

// Required parameters.
($_username = $_POST['username'] ?? null) || die('no username given');
($_userurl = $_POST['userurl'] ?? null) || die('no userurl given');
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');
($_comment = $_POST['comment'] ?? null) || die('no comment');
($_rating = $_POST['rating'] ?? null) || die('no rating given');

// Other parameters.
$_email = $_POST['email'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'INSERT INTO rating_comments (domain, comment, rating, username, userurl) VALUES($1, $2, $3, $4, $5)';
$result = pg_query_params($dbh, $sql, [$_domain, $_comment, $_rating, $_username, $_userurl]);
$result || die('Error in SQL query: ' . pg_last_error());

$to      = $adminemail;
$headers = ['From: ' . $_email];
$subject = 'New rating added to poduptime ';

$message_lines = [
  'Pod: ' . $_domain,
  'Username: ' . $_username,
  'User URL: ' . $_userurl,
  'Comment: ' . $_comment,
  'Rating:' . $_rating,
];

@mail($to, $subject, implode("\r\n", $message_lines), implode("\r\n", $headers));
echo 'Comment posted!';
