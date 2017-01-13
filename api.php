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
  $sql    = "SELECT name,monthsmonitored,responsetime,uptime_alltime,dateupdated,score,secure,domain,country,lat,long FROM pods";
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  $numrows = pg_num_rows($result);
  while ($row = pg_fetch_array($result)) {
    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $summary  = sprintf(
      'This pod %1$s has been watched for %2$s months and its average ping time is %3$s with uptime of %4$s%% this month and was last checked on %5$s. On a score of 100 this pod is a %6$s right now',
      $pod_name,
      $row['monthsmonitored'],
      $row['responsetime'],
      $row['uptime_alltime'],
      $row['dateupdated'],
      $row['score']
    );
    $scheme   = $row['secure'] === 't' ? 'https://' : 'http://';
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
  $sql    = 'SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,statsurl,monthsmonitored,uptime_alltime,responsetime,local_posts,comment_counts,date_created,date_updated,date_laststats,hidden,terms,sslexpire,uptime_custom,dnssec,softwarename,total_users,local_posts,comment_counts,service_facebook,service_twitter,service_tumblr,service_wordpress,service_xmpp FROM pods';
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
  $sql    = "SELECT domain,uptime_alltime,country,status FROM pods";
  $result = pg_query($dbh, $sql);
  $result || die('Error in SQL query: ' . pg_last_error());

  $numrows = pg_num_rows($result);
  while ($row = pg_fetch_array($result)) {

    $i++ > 0 && print ',';
    printf(
      '%1$s is %2$s now - online %3$s%% This Month - Located in: %4$s',
      $row['domain'],
      $row['status'],
      $row['uptime_alltime'],
      $row['country']
    );
  }
}

pg_free_result($result);
pg_close($dbh);
