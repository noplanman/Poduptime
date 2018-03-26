<?php

use RedBeanPHP\R;

// Required parameters.
($_username = $_POST['username'] ?? null) || die('no username given');
($_userurl = $_POST['userurl'] ?? null) || die('no userurl given');
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');
($_comment = $_POST['comment'] ?? null) || die('no comment');
($_rating = $_POST['rating'] ?? null) || die('no rating given');

// Other parameters.
$_email = $_POST['email'] ?? '';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
  $r             = R::dispense('rating_comments');
  $r['domain']   = $_domain;
  $r['comment']  = $_comment;
  $r['rating']   = $_rating;
  $r['username'] = $_username;
  $r['userurl']  = $_userurl;
  R::store($r);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

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
