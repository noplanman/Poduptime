<?php

/**
 * Get token to allow pod editing.
 */

declare(strict_types=1);

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
    $output    = 'Link sent to your email.';
} elseif (!$pod['email']) {
    die('Domain is registered but no email associated, to add an email use the add a pod feature.');
} else {
    $to              = $pod['email'];
    $subject         = 'Temporary edit key for ' . $_SERVER['HTTP_HOST'];
    $message_lines[] = 'Looks like you did not enter your email address, be sure to update it if you forgot the one we have for you.';
    $message_lines[] = 'Email found: ' . $pod['email'];
    $expire          = time() + 2700;
    $output          = 'Link sent to email we have for this pod on file.';
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
