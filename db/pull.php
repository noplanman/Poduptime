<?php 
/** 
 * Copyright (c) 2011, David Morley. 
 * This file is licensed under the Affero General Public License version 3 or later. 
 * See the COPYRIGHT file. 
 */

require_once "Net/GeoIP.php";

define("CURL_POST", 0);
define("CURL_HEADER", 0);
define("CURL_CONNECTTIMEOUT", 5);
define("CURL_RETURNTRANSFER", 1);
define("CURL_NOBODY", 0);

define("DB_DRIVER","mysql");
define("DB_NAME","dbname");
define("DB_HOST","localhost");
define("DB_USER","dbUser");
define("DB_PASSWORD","dbPassword");

define("DEBUG", true);

class Pull {
	
	public static function getCurlResultAndInfo($url, &$result, &$info) {
		$curl = curl_init();
		
		curl_setopt($mv, CURLOPT_URL, $url);
		curl_setopt($mv, CURLOPT_POST, CURL_POST);
		curl_setopt($mv, CURLOPT_HEADER, CURL_HEADER);
		curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, CURL_CONNECTTIMEOUT);
		curl_setopt($mv, CURLOPT_RETURNTRANSFER, CURL_RETURNTRANSFER);
		curl_setopt($mv, CURLOPT_NOBODY, CURL_NOBODY);
		
