<?php
/**
 * Copyright (c) 2011, David Morley. 
 * This file is licensed under the Affero General Public License version 3 or later. 
 * See the COPYRIGHT file.
 */

if ($_GET['key'] != "4r45tg") {
	exit;
} 

require_once 'db/config.inc.php';
require_once 'db/db.class.php';
 
$dbConnection = DB::connectDB();
if (!$dbConnection) {
	die("Error in connection: " . $dbConnection->errorInfo()[2]);
}

if (isset($_GET['format'])) {
	if ($_GET['format'] == "georss") {
		echo '
		<?xml version="1.0" encoding="utf-8"?>
		<feed xmlns="http://www.w3.org/2005/Atom" 
		  xmlns:georss="http://www.georss.org/georss">
		  <title>Diaspora Pods</title>
		  <subtitle>IP Locations of Diaspora pods on podupti.me</subtitle>
		  <link href="http://podupti.me/"/>
		';
		 $sql = "SELECT * FROM pods WHERE hidden <> 'yes'";
	 
		 $result = $dbConnection->query($sql); 
	 	if (!$result) {
	    	die("Error in SQL query: " . $dbConnection->errorInfo()[2]);
	 	}
		
		foreach ($result->fetchAll() as $row) {
			if ($row["secure"] == "true") {
				$method = "https://";
			} else {
				$method = "http://";
			}
			echo '
			  <entry>
			    <title>{'.$method.'}{'.$row['domain'].'}</title>
			    <link href="{'.$method.'}{'.$row['domain'].'}"/>
			    <id>urn:{'.$row['domain'].'}</id>
			    <summary>Location {'.$row['city'].'}, {'.$row['state'].'}<![CDATA[<br/>]]>Status {'.$row['status'].'}<![CDATA[<br/>]]>Uptime last 7 days {'.$row['uptimelast7'].'}<![CDATA[<br/>]]>Response Time {'.$row['responsetimelast7'].'}<![CDATA[<br/>]]>Last Git Update {'.$row['hgitdate'].'}<![CDATA[<br/>]]>Listed for {'.$row['monthsmonitored'].'} months<![CDATA[<br/>]]>Pingdom URL <![CDATA[<A href="{'.$row['pingdomurl'].'}">{'.$row['pingdomurl'].'}</a>]]></summary>
			    <georss:point>{'.$row['lat'].'} {'.$row['long'].'}</georss:point>
			    <georss:featureName>{'.$row['domain'].'}</georss:featureName>
			  </entry>
			
			';
		}
		
		echo "</feed>";
	} elseif ($_GET['format'] == "json") {
		$obj = new stdClass();
		$sql = "SELECT id,domain,status,secure,score,userrating,adminrating,city,state,country,lat,long,ip,ipv6,hgitdate,hgitref,pingdomurl,pingdomlast,monthsmonitored,uptimelast7,responsetimelast7,hruntime,hencoding,dateCreated,dateUpdated,dateLaststats,hidden FROM pods";
		$result = $dbConnection->query($sql);
	 	if (!$result) {
	    	die("Error in SQL query: " . $dbConnection->errorInfo()[2]);
	 	}
		
		//json output, thx Vipul A M for fixing this
	 	header('Content-type: application/json');
	 	$rows = array_values($result->fetchAll());
	 	
	 	$obj->podcount = count($rows);
	 	$obj->pods = $rows;
	 	
	 	if (isset($_GET['method']) && $_GET['method'] == "jsonp") {
	 		if (isset($_GET['callback'])) {
	    		print $_GET['callback'] . '(' . json_encode($obj) . ')';
	 		} else {
	 			die("Parameter callback is missing.");
	 		}
	 	} else {
	   		print json_encode($obj);
	 	}
	}
} else {
 	$sql = "SELECT * FROM pods WHERE hidden <> 'yes' ORDER BY uptimelast7 DESC";
 	$result = $dbConnection->query($sql);
 	if (!$result) {
	 	die("Error in SQL query: " . $dbConnection->errorInfo()[2]);
 	}
	foreach ($result->fetchAll() as $row) {
		if ($row["status"] == "up"){
			$status="Online";
		} else {
			$status="Offline";
		} 
  		if ($row["secure"] == "true") {
  			$method = "https://";$class="green";
  		} else {
  			$method = "http://";$class="red";
  		}
  		echo $row["domain"] ." Up ".$row["uptimelast7"]."% This Month - Located in: ".$row["country"];
  		echo ",";
 	}
}

?>
