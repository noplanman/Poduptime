<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<?php
$valid=0;
 include('config.php');
if (!$_POST['url']){
  echo "no url given";
 die;
}
if (!$_POST['email']){
  echo "no email given";
 die;
}
if (!$_POST['domain']){
  echo "no pod domain given";
 die;
}
if (!$_POST['url']){
  echo "no API key for your stats";
 die;
}
if (strlen($_POST['url']) < 14){
  echo "API key bad needs to be like m58978-80abdb799f6ccf15e3e3787ee";
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
echo "domain already exists";die;
}
if ($row["pingdomurl"] == $_POST['url']) {
echo "API key already exists";die;
}
 }

     //curl the header of pod with and without https

        $chss = curl_init();
        curl_setopt($chss, CURLOPT_URL, "https://".$_POST['domain']."/users/sign_in");
        curl_setopt($chss, CURLOPT_POST, 1);
        curl_setopt($chss, CURLOPT_HEADER, 1);
        curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chss, CURLOPT_NOBODY, 1);
        $outputssl = curl_exec($chss);
        curl_close($chss);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".$_POST['domain']."/users/sign_in");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $output = curl_exec($ch);
        curl_close($ch);

if (stristr($outputssl, 'Set-Cookie: _diaspora_session=')) {
  echo "Your pod has ssl and is valid<br>";
  $valid=1;
}
if (stristr($output, 'Set-Cookie: _diaspora_session=')) {
  echo "Your pod does not have ssl but is a valid pod<br>";
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
     $message.= "http://podupti.me\n\n Stats Url: http://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=" . $_POST["url"] . "\n\n Pod: http://podupti.me/db/pull.php?debug=1&domain=" . $_POST["domain"] . "\n\n";
     $message.= "Your pod will not show right away, needs to pass a few checks, Give it a few hours!";
     $headers = "From: ".$_POST["email"]."\r\nReply-To: ".$_POST["email"]."\r\nCc: " . $_POST["email"] . "\r\n";
     @mail( $to, $subject, $message, $headers );    

     echo "Data successfully inserted! Your pod will be reviewed and live on the list in a few hours!";
    
     pg_free_result($result);
    
     pg_close($dbh);
} else {
echo "Could not validate your pod on http or https, check your setup!";
}

?>
