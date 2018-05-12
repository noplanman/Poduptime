<?php

use RedBeanPHP\R;

// Required parameters.
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');

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
  $pod = R::findOne('pods', 'domain = ?', [$_domain]);
  $pod || die('domain not found');
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

// Set up common variables.
$uuid          = md5(uniqid($_domain, true));
$link          = sprintf('https://%1$s/?edit&domain=%2$s&token=%3$s', $_SERVER['HTTP_HOST'], $_domain, $uuid);
$headers       = ['From: ' . $adminemail];
$message_lines = [];

if ($_email) {
  $pod['email'] === $_email || die('email mismatch');

  $to        = $_email;
  $subject   = 'Temporary edit key for ' . $_SERVER['HTTP_HOST'];
  $headers[] = 'Bcc: ' . $adminemail;
  $expire    = time() + 2700;
  $output    = 'Link sent to your email';
} elseif (!$pod['email']) {
  die('domain is registered but no email associated, to add an email use the add a pod feature');
} else {
  $to              = $adminemail;
  $subject         = 'FORWARD REQUEST: Temporary edit key for ' . $_SERVER['HTTP_HOST'];
  $message_lines[] = 'User trying to edit pod without email address.';
  $message_lines[] = 'Email found: ' . $pod['email'];
  $expire          = time() + 9700;
  $output          = 'Link sent to administrator to review and verify, if approved they will forward the edit key to you.';
}

try {
  $pod['token']       = $uuid;
  $pod['tokenexpire'] = date('Y-m-d H:i:s', $expire);

  R::store($pod);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

$message_lines[] = 'Link: ' . $link;
$message_lines[] = 'Expires: ' . date('Y-m-d H:i:s T', $expire);

@mail($to, $subject, implode("\r\n", $message_lines), implode("\r\n", $headers));

echo $output;
