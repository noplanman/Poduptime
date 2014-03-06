<?php 
//* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

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
	
	public static function getCurlResult ($url) {
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
}

//get master code version
$masterVersionResult = Pull::getCurlResult("https://raw.github.com/diaspora/diaspora/master/config/defaults.yml");
preg_match('/number: "(.*?)"/', $masterVersionResult, $version);
$masterVersion = trim($version[1], '"');

if ($debug) {
	echo "Masterversion: ".$masterVersion."<br>";
} 
 
$dbh = Pull::getDatabaseConnection();

// Check if the pullrequest is made for a specific pod domain
$domain = isset($_GET['domain']) ? $_GET['domain'] : null;

if ($domain) {
	// Pull is requested for specific Domain
	$sql = $dbh->quote("SELECT domain,pingdomurl,score,datecreated FROM pods WHERE domain = ".$domain);
} else {
	// General pull. Get all pods from Database
	$sql = $dbh->quote("SELECT domain,pingdomurl,score,datecreated,adminrating FROM pods");
}

$result = $dbh->query($sql);

if (!$result) {
	if ($domain) {
		die("Error fetching SQL Result for Pod: ".$domain.". Error:" . $result->errorCode());
	} else {
		die("Error fetching SQL Result. Error: " . $result->errorCode());
	}
}

// Iterate over each Pod in the resultset

