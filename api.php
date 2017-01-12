<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
($_GET['key'] ?? null) === '4r45tg' || die;

// Other parameters.
$_format   = $_GET['format'] ?? '';
$_method   = $_GET['method'] ?? '';
$_callback = $_GET['callback'] ?? '';

require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

if ($_format === 'georss') {
  echo <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">
<title>Diaspora Pods</title>
<subtitle>IP Locations of Diaspora pods on {$_SERVER['HTTP_HOST']}</subtitle>
<link href="https://{$_SERVER['HTTP_HOST']}/"/>

EOF;
  $sql    = "SELECT * FROM pods WHERE hidden <> 'yes'";
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  $numrows = pg_num_rows($result);
  while ($row = pg_fetch_array($result)) {
    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $summary  = sprintf(
      'This pod %1$s has been watched for %2$s months and its average ping time is %3$s with uptime of %4$s%% this month and was last checked on %5$s. On a score of 100 this pod is a %6$s right now',
      $pod_name,
      $row['monthsmonitored'],
      $row['responsetimelast7'],
      $row['uptimelast7'],
      $row['dateupdated'],
      $row['score']
    );
    $scheme   = $row['secure'] === 'true' ? 'https://' : 'http://';
    echo <<<EOF
<entry>
  <title>{$scheme}{$row['domain']}</title>
  <link href="{$scheme}{$row['domain']}"/>
  <id>urn:{$row['domain']}</id>
  <summary>Pod Location is: {$row['country']}
	&#xA;
{$summary}</summary>
  <georss:point>{$row['lat']} {$row['long']}</georss:point>
  <georss:featureName>{$row['domain']}</georss:featureName>
</entry>

EOF;
  }
  echo '</feed>';
} elseif ($_format === 'json') {
  $sql    = 'SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,pingdomurl,monthsmonitored,uptimelast7,responsetimelast7,local_posts,comment_counts,dateCreated,dateUpdated,dateLaststats,hidden FROM pods';
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  //json output, thx Vipul A M for fixing this
  header('Content-type: application/json');

  $numrows = pg_num_rows($result);
  $rows    = array_values(pg_fetch_all($result));
  $obj     = [
    'podcount' => $numrows,
    'pods'     => $rows,
  ];
  if ($_method === 'jsonp') {
    print $_callback . '(' . json_encode($obj) . ')';
  } else {
    print json_encode($obj);
  }
} else {
  $i      = 0;
  $sql    = "SELECT * FROM pods WHERE hidden <> 'yes' ORDER BY uptimelast7 DESC";
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  $numrows = pg_num_rows($result);
  while ($row = pg_fetch_array($result)) {
//    $status = $row['status'] === 'up' ? 'Online' : 'Offline';
//    $scheme = $row['secure'] === 'true' ? 'https://' : 'http://';
//    $class  = $row['secure'] === 'true' ? 'green' : 'red';

    $i++ > 0 && print ',';
    printf(
      '%1$s Up %2$s%% This Month - Located in: %3$s',
      $row['domain'],
      $row['uptimelast7'],
      $row['country']
    );
  }
}

pg_free_result($result);
pg_close($dbh);
