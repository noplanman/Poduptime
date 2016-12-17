<?php

//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
if ($_GET['key'] != "4r45tg") {die;}
include('db/config.php');
$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
if (!$dbh) {
  die("Error in connection: " . pg_last_error());
}
if ($_GET['format'] == "georss") {
  echo <<<EOF
  <?xml version="1.0" encoding="utf-8"?>
  <feed xmlns="http://www.w3.org/2005/Atom"
  xmlns:georss="http://www.georss.org/georss">
  <title>Diaspora Pods</title>
  <subtitle>IP Locations of Diaspora pods on podupti.me</subtitle>
  <link href="http://podupti.me/"/>

EOF;
  $sql = "SELECT * FROM pods WHERE hidden <> 'yes'";
  $result = pg_query($dbh, $sql);
  if (!$result) {
   die("Error in SQL query: " . pg_last_error());
 }
  $numrows = pg_num_rows($result);
  while ($row = pg_fetch_array($result)) {
    $pod_name = htmlentities($row["name"], ENT_QUOTES);
    $tip="";
    $tip.="\n This pod {$pod_name} has been watched for {$row["monthsmonitored"]} months and its average ping time is {$row["responsetimelast7"]} with uptime of {$row["uptimelast7"]}% this month and was last checked on {$row["dateupdated"]}. ";
    $tip.="On a score of 100 this pod is a {$row["score"]} right now";
    if ($row["secure"] == "true") {$method = "https://";} else {$method = "http://";}
   echo <<<EOF
   <entry>
   <title>{$method}{$row['domain']}</title>
   <link href="{$method}{$row['domain']}"/>
   <id>urn:{$row['domain']}</id>
   <summary>Pod Location is: {$row['country']}
	&#xA;{$tip}</summary>
   <georss:point>{$row['lat']} {$row['long']}</georss:point>
   <georss:featureName>{$row['domain']}</georss:featureName>
   </entry>

EOF;
  }
  echo "</feed>";
}
elseif ($_GET['format'] == "json") {
  $sql = "SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,hgitdate,hgitref,pingdomurl,pingdomlast,monthsmonitored,uptimelast7,responsetimelast7,hruntime,hencoding,local_posts,comment_counts,dateCreated,dateUpdated,dateLaststats,hidden FROM pods";
  $result = pg_query($dbh, $sql);
  if (!$result) {
    die("Error in SQL query: " . pg_last_error());
  }
  $numrows = pg_num_rows($result);
  //json output, thx Vipul A M for fixing this
  header('Content-type: application/json');
  $rows=array_values(pg_fetch_all($result));
  $obj->podcount          = $numrows;
  $obj->pods             = $rows;
  if ($_GET['method'] == "jsonp") {
    print $_GET['callback'] . '(' . json_encode($obj) . ')';
  } else {
    print json_encode($obj);
  }
}
 else {
  $i=0;
  $sql = "SELECT * FROM pods WHERE hidden <> 'yes' ORDER BY uptimelast7 DESC";
  $result = pg_query($dbh, $sql);
  if (!$result) {
    die("Error in SQL query: " . pg_last_error());
  }
  $numrows = pg_num_rows($result);
  while ($row = pg_fetch_array($result)) {
    if ($row["status"] == "up"){$status="Online";} else {$status="Offline";}
    if ($row["secure"] == "true") {$method = "https://";$class="green";} else {$method = "http://";$class="red";}
    echo $row["domain"] ." Up ".$row["uptimelast7"]."% This Month - Located in: ".$row["country"];
    if ($i < ($numrows -1)) {
      echo ",";
    }
    $i++;

}

 pg_free_result($result);
 pg_close($dbh);
}
?>