while ($row = $result->fetchAll()) {
	
	$domain = $row['domain'];
	$score = $row['score'];
	$datecreated = $row['datecreated'];
	$adminrating = $row['adminrating'];
	
	
	$numrows = pg_num_rows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$domain =  $row[$i]['domain'];
		$score = $row[$i]['score'];
		$dateadded = $row[$i]['datecreated'];
		$admindb = $row[$i]['adminrating'];

		//get ratings
 		$userrate=0;
 		$adminrate=0;
 		$userratingavg = array();
 		$adminratingavg = array();
 		$userrating = array();
 		$adminrating = array();
 		
 		$sqlforr = "SELECT * FROM rating_comments WHERE domain = $1";
 		$ratings = pg_query_params($dbh, $sqlforr, array($domain));
 		
 		if (!$ratings) {
 			die("Error in SQL query2: " . pg_last_error());
 		}
 		
 		$numratings = pg_num_rows($ratings);
 		while($myrow = pg_fetch_assoc($ratings)) {
 			if ($myrow['admin'] == 0) {
 				$userratingavg[] = $myrow['rating'];
 				$userrate++;
 			} elseif ($myrow['admin'] == 1) {
     			$adminratingavg[] = $myrow['rating'];
     			$adminrate++;
   			} 
 		}

 		#echo array_sum($userratingavg);
		#echo "divided by";
		#echo $userrate;

		if ($userrate > 0) {
			$userrating = round(array_sum($userratingavg) / $userrate,2);
		}
		
		if ($adminrate > 0) {
			$adminrating = round(array_sum($adminratingavg) / $adminrate,2);
		}
		if ($debug) {
			echo "Domain: ".$domain."<br>";
		}

		#echo $userrating."\n";
		#echo $adminrating."\n";

		if (!$userrating) {
			$userrating=0;
		} elseif ($userrating > 10) {
			$userrating=10;
		}

		if (!$adminrating) {
			$adminrating=0;
		} elseif ($adminrating > 10) {
			$adminrating=10;
		}
		
		if ($admindb == -1) {
			$adminrating=-1;
		}
     	pg_free_result($ratings);
     	
		#echo $userrating."\n";
		#echo $adminrating."\n";
		$userrate=0;
		$adminrate=0;
		
		unset($userratingavg);
		unset($adminratingavg);
     	
     	//curl the header of pod with and without https
		unset($name);
		unset($total_users);
		unset($active_users_halfyear);
		unset($active_users_monthly);
		unset($local_posts);
		unset($registrations_open);
		
        $chss = curl_init();
        curl_setopt($chss, CURLOPT_URL, "https://".$domain."/statistics.json"); 
        curl_setopt($chss, CURLOPT_POST, 0);
        curl_setopt($chss, CURLOPT_HEADER, 1);
        curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chss, CURLOPT_NOBODY, 0);
        $outputssl = curl_exec($chss);      
        curl_close($chss);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".$domain."/statistics.json");
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        
		if ($debug) {
			print $output;
			print $outputssl;
		}
		
		if (stristr($outputssl, 'registrations_open')) {
			//parse header data
			$secure="true";
			if ($debug) {
				echo "Secure: ".$secure."<br>";
			}
			
			//$hidden="no";
			$score += 1;
			
			preg_match('/X-Git-Update: (.*?)\n/',$outputssl,$xgitdate);
			$gitdate = trim($xgitdate[1]);
			//$gitdate = strtotime($gitdate);
			
			preg_match('/X-Git-Revision: (.*?)\n/',$outputssl,$xgitrev);
			$gitrev = trim($xgitrev[1]);
			if ($debug) {
				echo "GitRevssl: ".$gitrev."<br>";
			}
			
			preg_match('/X-Diaspora-Version: (.*?)\n/',$outputssl,$xdver);
			$dverr = split("-",trim($xdver[1]));
			$dver = $dverr[0];
			
			if ($debug) {
				echo "Version code: ".$dver."<br>";
			}
			if (!$dver) {
				$score -= 2;
			}
			
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

			//get new json
			preg_match_all("/{(.*?)}/", $outputssl, $jsonssl_array);
			$jsonssl = json_decode($jsonssl_array[0][0]);
			if ($jsonssl->registrations_open === true) {
				$registrations_open=1;
			}
			$name = isset($jsonssl->name) ? $jsonssl->name : "null";
			$total_users = isset($jsonssl->total_users) ? $jsonssl->total_users : 0;
			$active_users_halfyear = isset($jsonssl->active_users_halfyear) ? $jsonssl->active_users_halfyear : 0;
			$active_users_monthly = isset($jsonssl->active_users_monthly) ? $jsonssl->active_users_monthly : 0;
			$local_posts = isset($jsonssl->local_posts) ? $jsonssl->local_posts : 0;
		} elseif (stristr($output, 'Set-Cookie: _diaspora_session=')) {
			"not";
			$secure="false";
			//$hidden="no";
			$score += 1;
			
			//parse header data
			preg_match('/X-Git-Update: (.*?)\n/',$output,$xgitdate);
			$gitdate = isset($xgitdate[1]) ? trim($xgitdate[1]) : null;
			
			preg_match('/X-Git-Revision: (.*?)\n/',$output,$xgitrev);
			$gitrev = isset($xgitrev[1]) ? trim($xgitrev[1]) : null;
			
			preg_match('/X-Diaspora-Version: (.*?)\n/',$output,$xdver);
			$dverr = split("-",trim($xdver[1]));
			$dver = $dverr[0];
			
			if ($debug) {
				echo "Version code: ".$dverr[1]."<br>";
			}
			if (!$dver) {
				$score -= 2;
			}
			
			preg_match('/X-Runtime: (.*?)\n/',$output,$xruntime);
			$runtime = isset($xruntime[1]) ? trim($xruntime[1]) : null;
			
			preg_match('/Server: (.*?)\n/',$output,$xserver);
			$server = isset($xserver[1]) ? trim($xserver[1]) : null;

			preg_match('/Content-Encoding: (.*?)\n/',$output,$xencoding);
			$encoding = isset($xencoding[1]) ? trim($xencoding[1]) : null;

			preg_match_all("/{(.*?)}/", $output, $jsonssl_array);
			$jsonssl = json_decode($jsonssl_array[0][0]);
			if ($jsonssl->registrations_open === true) {
				$registrations_open=1;
			}
			
			$name = isset($jsonssl->name) ? $jsonssl->name : "null";
			$total_users = isset($jsonssl->total_users) ? $jsonssl->total_users : 0;
			$active_users_halfyear = isset($jsonssl->active_users_halfyear) ? $jsonssl->active_users_halfyear : 0;
			$active_users_monthly = isset($jsonssl->active_users_monthly) ? $jsonssl->active_users_monthly : 0;
			$local_posts = isset($jsonssl->local_posts) ? $jsonssl->local_posts : 0;
		} else {
			$secure="false";
			$score -= 1;
			//$hidden="yes";
			//no diaspora cookie on either, lets set this one as hidden and notify someone its not really a pod
			//could also be a ssl pod with a bad cert, I think its ok to call that a dead pod now
		}
		
		$signup = $registrations_open;
		if ($debug) {
			echo "<br>Signup Open: ".$signup."<br>";
		}
		
		if ($score > 5) {
			$hidden = "no";
		} else {
			$hidden = "yes";
		}
		
		if ($debug) {
			echo "Hidden: ".$hidden."<br>";
		}
		
		// lets cap the scores or you can go too high or too low to never be effected by them
		if ($score > 20) {
			$score = 20;
		} elseif ($score < -20) {
			$score = -20;
		}
		
		$ip6 = escapeshellcmd('dig +nocmd '.$domain.' aaaa +noall +short');
		$ip = escapeshellcmd('dig +nocmd '.$domain.' a +noall +short');
		$ip6num = exec($ip6);
		$ipnum = exec($ip);
		$test = strpos($ip6num, ":");
		if ($test === false) {
			$ipv6="no";
		} else {
			$ipv6="yes";
		}

		//curl ip
		require_once "Net/GeoIP.php";
		$geoip = Net_GeoIP::getInstance("GeoLiteCity.dat");
		try {
    		$location = $geoip->lookupLocation($ipnum);
			if ($debug) {
				echo "GEOIP: ".$location."<br>";
			}
		} catch (Exception $e) {
    		// 	Handle exception
		}
		
		$ipdata = "Country: ".$location->countryName."\n";
		$whois = "Country: ".$location->countryName."\n Lat:".$location->latitude." Long:".$location->longitude;
		$country=$location->countryName;
		$city=  isset($location->city)?iconv("UTF-8", "UTF-8//IGNORE", $location->city):null;
		$state="";
		$months=0;
		$uptime=0;
		$lat=$location->latitude;
		$long=$location->longitude;
		$connection="";
		$pingdomdate = date('Y-m-d H:i:s');
		if (strpos($row[$i]['pingdomurl'], "pingdom.com")) {
			//curl the pingdom page 
        	$ping = curl_init();
        	$thismonth = "/".date("Y")."/".date("m");
        	curl_setopt($ping, CURLOPT_URL, $row[$i]['pingdomurl'].$thismonth);
			if ($debug) {
				echo $row[$i]['pingdomurl'].$thismonth;
			}
        	curl_setopt($ping, CURLOPT_POST, 0);
        	curl_setopt($ping, CURLOPT_HEADER, 1);
        	curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
        	curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
        	curl_setopt($ping, CURLOPT_NOBODY, 0);
        	curl_setopt($ping, CURLOPT_MAXCONNECTS, 5);
        	curl_setopt($ping, CURLOPT_FOLLOWLOCATION, true);
        	$pingdom = curl_exec($ping);
			$info = curl_getinfo($ping);
        	curl_close($ping);
			if ($debug) {
				echo "<br>Pingdom code: ".$info['http_code']."<br>";
			}
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
				$pingdomdate = date('Y-m-d H:i:s');
				
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
		} else {
			//do uptimerobot API instead
	        $ping = curl_init();
	        curl_setopt($ping, CURLOPT_URL, "http://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=".$row[$i]['pingdomurl']);
	        curl_setopt($ping, CURLOPT_POST, 0);
	        curl_setopt($ping, CURLOPT_HEADER, 0);
	        curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
	        curl_setopt($ping, CURLOPT_NOBODY, 0);
	        curl_setopt($ping, CURLOPT_MAXCONNECTS, 5);
	        curl_setopt($ping, CURLOPT_FOLLOWLOCATION, true);
	        $uptimerobot = curl_exec($ping);
	        curl_close($ping);
			$json_encap = "jsonUptimeRobotApi()";
        	$up2 = substr ($uptimerobot, strlen($json_encap) - 1, strlen ($uptimerobot) - strlen($json_encap)); 
			
        	$uptr = json_decode($up2);
			$responsetime = 'n/a';
			$uptime = $uptr->monitors->monitor{'0'}->alltimeuptimeratio;
			$diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($dateadded));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			switch ($uptr->monitors->monitor{'0'}->status) {
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
			$pingdomdate =  date('Y-m-d H:i:s');
		}
		
		//sql it
     	$timenow = date('Y-m-d H:i:s');
     	$sql = "UPDATE pods SET Hgitdate=$1, Hencoding=$2, secure=$3, hidden=$4, Hruntime=$5, Hgitref=$6, ip=$7, ipv6=$8, monthsmonitored=$9,";
     	$sql .= "uptimelast7=$10, status=$11, dateLaststats=$12, dateUpdated=$13, responsetimelast7=$14, score=$15, adminrating=$16, country=$17, city=$18,"; 
		$sql .= "state=$19, lat=$20, long=$21, postalcode='', connection=$22, whois=$23, userrating=$24, longversion=$25, shortversion=$26,";
		$sql .= "masterversion=$27, signup=$28, total_users=$29, active_users_halfyear=$30, active_users_monthly=$31, local_posts=$32, name=$33";
		$sql .= "WHERE"; 
		$sql .= "domain=$34";
     	
		$result = pg_query_params($dbh, $sql, array($gitdate, $encoding, $secure, $hidden, $runtime, $gitrev, $ipnum, $ipv6, $months, $uptime, $live, $pingdomdate, $timenow, $responsetime, $score, $adminrating, $country, $city, $state, $lat, $long, $dver, $whois, $userrating, $xdver[1], $dver, $masterversion, $signup, $total_users, $active_users_halfyear, $active_users_monthly, $local_posts, $name, $domain));
     	
     	if (!$result) {
        	die("Error in SQL query3: " . pg_last_error());
     	}
    
		if ($debug) {
			echo "<br>Score out of 20: ".$score."<br>";
		}
		if (!$debug) {
			echo "Success";
		}

		//end foreach
 	}
}   

pg_free_result($result);
pg_close($dbh);

?>
