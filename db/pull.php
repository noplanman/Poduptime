<?php
if ($_GET['debug'] == 1) {$debug = 1;}
//* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */
 include('config.php');
//get master code version
        $mv = curl_init();
        curl_setopt($mv, CURLOPT_URL, "https://raw.github.com/diaspora/diaspora/master/config/defaults.yml");
        curl_setopt($mv, CURLOPT_POST, 0);
        curl_setopt($mv, CURLOPT_HEADER, 0);
        curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($mv, CURLOPT_NOBODY, 0);
        $outputmv = curl_exec($mv);
        curl_close($mv);
	preg_match('/number: "(.*?)"/',$outputmv,$version);
 $masterversion = trim($version[1], '"');
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
 $dbh2 = pg_connect("dbname=$pgdb user=$pguser password=$pgpass"); 
    if (!$dbh) {
         die("Error in connection: " . pg_last_error());
     }
//foreach pod check it and update db
 $domain = ' ';    
 if ($_GET['domain']) {$domain=$_GET['domain'];$sql = "SELECT domain,pingdomurl,score,datecreated FROM pods WHERE domain = '$domain'";$sleep="0";} 
 else {$sql = "SELECT domain,pingdomurl,score,datecreated,adminrating FROM pods";$sleep="1";}

 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query1: " . pg_last_error());
 }
 while ($row = pg_fetch_all($result)) {
 $numrows = pg_num_rows($result);
 for ($i = 0; $i < $numrows; $i++) {
     $domain =  $row[$i]['domain'];
     $score = $row[$i]['score'];
     $dateadded = $row[$i]['datecreated'];
     $admindb = $row[$i]['adminrating'];
//get ratings
 $userrate=0;$adminrate=0;$userratingavg = array();$adminratingavg = array();$userrating = array();$adminrating = array();
 $sqlforr = "SELECT * FROM rating_comments WHERE domain = '$domain'";
 $ratings = pg_query($dbh, $sqlforr);
 if (!$ratings) {
     die("Error in SQL query2: " . pg_last_error());
 }
 $numratings = pg_num_rows($ratings);
 while($myrow = pg_fetch_assoc($ratings)) {
   if ($myrow['admin'] == 0) {
     $userratingavg[] = $myrow['rating'];$userrate++;
   } elseif ($myrow['admin'] == 1) {
     $adminratingavg[] = $myrow['rating'];$adminrate++;
   } 
 }
#echo array_sum($userratingavg);
#echo "divided by";
#echo $userrate;

if ($userrate > 0) {$userrating = round(array_sum($userratingavg) / $userrate,2);}
if ($adminrate > 0) {$adminrating = round(array_sum($adminratingavg) / $adminrate,2);}
if ($debug) {echo "Domain: ".$domain."<br>";}
#echo $userrating."\n";
#echo $adminrating."\n";

if (!$userrating) {$userrating=0;}
if ($userrating > 10) {$userrating=10;}
if (!$adminrating) {$adminrating=0;}
if ($adminrating > 10) {$adminrating=10;}
if ($admindb == -1) {$adminrating=-1;}
     pg_free_result($ratings);
#echo $userrating."\n";
#echo $adminrating."\n";
$userrate=0;$adminrate=0;
unset($userratingavg);
unset($adminratingavg);
     //curl the header of pod with and without https

        $chss = curl_init();
        curl_setopt($chss, CURLOPT_URL, "https://".$domain."/users/sign_in"); 
        curl_setopt($chss, CURLOPT_POST, 1);
        curl_setopt($chss, CURLOPT_HEADER, 1);
        curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chss, CURLOPT_NOBODY, 1);
        $outputssl = curl_exec($chss);      
        curl_close($chss);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".$domain."/users/sign_in");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $output = curl_exec($ch);
        curl_close($ch);

if (stristr($outputssl, 'Set-Cookie: _diaspora_session=')) {
//parse header data
$secure="true";
//$hidden="no";
$score = $score +1;
preg_match('/X-Git-Update: (.*?)\n/',$outputssl,$xgitdate);
$gitdate = trim($xgitdate[1]);
//$gitdate = strtotime($gitdate);
preg_match('/X-Git-Revision: (.*?)\n/',$outputssl,$xgitrev);
$gitrev = trim($xgitrev[1]);
preg_match('/X-Diaspora-Version: (.*?)\n/',$outputssl,$xdver);
$dverr = split("-",trim($xdver[1]));
$dver = $dverr[0];
if ($debug) {echo "Version code: ".$dverr[1]."<br>";}
if (!$dver) {$score = $score-2;}
preg_match('/X-Runtime: (.*?)\n/',$outputssl,$xruntime);
$runtime = trim($xruntime[1]);
preg_match('/Server: (.*?)\n/',$outputssl,$xserver);
$server = trim($xserver[1]);
preg_match('/Content-Encoding: (.*?)\n/',$outputssl,$xencoding);
if ($xencoding) {$encoding = trim($xencoding[1]);}

} elseif (stristr($output, 'Set-Cookie: _diaspora_session=')) {
"not";$secure="false";
//$hidden="no";
$score = $score +1;
//parse header data
preg_match('/X-Git-Update: (.*?)\n/',$output,$xgitdate);
$gitdate = trim($xgitdate[1]);
//$gitdate = strtotime($gitdate);
preg_match('/X-Git-Revision: (.*?)\n/',$output,$xgitrev);
$gitrev = trim($xgitrev[1]);
preg_match('/X-Diaspora-Version: (.*?)\n/',$output,$xdver);
$dverr = split("-",trim($xdver[1]));
$dver = $dverr[0];
if ($debug) {echo "Version code: ".$dverr[1]."<br>";}
if (!$dver) {$score = $score-2;}
preg_match('/X-Runtime: (.*?)\n/',$output,$xruntime);
$runtime = trim($xruntime[1]);
preg_match('/Server: (.*?)\n/',$output,$xserver);
$server = trim($xserver[1]);
preg_match('/Content-Encoding: (.*?)\n/',$output,$xencoding);
$encoding = trim($xencoding[1]);
} else {
$secure="false";
$score = $score - 1;
//$hidden="yes";
//no diaspora cookie on either, lets set this one as hidden and notify someone its not really a pod
//could also be a ssl pod with a bad cert, I think its ok to call that a dead pod now
}
if ($debug) {echo "SSL: ".$secure."<br>";}
if (!$gitdate) {
//if a pod is not displaying the git header data its really really really old lets lower your score
//$hidden="yes";
if ($debug) {echo "Valid Headers: ".$gitdate."<br>";}
$score = $score - 2;
}
if ($score > 5) {
$hidden = "no";
} else {
$hidden = "yes";
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
if ($debug) {echo "GEOIP: ".$location."<br>";}
} catch (Exception $e) {
    // Handle exception
}
$ipdata = "Country: ".$location->countryName."\n";
$whois = "Country: ".$location->countryName."\n Lat:".$location->latitude." Long:".$location->longitude;
$country=$location->countryName;
$city=  iconv("UTF-8", "UTF-8//IGNORE", $location->city);
$state="";
$lat=$location->latitude;
$long=$location->longitude;
$connection="";
if (strpos($row[$i]['pingdomurl'], "pingdom.com")) {
//curl the pingdom page 
        $ping = curl_init();
        $thismonth = "/".date("Y")."/".date("m");
        curl_setopt($ping, CURLOPT_URL, $row[$i]['pingdomurl'].$thismonth);
        curl_setopt($ping, CURLOPT_POST, 0);
        curl_setopt($ping, CURLOPT_HEADER, 1);
        curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ping, CURLOPT_NOBODY, 0);
        curl_setopt($ping, CURLOPT_MAXCONNECTS, 5);
        curl_setopt($ping, CURLOPT_FOLLOWLOCATION, true);
        $pingdom = curl_exec($ping);
        curl_close($ping);
if ($debug) {echo "Pingdom: ".$pingdom."<br>";}
//response time
preg_match_all('/<h3>Avg. resp. time this month<\/h3>
        <p class="large">(.*?)</',$pingdom,$matcheach);
$responsetime = $matcheach[1][0];

//months monitored
preg_match_all('/"historySelect">\s*(.*?)\s*<\/select/is',$pingdom,$matchhistory);
$implodemonths = implode(" ", $matchhistory[1]);
preg_match_all('/<option(.*?)/s',$implodemonths,$matchdates);
$months = count($matchdates[0]);
//uptime %
preg_match_all('/<h3>Uptime this month<\/h3>\s*<p class="large">(.*?)</',$pingdom,$matchper);
$uptime = preg_replace("/,/", ".", $matchper[1][0]);
//var_dump($matchper);
//last check
preg_match_all('/<h3>Last checked<\/h3>
<p>(.*?)</',$pingdom,$matchdate);

$pingdom_timestamp = $matchdate[1][0];
$Date_parts = preg_split("/[\s-]+/", $pingdom_timestamp);
if (strlen($Date_parts[0]) == "2") {
$pingdomdate = date('Y-m-d H:i:s');
}
else {
$pingdomdate = date('Y-m-d H:i:s');
}
//status
if (strpos($pingdom,"class=\"up\"")) { $live="up"; }
elseif (strpos($pingdom,"class=\"down\"")) { $live="down"; }
elseif (strpos($pingdom,"class=\"paused\"")) { $live="paused";}
else {$live="error";$score=$score-2;}
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
$uptime = $uptr->monitors->monitor{'0'}->alltimeuptimeratio."%";
$diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($dateadded));
$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
if ($uptr->monitors->monitor{'0'}->status == 2) {$live = "Up";}
if ($uptr->monitors->monitor{'0'}->status == 1) {$live = "Paused";}
if ($uptr->monitors->monitor{'0'}->status == 8) {$live = "Seems Down";}
if ($uptr->monitors->monitor{'0'}->status == 9) {$live = "Down";}

$pingdomdate =  date('Y-m-d H:i:s');
}
//sql it
     $timenow = date('Y-m-d H:i:s');
     $sql = "UPDATE pods SET Hgitdate='$gitdate', Hencoding='$encoding', secure='$secure', hidden='$hidden', Hruntime='$runtime', Hgitref='$gitrev', ip='$ipnum', ipv6='$ipv6', monthsmonitored='$months', 
uptimelast7='$uptime', status='$live', dateLaststats='$pingdomdate', dateUpdated='$timenow', responsetimelast7='$responsetime', score='$score', adminrating='$adminrating', country='$country', city='$city', 
state='$state', lat='$lat', long='$long', postalcode='$postalcode', connection='$dver', whois='$whois', userrating='$userrating', longversion='$xdver[1]', shortversion='$dver', 
masterversion='$masterversion' 
WHERE 
domain='$domain'";
     if ($debug) {echo "SQL: ".$sql."<br>";}
     $result = pg_query($dbh, $sql);
     if (!$result) {
         die("Error in SQL query3: " . pg_last_error());
     }
    
if ($debug) {echo "Score out of 20: ".$score."<br>";}
if (!$debug) {echo "Success";}

//end foreach
sleep($sleep);
 }
 }   
     pg_free_result($result);
    
     pg_close($dbh);

?>
