<?php
//* Copyright (c) 2011-2016, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */
$debug = 1;

// Other parameters.
$_domain = $_GET['domain'] ?? null;

require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT stats_apikey FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());

$apikey = pg_fetch_all($result);
$upti   = curl_init();
$key    = $apikey[0]['stats_apikey'];
$data   = ['all_time_uptime_ratio' => 1, 'format' => 'json', 'custom_uptime_ratios' => '7-30-60-90', 'response_times' => 1, 'response_times_average' => 86400, 'api_key' => $key, 'callback' => 'jsonpUptimeRobot'];
curl_setopt($upti, CURLOPT_URL, 'https://api.uptimerobot.com/v2/getMonitors');
curl_setopt($upti, CURLOPT_HEADER, 0);
curl_setopt($upti, CURLOPT_POST, 1);
curl_setopt($upti, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($upti, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($upti, CURLOPT_CONNECTTIMEOUT, 8);
$uptimerobot = curl_exec($upti);
curl_close($upti);
$json_encap = 'jsonpUptimeRobot()';
$up2        = substr($uptimerobot, strlen($json_encap) - 1, strlen($uptimerobot) - strlen($json_encap));
$uptr       = json_decode($up2);
echo '<b>UptimeRobot Json displayed in Human</b><br><br>';
echo 'Name: ' . $uptr->monitors[0]->friendly_name . '<br>';
echo 'Url: ' . $uptr->monitors[0]->url . '<br>';
echo 'Interval: ' . $uptr->monitors[0]->interval . 'ms<br>';
echo 'Uptime: ' . $uptr->monitors[0]->all_time_uptime_ratio . '%<br>';
echo 'Response Time: ' . round($uptr->monitors[0]->average_response_time) . 'ms<br>';
if ($uptr->monitors[0]->status == 2) {
  $live = 'Up';
}
if ($uptr->monitors[0]->status == 0) {
  $live = 'Paused';
}
if ($uptr->monitors[0]->status == 1) {
  $live = 'Not Checked Yet';
}
if ($uptr->monitors[0]->status == 8) {
  $live = 'Seems Down';
}
if ($uptr->monitors[0]->status == 9) {
  $live = 'Down';
}
echo 'Status: ' . $live;
pg_free_result($result);
pg_close($dbh);
