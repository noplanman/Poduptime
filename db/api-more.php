<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
//this is just a single api for a pod for the android app to get data

// Required parameters.
($_domain = $_GET['domain'] ?? null) || die('no domain given');

// Other parameters.
$_format = $_GET['format'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT hgitdate,id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,pingdomurl,monthsmonitored,uptimelast7,responsetimelast7,local_posts,comment_counts,dateCreated,dateUpdated,dateLaststats,hidden FROM pods_apiv1 WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());

while ($row = pg_fetch_array($result)) {
  if ($_format === 'json') {
    echo json_encode($row);
  } else {
    echo 'Status: ' . $row['status'] . '<br>';
    echo 'Last Git Pull: ' . $row['hgitdate'] . '<br>';
    echo 'Uptime This Month ' . $row['uptimelast7'] . '<br>';
    echo 'Months Monitored: ' . $row['monthsmonitored'] . '<br>';
    echo 'Response Time: ' . $row['responsetimelast7'] . '<br>';
    echo 'User Rating: ' . $row['userrating'] . '<br>';
    echo 'Server Location: ' . $row['country'] . '<br>';
    echo 'Latitude: ' . $row['lat'] . '<br>';
    echo 'Longitude: ' . $row['long'] . '<br>';
  }
}
