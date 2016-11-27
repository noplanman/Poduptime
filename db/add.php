<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<?php
$valid=0;
include('config.php');
include('../logging.php');
$log = new Logging();
$log->lfile($log_dir."/add.php.log");
if (!$_POST['url']){
  echo "no url given";$log->lwrite('no url given '.$_POST['domain']);
  die;
}
if (!$_POST['email']){
  echo "no email given";$log->lwrite('no email given '.$_POST['domain']);
  die;
}
if (!$_POST['domain']){
  echo "no pod domain given";$log->lwrite('no domain given '.$_POST['domain']);
  die;
}
if (!$_POST['url']){
  echo "no API key for your stats";$log->lwrite('no api given '.$_POST['domain']);
  die;
}
if (strlen($_POST['url']) < 14){
  echo "API key bad needs to be like m58978-80abdb799f6ccf15e3e3787ee";$log->lwrite('api key too short '.$_POST['domain']);
  die;
}
$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
if (!$dbh) {
  die("Error in connection: " . pg_last_error());
}
$sql = "SELECT domain,pingdomurl FROM pods";
$result = pg_query($dbh, $sql);
if (!$result) {
  die("Error in SQL query: " . pg_last_error());
}
while ($row = pg_fetch_array($result)) {
  if ($row["domain"] == $_POST['domain']) {
    echo "domain already exists";$log->lwrite('domain already exists '.$_POST['domain']);die;
  }
  if ($row["pingdomurl"] == $_POST['url']) {
    echo "API key already exists";$log->lwrite('API key already exists '.$_POST['domain']);die;
  }
}

//curl the header of pod with and without https
$chss = curl_init();
curl_setopt($chss, CURLOPT_URL, "https://".$_POST['domain']."/nodeinfo/1.0");
curl_setopt($chss, CURLOPT_POST, 0);
curl_setopt($chss, CURLOPT_HEADER, 0);
curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chss, CURLOPT_NOBODY, 0);
$outputssl = curl_exec($chss);
curl_close($chss);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://".$_POST['domain']."/nodeinfo/1.0");
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_NOBODY, 0);
$output = curl_exec($ch);
curl_close($ch);

if (stristr($outputssl, 'nodeName')) {
  echo "Your pod has ssl and is valid<br>";$log->lwrite('Your pod has ssl and is valid '.$_POST['domain']);
  $valid=1;
}
if (stristr($output, 'nodeName')) {
  echo "Your pod does not have ssl but is a valid pod<br>";$log->lwrite('Your pod does not have ssl but is a valid pod '.$_POST['domain']);
  $valid=1;
}
if ($valid=="1") {    
  $sql = "INSERT INTO pods (domain, pingdomurl, email) VALUES($1, $2, $3)";
  $result = pg_query_params($dbh, $sql, array($_POST['domain'], $_POST['url'], $_POST['email']));
  if (!$result) {
    die("Error in SQL query: " . pg_last_error());
  }
  $to = $adminemail;
  $cc = $_POST["email"];
  $subject = "New pod added to podupti.me ";
  $message.= "https://podupti.me\n\n Stats Url: https://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=" . $_POST["url"] . "\n\n Pod: https://podupti.me/db/pull.php?debug=1&domain=" . $_POST["domain"] . "\n\n";
  $message.= "Your pod will not show right away, needs to pass a few checks, Give it a few hours!";
  $headers = "From: ".$_POST["email"]."\r\nReply-To: ".$_POST["email"]."\r\nCc: " . $_POST["email"] . "\r\n";
  @mail( $to, $subject, $message, $headers );    

  echo "Data successfully inserted! Your pod will be reviewed and live on the list in a few hours!";
    
  pg_free_result($result);
    
  pg_close($dbh);
} else {
  echo "Could not validate your pod on http or https, check your setup!<br>Take a look at <a href='https://".$_POST['domain']."/nodeinfo/1.0'>your /nodeinfo</a>";$log->lwrite('Could not validate your pod on http or https, check your setup! '.$_POST['domain']);
}
$log->lclose();
?>
