<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<?php
require_once __DIR__ . '/../logging.php';
require_once __DIR__ . '/../config.php';
$log = new Logging();
$log->lfile(__DIR__ . '/../' . $log_dir . '/add.log');
if (!($_domain = $_GET['domain'] ?? null)) {
  $log->lwrite('no domain given');
  die('no pod domain given');
}

$_email            = $_GET['email'] ?? '';
$_podmin_statement = $_GET['podmin_statement'] ?? '';
$_podmin_notify    = $_GET['podmin_notify'] ?? 0;

$_domain = strtolower($_domain);
if (!filter_var(gethostbyname($_domain), FILTER_VALIDATE_IP)) {
  die('Could not validate the domain name, be sure to enter it as "domain.com" (no caps, no slashes, no extras)');
}

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT domain, stats_apikey, publickey, email FROM pods';
$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  if ($row['domain'] === $_domain ) {
    if ($row['email']) {
      $log->lwrite('domain already exists and is registered to an owner' . $_domain);
      die('domain already exists and is registered to an owner, use the edit function to modify');
    }

    $digtxt = exec(escapeshellcmd('dig ' . $_domain . ' TXT +short'));
    if (strpos($digtxt, $row['publickey']) !== false) {
      echo 'domain validated, you can now add details '; 
      $uuid     = md5(uniqid($_domain, true));
      $expire   = time() + 2700;
      $sql      = 'UPDATE pods SET token = $1, tokenexpire = $2 WHERE domain = $3';
      $result   = pg_query_params($dbh, $sql, [$uuid, date('Y-m-d H:i:s', $expire), $_domain]);
      $result   || die('Error in SQL query: ' . pg_last_error());
      
      echo <<<EOF
      <form action="edit.php" method="get">
      <input type="hidden" name="domain" value="{$_domain}">
      <input type="hidden" name="token" value="{$uuid}">
      <label>Email <input type="text" size="20" name="email"></label><br>
      <label>Podmin Statement (You can include links to your terms and policies and information about your pod you wish to share with users.) <textarea cols="100" rows="7" name="podmin_statement"></textarea></label><br>
      <label>Weight <input type="text" size="2" name="weight"> This lets you weight your pod lower on the list if you have too much traffic coming in, 10 is the norm use lower to move down the list.</label><br>
      <input type="submit" name="action" value="save">
      </form>
EOF;
      
      die;
    } else {
      $log->lwrite('domain already exists and can be registered' . $_domain);
      die('domain already exists, you can claim the domain by adding a DNS TXT record that states<br><b> ' . $_domain . ' IN TXT "' . $row['publickey'] . '"</b>');
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

  $publickey = md5(uniqid($domain, true));
  $sql    = 'INSERT INTO pods (domain, email, podmin_statement, podmin_notify, publickey) VALUES ($1, $2, $3, $4, $5)';
  $result = pg_query_params($dbh, $sql, [$_domain, $_email, $_podmin_statement, $_podmin_notify, $publickey]);
  $result || die('Error in SQL query: ' . pg_last_error());

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
