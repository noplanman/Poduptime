<?php
/* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

use RedBeanPHP\R;

require_once __DIR__ . '/../logging.php';

$log = new Logging();
$log->lfile(realpath(getenv('LOG_DIR')) . '/add.log');
if (!($_domain = $_GET['domain'] ?? null)) {
  $log->lwrite('no domain given');
  echo 'no pod domain given';
  return;
}

// Other parameters.
$_email            = $_GET['email'] ?? '';
$_podmin_statement = $_GET['podmin_statement'] ?? '';
$_podmin_notify    = $_GET['podmin_notify'] ?? 0;

require_once __DIR__ . '/../loader.php';

$_domain = strtolower($_domain);
if (!unitTesting() && !filter_var(gethostbyname($_domain), FILTER_VALIDATE_IP)) {
  echo 'Could not validate the domain name, be sure to enter it as "domain.com" (no caps, no slashes, no extras)';
  return;
}

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
      echo 'domain already exists and is registered to an owner, use the edit function to modify';
      return;
    }

    $digtxt = !unitTesting() ?
      exec(escapeshellcmd('dig ' . $_domain . ' TXT +short'))
      : $_GET['digtxt'];
    if (strpos($digtxt, $pod['publickey']) !== false) {
      echo 'domain validated, you can now add details ';
      $uuid   = md5(uniqid($_domain, true));
      $expire = time() + 2700;

      try {
        $p                = R::load('pods', $pod['id']);
        $p['token']       = $uuid;
        $p['tokenexpire'] = date('Y-m-d H:i:s', $expire);

        // @todo Temporary fix! https://github.com/gabordemooij/redbean/issues/547
        foreach ($p->getProperties() as $key => $value) {
          $p[$key] = $value;
        }

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

      return;
    } else {
      $log->lwrite('domain already exists and can be registered' . $_domain);
      echo 'domain already exists, you can claim the domain by adding a DNS TXT record that states<br><b> ' . $_domain . ' IN TXT "' . $pod['publickey'] . '"</b>';
      return;
    }
  }
}

if (unitTesting()) {
  $outputssl = $_GET['nodeinfo'];
} else {
  $chss = curl_init();
  curl_setopt($chss, CURLOPT_URL, 'https://' . $_domain . '/nodeinfo/1.0');
  curl_setopt($chss, CURLOPT_POST, 0);
  curl_setopt($chss, CURLOPT_HEADER, 0);
  curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($chss, CURLOPT_NOBODY, 0);
  $outputssl = curl_exec($chss);
  curl_close($chss);
}

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

    // @todo Temporary fix! https://github.com/gabordemooij/redbean/issues/547
    foreach ($p->getProperties() as $key => $value) {
      $p[$key] = $value;
    }

    R::store($p);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  if ($_email) {
    $to      = getenv('ADMIN_EMAIL');
    $subject = 'New pod added to ' . $_SERVER['HTTP_HOST'];
    $headers = ['From: ' . $_email, 'Reply-To: ' . $_email, 'Cc: ' . $_email];

    $message_lines = [
      'https://' . $_SERVER['HTTP_HOST'],
      'Pod: https://' . $_SERVER['HTTP_HOST'] . '/db/pull.php?debug=1&domain=' . $_domain,
      '',
      'Your pod will not show up right away, as it needs to pass a few checks first.',
      'Give it a few hours!',
    ];

    unitTesting() || @mail($to, $subject, implode("\r\n", $message_lines), implode("\r\n", $headers));
  }

  echo 'Data successfully inserted! Your pod will be reviewed and live on the list in a few hours!';

} else {
  $log->lwrite('Could not validate your pod, check your setup! ' . $_domain);
  echo 'Could not validate your pod, check your setup!<br>Take a look at <a href="https://' . $_domain . '/nodeinfo/1.0">your /nodeinfo</a>';
}
$log->lclose();
