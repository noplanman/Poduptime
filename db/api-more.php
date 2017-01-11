<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
//this is just a single api for a pod for the android app to get data

// Required parameters.
($_url = $_GET['url'] ?? null) || die('no url given');

// Other parameters.
$_format = $_GET['format'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = 'SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,pingdomurl,monthsmonitored,uptimelast7,responsetimelast7,local_posts,comment_counts,dateCreated,dateUpdated,dateLaststats,hidden FROM pods WHERE domain = $1';
$result = pg_query_params($dbh, $sql, [$_url]);
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
    echo 'Server Country: ' . $row['country'] . '<br>';
    echo 'Server State: ' . $row['state'] . '<br>';
    echo 'Server City: ' . $row['city'] . '<br>';
    echo 'Latitude: ' . $row['lat'] . '<br>';
    echo 'Longitude: ' . $row['long'] . '<br>';
  }
}
pg_free_result($result);
pg_close($dbh);
