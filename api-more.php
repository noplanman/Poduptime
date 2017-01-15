<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
//this is just a single api for a pod for the android app to get data

// Required parameters.
($_domain = $_GET['domain'] ?? null) || die('no domain given');

// Other parameters.
$_format = $_GET['format'] ?? '';

require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,stats_apikey,monthsmonitored,uptime_alltime,responsetime,local_posts,comment_counts,date_created,date_updated,date_laststats,hidden FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  if ($_format === 'json') {
    echo json_encode($row);
  } else {
    echo 'Status: ' . $row['status'] . '<br>';
    echo 'Last Git Pull: ' . $row['hgitdate'] . '<br>';
    echo 'Uptime This Month ' . $row['uptime_alltime'] . '<br>';
    echo 'Months Monitored: ' . $row['monthsmonitored'] . '<br>';
    echo 'Response Time: ' . $row['responsetime'] . '<br>';
    echo 'User Rating: ' . $row['userrating'] . '<br>';
    echo 'Server Location: ' . $row['country'] . '<br>';
    echo 'Latitude: ' . $row['lat'] . '<br>';
    echo 'Longitude: ' . $row['long'] . '<br>';
  }
}