		$result = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
	}
	
	public static function getCurlResult($url) {
		$curl = curl_init();

		curl_setopt($mv, CURLOPT_URL, $url);
		curl_setopt($mv, CURLOPT_POST, CURL_POST);
		curl_setopt($mv, CURLOPT_HEADER, CURL_HEADER);
		curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, CURL_CONNECTTIMEOUT);
		curl_setopt($mv, CURLOPT_RETURNTRANSFER, CURL_RETURNTRANSFER);
		curl_setopt($mv, CURLOPT_NOBODY, CURL_NOBODY);
		
		$result = curl_exec($curl);
		curl_close($curl);
		
		return $result;
	}

	public static function getDatabaseConnection() {
		$dsn = DB_DRIVER.":dbname=".DB_NAME.";host=".DB_HOST;
				
		try {
			$connection = new PDO($dsn, DB_USER, DB_PASSWORD);
			return $connection;
		} catch (PDOException $e) {
			die('Connection to databse failed: ' . $e->getMessage());
		}
	}
	
	public static function getRatings(&$adminRating=0, &$userRating=0, String $podUrl, PDO $db) {
		$adminRatingCounter = 0;
		$userRatingCounter = 0;
		$adminRatingTemp = 0;
		$userRatingTemp = 0;
		$sql = "SELECT * FROM rating_comments WHERE domain = ".$podUrl;
		
		$quotedSql = $db->quote($sql);
		
		if ($quotedSql === false) {
			die('Problem with SQL Statement. '.$sql);
		} else {
			$result = $db->query($quotedSql);
			
			if (!result) {
				die("Error fetching SQL Result for Ratings. Error: ".$result->errorCode());
			}
			
			while ($row = $result->fetchAll()) {
				if ($row['admin'] == 1) {
					$adminRatingCounter++;
					$adminRatingTemp += $row['rating'];
				} elseif ($row['admin'] == 0) {
					$userRatingCounter++;
					$userRatingTemp += $row['rating'];
				}
			}
			
			// Set the Ratingvalues
			$adminRating = round($adminRatingTemp/$adminRatingCounter,22);
			$userRating = round($userRatingTemp/$userRatingCounter, 2);
		}
	}
	
	public static function parseHeader($header, &$gitdate, &$gitrev, &$xdver, &$diasporaVersion, &$runtime, &$server, &$encoding) {
		
		preg_match('/X-Git-Update: (.*?)\n/', $header, $xgitdate);
		$gitdate = trim($xgitdate[1]);
			
		preg_match('/X-Git-Revision: (.*?)\n/',$header,$xgitrev);
		$gitrev = trim($xgitrev[1]);
			
		preg_match('/X-Diaspora-Version: (.*?)\n/',$header,$xdver);
		$dverr = split("-",trim($xdver[1]));
		$diasporaVersion = $dverr[0];
			
		preg_match('/X-Runtime: (.*?)\n/',$outputssl,$xruntime);
		$runtime = isset($xruntime[1]) ? trim($xruntime[1]) : null;
			
		preg_match('/Server: (.*?)\n/',$outputssl,$xserver);
		$server = isset($xserver[1]) ? trim($xserver[1]) : null;
			
		preg_match('/Content-Encoding: (.*?)\n/',$outputssl,$xencoding);
		if ($xencoding) {
			$encoding = trim($xencoding[1]);
		} else {
			$encoding = null;
		}

		if (DEBUG) {
			echo "GitUpdate: ".$gitdate."<br />";
			echo "GitRev: ".$gitrev."<br />";
			echo "Version code: ".$diasporaVersion."<br />";
			echo "Runtime: ".$runtime."<br />";
			echo "Server: ".$server."<br />";
			echo "Encoding: ".$encoding."<br />";
		}
		
	}
	
	public static function parseJSON($header, &$podName, &$registrationsOpen, &$totalUsers, &$activeUsersHalfyear, &$activeUsersMonthly, &$localPosts) {
		preg_match_all("/{(.*?)}/", $header, $JSONArray);
		$JSON = json_decode($JSONArray[0][0]);

		if ($JSON->registrations_open === true) {
			$registrationsOpen = 1;
		} else {
			$registrationsOpen = 0;
		}
		
		$podName = isset($JSON->name) ? $JSON->name : "null";
		$totalUsers = isset($JSON->total_users) ? $JSON->total_users : 0;
		$activeUsersHalfyear = isset($JSON->active_users_halfyear) ? $JSON->active_users_halfyear : 0;
		$activeUsersMonthly = isset($JSON->active_users_monthly) ? $JSON->active_users_monthly : 0;
		$localPosts = isset($JSON->local_posts) ? $JSON->local_posts : 0;
		
		if (DEBUG) {
			echo "Registrations Open: ".$registrationsOpen."<br />";
			echo "PodName: ".$podName."<br />";
			echo "Active user over half year: ".$activeUsersHalfyear."<br />";
			echo "Active user monthly: ".$activeUsersMonthly."<br />";
			echo "Local Posts: ".$localPosts."<br />";
		}	
	
	}
	
	public static function getMasterVersion() {
		//get master code version
		$masterVersionResult = Pull::getCurlResult("https://raw.github.com/diaspora/diaspora/master/config/defaults.yml");
		preg_match('/number: "(.*?)"/', $masterVersionResult, $version);

		if (DEBUG) {
			echo "MasterVersion: ".$masterVersion."<br>";
		} 
		
		return trim($version[1], '"');
	}
	
	public static function getPodList($domain, PDO $dbConnection) {
		if ($domain) {
			// Pull is requested for specific Domain
			$sql = $dbConnection->quote("SELECT domain,pingdomurl,score,datecreated FROM pods WHERE domain = ".$domain);
		} else {
			// General pull. Get all pods from Database
			$sql = $dbConnection->quote("SELECT domain,pingdomurl,score,datecreated,adminrating FROM pods");
		}
		
		$result = $dbConnection->query($sql);
		
		if (!$result) {
			if ($domain) {
				die("Error fetching SQL Result for Pod: ".$domain.". Error:" . $result->errorCode());
			} else {
				die("Error fetching SQL Result. Error: " . $result->errorCode());
			}
		} else {
			return $result;
		}
	}
	
	/**
	 * Cap the score at 20 and -20
	 * @param integer $score
	 */
	public static function capScore(&$score) {
		if ($score > 20) {
			$score = 20;
		} elseif ($score < -20) {
			$score = -20;
		}
	}
	
	/**
	 * Returns the IPv6 Address of the pod if there is any
	 * @param string $podurl
	 * @return string
	 */
	public static function getIPv6($podurl) {
		$command = escapeshellcmd('dig +nocmd '.$podurl.' aaaa +noall +short');
		return exec($command);
	}

	/**
	 * Returns the IPv4 Address of the pod, if there is any
	 * @param string $podurl
	 * @return string
	 */
	public static function getIPv4($podurl) {
		$command = escapeshellcmd('dig +nocmd '.$podurl.' a +noall +short');
		return exec($command);
	}
	
	/**
	 * Tries ti get a GeoIP based Location
	 * @param unknown $ipnum
	 * @param unknown $whois
	 * @param unknown $country
	 * @param unknown $city
	 * @param unknown $lat
	 * @param unknown $long
	 */
	public static function getGeoIPData($ipnum, &$whois, &$country, &$city, &$lat, &$long) {
		$geoip = Net_GeoIP::getInstance("GeoLiteCity.dat");
		try {
    		$location = $geoip->lookupLocation($ipnum);
			if ($debug) {
				echo "GEOIP: ".$location."<br>";
			}
		} catch (Exception $e) {
    		// 	Handle exception
		}
		
		$whois = "Country: ".$location->countryName."\n Lat:".$location->latitude." Long:".$location->longitude;
		$country = $location->countryName;
		$city = isset($location->city) ? iconv("UTF-8", "UTF-8//IGNORE", $location->city) : null;
		$lat = $location->latitude;
		$long = $location->longitude;
	}
	
	private static function getPingdomData($pingdomUrl, &$responsetime, &$months, &$uptime, &$live, &$score) {
		// Pod is monitored via pingdom
		$thismonth = "/".date("Y")."/".date("m");
		Pull::getCurlResultAndInfo($pingdomUrl.$thismonth,$pingdom,$info);

		if ($info['http_code'] == 200) {
		
			//response time
			preg_match_all('/<h3>Avg. resp. time this month<\/h3>
				        <p class="large">(.*?)</',$pingdom,$matcheach);
			$responsetime = $matcheach[1][0];
		
			//months monitored
			preg_match_all('/"historySelect">\s*(.*?)\s*<\/select/is',$pingdom,$matchhistory);
			$implodemonths = implode(" ", $matchhistory[1]);
		
			preg_match_all('/<option(.*?)/s',$implodemonths,$matchdates);
			$months = isset($matchdates[0])?count($matchdates[0]):0;
			//echo $matchdates[0];
			//uptime %
		
			preg_match_all('/<h3>Uptime this month<\/h3>\s*<p class="large">(.*?)%</',$pingdom,$matchper);
			$uptime = isset($matchper[1][0])?preg_replace("/,/", ".", $matchper[1][0]):0;
		
			if (strpos($pingdom,"class=\"up\"")) {
				$live="up";
			} elseif (strpos($pingdom,"class=\"down\"")) {
				$live="down";
			} elseif (strpos($pingdom,"class=\"paused\"")) {
				$live="paused";
			} else {
				$live="error";
				$score -= 2;
			}
		} else {
			//pingdom url is <> 200 so stats are gone, lower score
			$score -= 2;
		}
		
		if (DEBUG) {
			echo "Pingdom - Url: ".$pingdomUrl.$thismonth."<br />";
			echo "Pingdom code: ".$info['http_code']."<br />";
			echo "Responsetime: ".$responsetime."<br />";
			echo "Months: ".$months."<br />";
			echo "Live: ".$live."<br />";
			echo "Score: ".$score."<br />";
		}
		
	}
	
	private static function getUptimerobotData($pingdomUrl, &$responsetime, &$months, &$uptime, &$live) {
		//do uptimerobot API instead
		$uptimerobot = Pull::getCurlResult($pingdomUrl);
		$json_encap = "jsonUptimeRobotApi()";
		$up2 = substr ($uptimerobot, strlen($json_encap) - 1, strlen ($uptimerobot) - strlen($json_encap));
			
		$JSON = json_decode($up2);
		$responsetime = 'n/a';
		$uptime = $JSON->monitors->monitor{'0'}->alltimeuptimeratio;
		$diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($dateadded));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		
		switch ($JSON->monitors->monitor{'0'}->status) {
			case 1:
				$live = "Paused";
				break;
			case 2:
				$live = "Up";
				break;
			case 8:
				$live = "Seems Down";
				break;
			case 9:
				$live = "Down";
				break;
		}
		
		if (DEBUG) {
			echo "UptimeRobot - Url: ".$pingdomUrl."<br />";
			echo "Responsetime: ".$responsetime."<br />";
			echo "Months: ".$months."<br />";
			echo "Live: ".$live."<br />";
		}
	}
	
	public static function getRobotData($pingdomUrl, &$responsetime, &$months, &$uptime, &$live, &$score) {
		$month = 0;
		$uptime = 0;
		
		if (strpos($pingdomUrl, "pingdom.com")) {
			Pull::getPingdomData($pingdomUrl, $responsetime, $months, $uptime, $live, $score);
		} else {
			Pull::getUptimerobotData($pingdomUrl, $responsetime, $months, $uptime, $live);
		}
	}
	
	public static function writeData(PDO $connection, $gitdate, $encoding, $secure, $hidden, $runtime, $gitrev, $ipnum, $ipv6, $months, $uptime, $live, $pingdomdate, $timenow, $responsetime, $score, $adminRating, $country, $city, $state, $lat, $long, $diasporaVersion, $whois, $userRating, $xdver, $masterVersion, $registrationsOpen, $totalUsers, $activeUsersHalfyear, $activeUsersMonthly, $localPosts, $podName, $domain) {
		
		$sql = "UPDATE pods SET Hgitdate=".$gitDate.", Hencoding=".$encoding.", secure=".$secure.", hidden=".$hidden.", Hruntime=".$runtime.", ";
		$sql .= "Hgitref=".$gitrev.", ip=".$ipnum.", ipv6=".$ipv6.", monthsmonitored=".$months.", uptimelast7=".$uptime.", status=".$live.", ";
		$sql .= "dateLaststats=".$pingdomdate.", dateUpdated=".$timenow.", responsetimelast7=".$responsetime.", score=".$score.", ";
		$sql .= "adminrating=".$adminRating.", country=".$country.", city=".$city.", state=".$state.", lat=".$lat.", long=".$long.", ";
		$sql .= "postalcode='', connection=".$diasporaVersion.", whois=".$whois.", userrating=".$userRating.", longversion=".$xdver.", ";
		$sql .= "shortversion=".$diasporaVersion.", masterversion=".$masterVersion.", signup=".$registrationsOpen.", total_users=".$totalUsers.", ";
		$sql .= "active_users_halfyear=".$activeUsersHalfyear.", active_users_monthly=".$activeUsersMonthly.", local_posts=".$localPosts.", ";
		$sql .= "name=".$podName;
		$sql .= "WHERE";
		$sql .= "domain=".$domain;
		
		$escapedSql = $connection->quote($sql); 
		
		if (!$escapedSql) {
			die("Error escaping SQL query: ".$sql);
		} else {
			$result = $connection->query($escapedSql);
			if (!$result) {
				die("Error executing SQL query: ". $result->errorCode());
			}
		}
	}
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
$months 				= ""; 
$uptime 				= ""; 
$live 					= ""; 
$pingdomdate 			= ""; 
$timenow 				= ""; 
$responsetime 			= ""; 
$score 					= ""; 
$adminRating 			= ""; 
$country 				= ""; 
$city 					= ""; 
$state 					= ""; 
$lat 					= ""; 
$long 					= ""; 
$diasporaVersion		= ""; 
$whois 					= ""; 
$userRating 			= ""; 
$xdver 					= ""; 
$registrationsOpen		= ""; 
$totalUsers 			= ""; 
$activeUsersHalfyear 	= "";
$activeUsersMonthly 	= ""; 
$localPosts 			= ""; 
$podName 				= ""; 

