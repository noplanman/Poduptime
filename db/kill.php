<?php

use RedBeanPHP\R;

// Required parameters.
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');
($_adminkey = $_POST['adminkey'] ?? null) || die('no admin key given');
($_action = $_POST['action'] ?? null) || die('no action selected');

// Other parameters.
$_comments = $_POST['comments'] ?? '';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

$adminkey === $_adminkey || die('admin key mismatch');

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
  $pod = R::getRow('SELECT id, email FROM pods WHERE domain = ?', [$_domain]);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

if ($pod) {
  $email = $pod['email'];

  if ($_action === 'delete') {
    R::trash('pods', $pod['id']);

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
}
