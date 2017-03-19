<?php

use RedBeanPHP\R;

// Required parameters.
if (!($_domain = $_POST['domain'] ?? null)) {
  echo 'no pod domain given';
  return;
}

// Other parameters.
$_email = $_POST['email'] ?? '';

require_once __DIR__ . '/../loader.php';

try {
  $pod = R::findOne('pods', 'domain = ?', [$_domain]);
  if (!$pod) {
    echo 'domain not found';
    return;
  }
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

// Set up common variables.
$uuid          = md5(uniqid($_domain, true));
$link          = sprintf('https://%1$s/db/edit.php?domain=%2$s&token=%3$s', $_SERVER['HTTP_HOST'], $_domain, $uuid);
$headers       = ['From: ' . getenv('ADMIN_EMAIL')];
$message_lines = [];

if ($_email) {
  if ($pod['email'] !== $_email) {
    echo 'email mismatch';
    return;
  }

  $to        = $_email;
  $subject   = 'Temporary edit key for ' . $_SERVER['HTTP_HOST'];
  $headers[] = 'Bcc: ' . getenv('ADMIN_EMAIL');
  $expire    = time() + 2700;
  $output    = 'Link sent to your email';
} elseif (!$pod['email']) {
  echo 'domain is registered but no email associated, to add an email use the add a pod feature';
  return;
} else {
  $to              = getenv('ADMIN_EMAIL');
  $subject         = 'FORWARD REQUEST: Temporary edit key for ' . $_SERVER['HTTP_HOST'];
  $message_lines[] = 'User trying to edit pod without email address.';
  $message_lines[] = 'Email found: ' . $pod['email'];
  $expire          = time() + 9700;
  $output          = 'Link sent to administrator to review and verify, if approved they will forward the edit key to you.';
}

try {
  $pod['token']       = $uuid;
  $pod['tokenexpire'] = date('Y-m-d H:i:s', $expire);

  // @todo Temporary fix! https://github.com/gabordemooij/redbean/issues/547
  foreach ($pod->getProperties() as $key => $value) {
    $pod[$key] = $value;
  }

  R::store($pod);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

$message_lines[] = 'Link: ' . $link;
$message_lines[] = 'Expires: ' . date('Y-m-d H:i:s T', $expire);

unitTesting() || @mail($to, $subject, implode("\r\n", $message_lines), implode("\r\n", $headers));

echo $output;
