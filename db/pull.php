<?php
 error_reporting(E_ALL);
/** 
 * Copyright (c) 2011, David Morley. 
 * This file is licensed under the Affero General Public License version 3 or later. 
 * See the COPYRIGHT file. 
 */


require_once 'config.inc.php';
require_once 'pull.class.php';

if (DEBUG) {
	echo "Starting script<br/>";
}
// Inititialize variables

$state 					= ""; // Seems to be always empty. Deprecated?
$pingdomdate 			= date('Y-m-d H:i:s'); // is always the current date
$timenow 				= date('Y-m-d H:i:s'); // is always the current date
$gitdate 				= "";
$encoding 				= "";
$secure 				= ""; 
$hidden 				= ""; 
$runtime 				= ""; 
$gitrev 				= ""; 
$ipnum 					= ""; 
$ipv6 					= ""; 
$months 				= 0; 
$uptime 				= 0; 
$live 					= "";  
$responsetime 			= ""; 
$score 					= ""; 
$adminRating 			= ""; 
$country 				= ""; 
$city 					= ""; 
$lat 					= ""; 
$long 					= ""; 
$diasporaVersion		= ""; 
$whois 					= ""; 
$userRating 			= ""; 
$xdver 					= ""; 
$registrationsOpen		= 0; 
$totalUsers 			= 0; 
$activeUsersHalfyear 	= 0;
$activeUsersMonthly 	= 0; 
$localPosts 			= 0; 
$podName 				= ""; 

$masterVersion = Pull::getMasterVersion();
$dbh = Pull::getDatabaseConnection();

// Check if the pullrequest is made for a specific pod domain
$domain = isset($_GET['domain']) ? $_GET['domain'] : null;
$result = Pull::getPodList($domain, $dbh);

// Iterate over each Pod in the resultset
foreach ($result->fetchAll() as $row) {

	$podSecure = "false";
	
	$domain = $row['domain'];
	$score = $row['score'];
	$datecreated = $row['datecreated'];
	$adminRating = $row['adminrating'];
	
	if (DEBUG) {
		echo("Pod: <b>".$domain."</b><br />");
	}
	// Get Ratings for Pod
	Pull::getRatings($adminRating, $userRating, $domain, $dbh);
	
	// Get Header from Pod
	$header = Pull::getHeaderFromPod($domain, $podSecure);
	
	if (DEBUG) {
		if ($podSecure == "true") {
			echo "Pod has SSL connection<br />";
		} else {
			echo "Pod has no SSL connection<br />";
		}
		echo "Pod Header: ".$header."<br />";
	}
	
	if ($header) {
		// Parse Header Data, if there is a header
		Pull::parseHeader($header, $gitdate, $gitrev, $xdver, $diasporaVersion, $runtime, $server, $encoding);
		Pull::parseJSON($header, $podName, $registrationsOpen, $totalUsers, $activeUsersHalfyear, $activeUsersMonthly, $localPosts, $diasporaVersion, $xdver);
		
		if (!$diasporaVersion) {
			// No Diaspora-Version identifier. might not be trustable?
			$score -= 2;
			if (DEBUG) {
				echo("No Diaspora Version-identifier. Reducing points.<br />");
			}	
		} else {
			$score++;
			if (DEBUG) {
				echo("Everything allright. Increasing points.<br />");
			}
		}
	} else {
		// No header, no connection
		$score--;
		if (DEBUG) {
			echo("No header. Reducing points.<br />");
		}
	}
	
	

	// Get IPv6 if present
	$ip6num = Pull::getIPv6($domain);
	if ($ip6num == '') {
		$podHasIPv6 = "no";
	} else {
		$podHasIPv6 = "yes";
	}

	//Get IPv4 if present
	$ipnum = Pull::getIPv4($domain);

	// Try to get the position of the Pod via GeoIP
	Pull::getGeoIPData($ipnum, $whois, $country, $city, $lat, $long);

	// Pull the uptimedata
	$robotData = Pull::getRobotData($row['pingdomurl'], $datecreated, $responsetime, $months, $uptime, $live, $score);

	if ($robotData) {
		// All data is present.
		// Cap the score
		Pull::capScore($score);

		// Check if the Pod should be hidden or not
		if ($score > 5) {
			$hidden = "no";
		} else {
			$hidden = "yes";
		}

        if (DEBUG) {
            echo "Hidden: ".$hidden."<br />";
        }

		// Update Database entry
		Pull::writeData($dbh, $gitdate, $encoding, $podSecure, $hidden, $runtime, $gitrev, $ipnum,
						$ip6num, $months, $uptime, $live, $pingdomdate, $timenow, $responsetime, 
						$score, $adminRating, $country, $city, $state, $lat, $long, 
						$diasporaVersion, $whois, $userRating, $xdver, $masterVersion, 
						$registrationsOpen, $totalUsers, $activeUsersHalfyear, $activeUsersMonthly, 
						$localPosts, $podName, $domain);
		if (DEBUG) {
			echo "<br />Score out of 20: ".$score."<br />";
			echo "Success <br /><hr><br />";
		}
	} else {
		
		if (DEBUG) {
			echo "Not succesfull.<br /><hr><br />";
		}
	}	
	
} // end while	
unset($dbh);
?>