$masterVersion = Pull::getMasterVersion();
$dbh = Pull::getDatabaseConnection();

// Check if the pullrequest is made for a specific pod domain
$domain = isset($_GET['domain']) ? $_GET['domain'] : null;
$result = Pull::getPodList($domain, $dbh);

// Iterate over each Pod in the resultset
while ($row = $result->fetchAll()) {
	
	$podSecure = false;
	
	$domain = $row['domain'];
	$score = $row['score'];
	//$datecreated = $row['datecreated']; Not used
	$adminRating = $row['adminrating'];
	
	if (DEBUG) {
		echo("Pod: <b>".$domain."</b><br />");
	}
	// Get Ratings for Pod
	Pull::getRatings($adminRating, $userRating, $domain, $dbh);
	
	// Get Header from Pod
	if (!$header = Pull::getCurlResult("https://".$domain."/statistics.json")) {
		// No https connection possible, try http instead
		$header = Pull::getCurlResult("http://".$domain."/statistics.json");
	} else {
		// Got https connection. Pod seems to be secure
		$podSecure = true;
	}
	
	if (DEBUG) {
		if ($podSecure) {
			echo "Pod has SSL connection<br />";
		} else {
			echo "Pod has no SSL connection<br />";
		}
		echo "Pod Header: ".$header."<br />";
	}
	
	if ($header) {
		// Parse Header Data, if there is a header
		Pull::parseHeader($header, $gitdate, $gitrev, $xdver, $diasporaVersion, $runtime, $server, $encoding);
		Pull::parseJSON($header, $podName, $registrationsOpen, $totalUsers, $activeUsersHalfyear, $activeUsersMonthly, $localPosts);
		
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
	
	if ($score > 5) {
		$hidden = "no";
	} else {
		$hidden = "yes";
	}
	
	if (DEBUG) {
		echo "Hidden: ".$hidden;
	}
	
	Pull::capScore($score);
	$ip6num = Pull::getIPv6($domain);
	if ($ip6num == '') {
		$podHasIPv6 = "no";
	} else {
		$podHasIPv6 = "yes";
	}
	$ipnum = Pull::getIPv4($domain);
	Pull::getGeoIPData($ipnum, $whois, $country, $city, $lat, $long);
	Pull::getRobotData($row['pingdomurl'], $responsetime, $months, $uptime, $live, $score);
	Pull::writeData($dbh, $gitdate, $encoding, $secure, $hidden, $runtime, $gitrev, $ipnum, $ipv6, $months, $uptime, $live, $pingdomdate, $timenow, $responsetime, $score, $adminRating, $country, $city, $state, $lat, $long, $diasporaVersion, $whois, $userRating, $xdver, $masterVersion, $registrationsOpen, $totalUsers, $activeUsersHalfyear, $activeUsersMonthly, $localPosts, $podName, $domain);	
	
	if (DEBUG) {
		echo "<br />Score out of 20: ".$score."<br />";
		echo "Success <br />";
	}
	
} // end while	
unset($dbh);
?>
