<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.
if ($_GET['key'] != "4r45tg") {exit;}
 include('config.php');
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
if ($row["secure"] == "true") {$method = "https://";} else {$method = "http://";}
echo <<<EOF
  <entry>
    <title>{$method}{$row['domain']}</title>
    <link href="{$method}{$row['domain']}"/>
    <id>urn:{$row['domain']}</id>
    <summary>Location {$row['city']}, {$row['state']}<![CDATA[<br/>]]>Status {$row['status']}<![CDATA[<br/>]]>Uptime last 7 days {$row['uptimelast7']}<![CDATA[<br/>]]>Response Time {$row['responsetimelast7']}<![CDATA[<br/>]]>Last Git Update {$row['hgitdate']}<![CDATA[<br/>]]>Listed for {$row['monthsmonitored']} months<![CDATA[<br/>]]>Pingdom URL <![CDATA[<A href="{$row['pingdomurl']}">{$row['pingdomurl']}</a>]]></summary>
    <georss:point>{$row['lat']} {$row['long']}</georss:point>
    <georss:featureName>{$row['domain']}</georss:featureName>
  </entry>

EOF;
}
echo "</feed>";

}

elseif ($_GET['format'] == "json") {
 $sql = "SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,hgitdate,hgitref,pingdomurl,pingdomlast,monthsmonitored,uptimelast7,responsetimelast7,hruntime,hencoding,dateCreated,dateUpdated,dateLaststats,hidden FROM pods";
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
 print json_encode($obj);
} else {
 $i=0;
 $sql = "SELECT * FROM pods WHERE hidden <> 'yes' ORDER BY Hgitdate DESC, uptimelast7 DESC";
 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }
 $numrows = pg_num_rows($result);
 while ($row = pg_fetch_array($result)) {
  if ($row["status"] == "up"){$status="Online";}else{$status="Offline";} 
  if ($row["secure"] == "true") {$method = "https://";$class="green";} else {$method = "http://";$class="red";}
  echo $method.$row["domain"] ." - ".$status." Now - Up ".$row["uptimelast7"]." This Month";
  if ($i < ($numrows -1)) {
    echo ",";
  }
$i++;
 }
}


 pg_free_result($result);       
 pg_close($dbh);
?>
