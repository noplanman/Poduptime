<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<?php
$valid = 0;
require_once __DIR__ . '/../logging.php';

$log = new Logging();
$log->lfile(__DIR__ . $log_dir . '/add.log');
if (!$_POST['url']) {
  $log->lwrite('no url given ' . $_POST['domain']);
  die('no url given');
}
if (!$_POST['email']) {
  $log->lwrite('no email given ' . $_POST['domain']);
  die('no email given');
}
if (!$_POST['domain']) {
  $log->lwrite('no domain given ' . $_POST['domain']);
  die('no pod domain given');
}
if (!$_POST['url']) {
  $log->lwrite('no api given ' . $_POST['domain']);
  die('no API key for your stats');
}
if (strlen($_POST['url']) < 14) {
  $log->lwrite('api key too short ' . $_POST['domain']);
  die('API key bad needs to be like m58978-80abdb799f6ccf15e3e3787ee');
}

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT domain,pingdomurl FROM pods';
$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  if ($row['domain'] == $_POST['domain']) {
    $log->lwrite('domain already exists ' . $_POST['domain']);
    die('domain already exists');
  }
  if ($row['pingdomurl'] == $_POST['url']) {
    $log->lwrite('API key already exists ' . $_POST['domain']);
    die('API key already exists');
  }
}

//curl the header of pod with and without https
$chss = curl_init();
curl_setopt($chss, CURLOPT_URL, 'https://' . $_POST['domain'] . '/nodeinfo/1.0');
curl_setopt($chss, CURLOPT_POST, 0);
curl_setopt($chss, CURLOPT_HEADER, 0);
curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chss, CURLOPT_NOBODY, 0);
$outputssl = curl_exec($chss);
curl_close($chss);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_POST['domain'] . '/nodeinfo/1.0');
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_NOBODY, 0);
$output = curl_exec($ch);
curl_close($ch);

if (stristr($outputssl, 'nodeName')) {
  $log->lwrite('Your pod has ssl and is valid ' . $_POST['domain']);
  echo 'Your pod has ssl and is valid<br>';
  $valid = 1;
}
if (stristr($output, 'nodeName')) {
  $log->lwrite('Your pod does not have ssl but is a valid pod ' . $_POST['domain']);
  echo 'Your pod does not have ssl but is a valid pod<br>';
  $valid = 1;
}
if ($valid == '1') {
  $sql    = "INSERT INTO pods (domain, pingdomurl, email) VALUES($1, $2, $3)";
  $result = pg_query_params($dbh, $sql, [$_POST['domain'], $_POST['url'], $_POST['email']]);
  $result || die('Error in SQL query: ' . pg_last_error());

  $to      = $adminemail;
  $cc      = $_POST['email'];
  $subject = 'New pod added to podupti.me ';
  $message = sprintf(
    "%1\$s\n\nStats Url: %2\$s\n\nPod: %3\$s\n\n",
    'https://podupti.me',
    'https://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=' . $_POST['url'],
    'https://podupti.me/db/pull.php?debug=1&domain=' . $_POST['domain']
  );
  $message .= 'Your pod will not show right away, needs to pass a few checks, Give it a few hours!';
  $headers = 'From: ' . $_POST['email'] . "\r\nReply-To: " . $_POST['email'] . "\r\nCc: " . $_POST['email'] . "\r\n";
  @mail($to, $subject, $message, $headers);

  echo 'Data successfully inserted! Your pod will be reviewed and live on the list in a few hours!';

  pg_free_result($result);

  pg_close($dbh);
} else {
  $log->lwrite('Could not validate your pod on http or https, check your setup! ' . $_POST['domain']);
  echo 'Could not validate your pod on http or https, check your setup!<br>Take a look at <a href="https://' . $_POST['domain'] . '/nodeinfo/1.0">your /nodeinfo</a>';
}
$log->lclose();
