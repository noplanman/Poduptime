<?php
$debug=1;
//* Copyright (c) 2011-2016, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */
include('config.php');
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
    if (!$dbh) {
         die("Error in connection: " . pg_last_error());
     }
 $domain = isset($_GET['domain'])?$_GET['domain']:null;
	 $sql = "SELECT pingdomurl FROM pods WHERE domain = $1";
	 $result = pg_query_params($dbh, $sql, array($domain));
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }
$apikey = pg_fetch_all($result);
  $upti = curl_init();
  $curlurl = "https://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&responseTimes=1&responseTimesAverage=86400&apiKey=".$apikey[0]['pingdomurl'];
  curl_setopt($upti, CURLOPT_URL, $curlurl);
  curl_setopt($upti, CURLOPT_HEADER, 0);
  curl_setopt($upti, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($upti, CURLOPT_CONNECTTIMEOUT, 8);
  curl_setopt($upti, CURLOPT_FOLLOWLOCATION, true);
  $uptimerobot = curl_exec($upti);  curl_close($upti);
  $json_encap = "jsonUptimeRobotApi()"; $up2 = substr ($uptimerobot, strlen($json_encap) - 1, strlen ($uptimerobot) - strlen($json_encap)); $uptr = json_decode($up2); 
var_dump($uptimerobot);
echo "<br><br>UptimeRobot Json displayed in Human<br>"; 
echo "Name: ".$uptr->monitors->monitor{'0'}->friendlyname."<br>";
echo "Url: ".$uptr->monitors->monitor{'0'}->url."<br>";
echo "Interval: ".$uptr->monitors->monitor{'0'}->interval."ms<br>";
echo "Uptime: ".$uptr->monitors->monitor{'0'}->alltimeuptimeratio."%<br>";
echo "Response Time: ".$uptr->monitors->monitor{'0'}->responsetime{'0'}->value."ms<br>";
if ($uptr->monitors->monitor{'0'}->status == 2) {$live = "Up";}
if ($uptr->monitors->monitor{'0'}->status == 1) {$live = "Paused";}
if ($uptr->monitors->monitor{'0'}->status == 8) {$live = "Seems Down";}
if ($uptr->monitors->monitor{'0'}->status == 9) {$live = "Down";}
echo "Status: ".$live;


     pg_free_result($result);
     pg_close($dbh);

?>
