<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<?php

use RedBeanPHP\R;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../logging.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

$log = new Logging();
$log->lfile(__DIR__ . '/../' . $log_dir . '/add.log');
if (!($_domain = $_GET['domain'] ?? null)) {
  $log->lwrite('no domain given');
  die('no pod domain given');
}

// Other parameters.
$_email            = $_GET['email'] ?? '';
$_podmin_statement = $_GET['podmin_statement'] ?? '';
$_podmin_notify    = $_GET['podmin_notify'] ?? 0;

$_domain = strtolower($_domain);
if (!filter_var(gethostbyname($_domain), FILTER_VALIDATE_IP)) {
  die('Could not validate the domain name, be sure to enter it as "domain.com" (no caps, no slashes, no extras)');
}

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
  $pods = R::getAll('
    SELECT id, domain, stats_apikey, publickey, email
    FROM pods
  ');
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

foreach ($pods as $pod) {
  if ($pod['domain'] === $_domain) {
    if ($pod['email']) {
      $log->lwrite('domain already exists and is registered to an owner' . $_domain);
      die('domain already exists and is registered to an owner, use the edit function to modify');
    }

    $digtxt = exec(escapeshellcmd('dig ' . $_domain . ' TXT +short'));
    if (strpos($digtxt, $pod['publickey']) !== false) {
      echo 'domain validated, you can now add details ';
      $uuid   = md5(uniqid($_domain, true));
      $expire = time() + 2700;

      try {
        $p                = R::load('pods', $pod['id']);
        $p['token']       = $uuid;
        $p['tokenexpire'] = date('Y-m-d H:i:s', $expire);

        R::store($p);
      } catch (\RedBeanPHP\RedException $e) {
        die('Error in SQL query: ' . $e->getMessage());
      }

      echo <<<EOF
      <form action="edit.php" method="get">
      <input type="hidden" name="domain" value="{$_domain}">
      <input type="hidden" name="token" value="{$uuid}">
      <label>Email <input type="text" size="20" name="email"></label><br>
      <label>Podmin Statement (You can include links to your terms and policies and information about your pod you wish to share with users.) <br><textarea cols="100" rows="7" name="podmin_statement"></textarea></label><br>
      <label>Weight <input type="text" size="2" name="weight"> This lets you weight your pod lower on the list if you have too much traffic coming in, 10 is the norm use lower to move down the list.</label><br>
      <input type="submit" name="action" value="save">
      </form>
EOF;

      die;
    } else {
      $log->lwrite('domain already exists and can be registered' . $_domain);
      die('domain already exists, you can claim the domain by adding a DNS TXT record that states<br><b> ' . $_domain . ' IN TXT "' . $pod['publickey'] . '"</b>');
    }
  }
}

$chss = curl_init();
curl_setopt($chss, CURLOPT_URL, 'https://' . $_domain . '/nodeinfo/1.0');
curl_setopt($chss, CURLOPT_POST, 0);
curl_setopt($chss, CURLOPT_HEADER, 0);
curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chss, CURLOPT_NOBODY, 0);
$outputssl = curl_exec($chss);
curl_close($chss);

if (stristr($outputssl, 'openRegistrations')) {
  $log->lwrite('Your pod has ssl and is valid ' . $_domain);
  echo 'Your pod has ssl and is valid<br>';

  $publickey = md5(uniqid($_domain, true));

  try {
    $p                     = R::dispense('pods');
    $p['domain']           = $_domain;
    $p['email']            = $_email;
    $p['podmin_statement'] = $_podmin_statement;
    $p['podmin_notify']    = $_podmin_notify;
    $p['publickey']        = $publickey;

    R::store($p);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  if ($_email) {
    $to      = $adminemail;
    $subject = 'New pod added to ' . $_SERVER['HTTP_HOST'];
    $headers = ['From: ' . $_email, 'Reply-To: ' . $_email, 'Cc: ' . $_email];

    $message_lines = [
      'https://' . $_SERVER['HTTP_HOST'],
      'Pod: https://' . $_SERVER['HTTP_HOST'] . '/db/pull.php?debug=1&domain=' . $_domain,
      '',
      'Your pod will not show up right away, as it needs to pass a few checks first.',
      'Give it a few hours!',
    ];

    @mail($to, $subject, implode("\r\n", $message_lines), implode("\r\n", $headers));
  }

  echo 'Data successfully inserted! Your pod will be reviewed and live on the list in a few hours!';

} else {
  $log->lwrite('Could not validate your pod, check your setup! ' . $_domain);
  echo 'Could not validate your pod, check your setup!<br>Take a look at <a href="https://' . $_domain . '/nodeinfo/1.0">your /nodeinfo</a>';
}
$log->lclose();
