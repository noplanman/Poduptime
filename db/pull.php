<?php
//$debug = isset($_GET['debug']);
$debug = true;
//$debug = isset($argv[1])?1:0;
//* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

// Other parameters.
$_domain = $_GET['domain'] ?? '';

require_once __DIR__ . '/../config.php';

//get master code version for diaspora pods
$mv = curl_init();
curl_setopt($mv, CURLOPT_URL, 'https://raw.githubusercontent.com/diaspora/diaspora/master/config/defaults.yml');
curl_setopt($mv, CURLOPT_POST, 0);
curl_setopt($mv, CURLOPT_HEADER, 0);
curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($mv, CURLOPT_NOBODY, 0);
$outputmv = curl_exec($mv);
curl_close($mv);
preg_match('/number: "(.*?)"/', $outputmv, $version);
$dmasterversion = trim($version[1], '"');
if ($debug) {
  echo 'Diaspora Masterversion: ' . $dmasterversion . '<br>';
}

//get master code version for freindica pods
$mv = curl_init();
curl_setopt($mv, CURLOPT_URL, 'https://raw.githubusercontent.com/friendica/friendica/master/boot.php');
curl_setopt($mv, CURLOPT_POST, 0);
curl_setopt($mv, CURLOPT_HEADER, 0);
curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($mv, CURLOPT_NOBODY, 0);
$outputmv = curl_exec($mv);
curl_close($mv);
preg_match('/FRIENDICA_VERSION\',      \'(.*?)\'/', $outputmv, $version);
$fmasterversion = trim($version[1], '"');
if ($debug) {
  echo 'Frendica Masterversion: ' . $fmasterversion . '<br>';
}

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

//foreach pod check it and update db
if ($_domain) {
  $sql    = 'SELECT domain,pingdomurl,score,datecreated,weight FROM pods WHERE domain = $1';
  $sleep  = '0';
  $result = pg_query_params($dbh, $sql, [$_domain]);
} elseif (PHP_SAPI === 'cli') {
  $sql    = 'SELECT domain,pingdomurl,score,datecreated,adminrating,weight FROM pods';
  $sleep  = '1';
  $result = pg_query($dbh, $sql);
} else {
  die('No valid input');
}
$result || die('Error in SQL query1: ' . pg_last_error());

while ($row = pg_fetch_all($result)) {
  $numrows = pg_num_rows($result);
  for ($i = 0; $i < $numrows; $i++) {
    $domain    = $row[$i]['domain'];
    $score     = (int) $row[$i]['score'];
    $dateadded = $row[$i]['datecreated'];
    $admindb   = (int) $row[$i]['adminrating'];
    $weight    = $row[$i]['weight'];
    //get ratings
    $userrate       = 0;
    $adminrate      = 0;
    $userratingavg  = [];
    $adminratingavg = [];
    $userrating     = [];
    $adminrating    = [];
    $sqlforr        = 'SELECT * FROM rating_comments WHERE domain = $1';
    $ratings        = pg_query_params($dbh, $sqlforr, [$domain]);
    $ratings || die('Error in SQL query2: ' . pg_last_error());

    $numratings = pg_num_rows($ratings);
    while ($myrow = pg_fetch_assoc($ratings)) {
      if ($myrow['admin'] == 0) {
        $userratingavg[] = $myrow['rating'];
        $userrate ++;
      } elseif ($myrow['admin'] == 1) {
        $adminratingavg[] = $myrow['rating'];
        $adminrate ++;
      }
    }

    if ($userrate > 0) {
      $userrating = round(array_sum($userratingavg) / $userrate, 2);
    }
    if ($adminrate > 0) {
      $adminrating = round(array_sum($adminratingavg) / $adminrate, 2);
    }
    if ($debug) {
      echo 'Domain: ' . $domain . '<br>';
    }
    if (!$userrating) {
      $userrating = 0;
    }
    if ($userrating > 10) {
      $userrating = 10;
    }
    if (!$adminrating) {
      $adminrating = 0;
    }
    if ($adminrating > 10) {
      $adminrating = 10;
    }
    if ($admindb == - 1) {
      $adminrating = - 1;
    }
    pg_free_result($ratings);
    $userrate  = 0;
    $adminrate = 0;
    unset($userratingavg);
    unset($adminratingavg);
    unset($name);
    unset($total_users);
    unset($active_users_halfyear);
    unset($active_users_monthly);
    unset($local_posts);
    unset($registrations_open);
    unset($comment_counts);
    unset($service_facebook);
    unset($service_twitter);
    unset($service_tumblr);
    unset($service_wordpess);
    unset($service_xmpp);
    unset($dver);
    unset($dverr);
    unset($xdver);
    unset($softwarename);
    unset($outputsslerror);

    $chss = curl_init();
    curl_setopt($chss, CURLOPT_URL, 'https://' . $domain . '/nodeinfo/1.0');
    curl_setopt($chss, CURLOPT_POST, 0);
    curl_setopt($chss, CURLOPT_HEADER, 0);
    curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 9);
    curl_setopt($chss, CURLOPT_TIMEOUT, 9);
    curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($chss, CURLOPT_NOBODY, 0);
    $outputssl      = curl_exec($chss);
    $outputsslerror = curl_error($chss);
    curl_close($chss);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $domain . '/nodeinfo/1.0');
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
    curl_setopt($ch, CURLOPT_TIMEOUT, 9);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    if ($debug) {
      echo 'not-e';
      print $output;
    }
    if ($debug) {
      echo 'e';
      var_dump($outputssl);
    }
    if (!$output && !$outpulssl && !$domain) {
      continue;
      echo 'no connection to pod';
    }
    if ($outputssl) {
      $secure        = 'true';
      $outputresults = $outputssl;
    } elseif ($output) {
      $secure        = 'false';
      $outputresults = $output;
    }
    if (stristr($outputresults, 'openRegistrations')) {
      $score = $score + 1;
      if ($debug) {
        echo 'Secure: ' . $secure . '<br>';
      }
      //get new json from nodeinfo
      $jsonssl = json_decode($outputresults);
      var_dump($jsonssl);
      if ($jsonssl->openRegistrations === true) {
        $registrations_open = 1;
      }
      $xdver = $jsonssl->software->version ?? 0;
      $dverr = explode('-', trim($xdver));
      $dver  = $dverr[0];
      if ($debug) {
        echo ' <br> Version code: ' . $dver . '<br>';
      }
      if (!$dver) {
        $score = $score - 2;
      }
      $softwarename          = $jsonssl->software->name ?? 'null';
      $name                  = $jsonssl->metadata->nodeName ?? 'null';
      $total_users           = $jsonssl->usage->users->total ?? 0;
      $active_users_halfyear = $jsonssl->usage->users->activeHalfyear ?? 0;
      $active_users_monthly  = $jsonssl->usage->users->activeMonth ?? 0;
      $local_posts           = $jsonssl->usage->localPosts ?? 0;
      $comment_counts        = $jsonssl->usage->localComments ?? 0;
      $service_facebook      = in_array('facebook', $jsonssl->services->outbound, true) ? 'true' : 'false';
      $service_twitter       = in_array('twitter', $jsonssl->services->outbound, true) ? 'true' : 'false';
      $service_tumblr        = in_array('tumblr', $jsonssl->services->outbound, true) ? 'true' : 'false';
      $service_wordpress     = in_array('wordpress', $jsonssl->services->outbound, true) ? 'true' : 'false';
      $service_xmpp          = $jsonssl->metadata->xmppChat === true ? 'true' : 'false';
    } else {
      $secure = 'false';
      $score  = $score - 1;
      $dver   = '.connect error';
      $dverr  = 0;
      //no diaspora cookie on either, lets set this one as hidden and notify someone its not really a pod
      //could also be a ssl pod with a bad cert, I think its ok to call that a dead pod now
    }
    $signup = $registrations_open;
    if ($debug) {
      echo '<br>Signup Open: ' . $signup . '<br>';
    }
    $ip6    = escapeshellcmd('dig +nocmd ' . $domain . ' aaaa +noall +short');
    $ip     = escapeshellcmd('dig +nocmd ' . $domain . ' a +noall +short');
    $ip6num = exec($ip6);
    $ipnum  = exec($ip);
    $test   = strpos($ip6num, ':');
    if ($test === false) {
      $ipv6 = 'no';
    } else {
      $ipv6 = 'yes';
    }
    if ($debug) {
      echo 'IP: ' . $ipnum . '<br>';
    }
    $location = geoip_record_by_name($ipnum);
    if ($debug) {
      echo ' Location: ';
      var_dump($location);
      echo '<br>';
    }
    if ($location) {
      $ipdata  = 'Country: ' . $location['country_name'] . "\n";
      $whois   = 'Country: ' . $location['country_name'] . "\n Lat:" . $location['latitude'] . ' Long:' . $location['longitude'];
      $country = $location['country_code'];
      $city    = isset($location->city) ? iconv('UTF-8', 'UTF-8//IGNORE', $location->city) : null;
      $state   = '';
      $months  = 0;
      $uptime  = 0;
      $lat     = $location['latitude'];
      $long    = $location['longitude'];
      //if lat and long are just a generic country with no detail lets make some tail up or openmap just stacks them all on top another
      if (strlen($lat) < 4) {
        $lat = $lat + (rand(1, 15) / 10);
      }
      if (strlen($long) < 4) {
        $long = $long + (rand(1, 15) / 10);
      }
    }
    echo '<br>';
    $connection  = '';
    $pingdomdate = date('Y-m-d H:i:s');
    if (strpos($row[$i]['pingdomurl'], 'pingdom.com')) {
      //curl the pingdom page 
      $ping      = curl_init();
      $thismonth = '/' . date('Y') . '/' . date('m');
      curl_setopt($ping, CURLOPT_URL, $row[$i]['pingdomurl'] . $thismonth);
      if ($debug) {
        echo $row[$i]['pingdomurl'] . $thismonth;
      }
      curl_setopt($ping, CURLOPT_POST, 0);
      curl_setopt($ping, CURLOPT_HEADER, 1);
      curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
      curl_setopt($ping, CURLOPT_NOBODY, 0);
      curl_setopt($ping, CURLOPT_MAXCONNECTS, 5);
      curl_setopt($ping, CURLOPT_FOLLOWLOCATION, true);
      $pingdom = curl_exec($ping);
      $info    = curl_getinfo($ping);
      curl_close($ping);
      if ($debug) {
        echo '<br>Pingdom code: ' . $info['http_code'] . '<br>';
      }
      if ($info['http_code'] == 200) {
        //response time
        preg_match_all('/<h3>Avg. resp. time this month<\/h3>
          <p class="large">(.*?)</', $pingdom, $matcheach);
        $responsetime = $matcheach[1][0];
        //months monitored
        preg_match_all('/"historySelect">\s*(.*?)\s*<\/select/is', $pingdom, $matchhistory);
        $implodemonths = implode(' ', $matchhistory[1]);
        preg_match_all('/<option(.*?)/s', $implodemonths, $matchdates);
        $months = isset($matchdates[0]) ? count($matchdates[0]) : 0;
        //uptime %
        preg_match_all('/<h3>Uptime this month<\/h3>\s*<p class="large">(.*?)%</', $pingdom, $matchper);
        $uptime      = isset($matchper[1][0]) ? preg_replace('/,/', '.', $matchper[1][0]) : 0;
        $pingdomdate = date('Y-m-d H:i:s');
        if (strpos($pingdom, "class=\"up\"")) {
          $live = 'up';
        } elseif (strpos($pingdom, "class=\"down\"")) {
          $live = 'down';
        } elseif (strpos($pingdom, "class=\"paused\"")) {
          $live = 'paused';
        } else {
          $live  = 'error';
          $score = $score - 2;
        }
      } else {
        //pingdom url is <> 200 so stats are gone, lower score
        $score = $score - 2;
      }
    } else {
      //do uptimerobot API instead
      $ping = curl_init();
      curl_setopt($ping, CURLOPT_URL, 'https://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&responseTimes=1&responseTimesAverage=86400&apiKey=' . $row[$i]['pingdomurl']);
      curl_setopt($ping, CURLOPT_POST, 0);
      curl_setopt($ping, CURLOPT_HEADER, 0);
      curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
      curl_setopt($ping, CURLOPT_NOBODY, 0);
      curl_setopt($ping, CURLOPT_MAXCONNECTS, 5);
      curl_setopt($ping, CURLOPT_FOLLOWLOCATION, true);
      $uptimerobot = curl_exec($ping);
      curl_close($ping);
      $json_encap = 'jsonUptimeRobotApi()';
      $up2        = substr($uptimerobot, strlen($json_encap) - 1, strlen($uptimerobot) - strlen($json_encap));
      $uptr       = json_decode($up2);
      if ($debug) {
        print_r($uptr);
        echo '<br>';
      }
      if (!$uptr) {
        $score = $score - 2;
      }
      $responsetime    = $uptr->monitors->monitor{'0'}->responsetime{'0'}->value;
      $uptimerobotstat = $uptr->stat;
      $uptime          = $uptr->monitors->monitor{'0'}->alltimeuptimeratio;
      $diff            = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($dateadded));
      $months          = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
      if ($uptr->monitors->monitor{'0'}->status == 2) {
        $live = 'Up';
      }
      if ($uptr->monitors->monitor{'0'}->status == 0) {
        $live = 'Paused';
      }
      if ($uptr->monitors->monitor{'0'}->status == 1) {
        $live = 'Not Checked Yet';
      }
      if ($uptr->monitors->monitor{'0'}->status == 8) {
        $live = 'Seems Down';
      }
      if ($uptr->monitors->monitor{'0'}->status == 9) {
        $live = 'Down';
      }
      $pingdomdate = date('Y-m-d H:i:s');
      if ($uptimerobotstat == 'fail' || $live <> 'Up') {
        $score = $score - 2;
      }
    }
    if ($softwarename == 'diaspora') {
      $masterversion = $dmasterversion;
    } elseif ($softwarename == 'friendica') {
      $masterversion = $fmasterversion;
    }
    if ($score > 70) {
      $hidden = 'no';
    } else {
      $hidden = 'yes';
    }
    if ($debug) {
      echo 'Hidden: ' . $hidden . '<br>';
    }
    // lets cap the scores or you can go too high or too low to never be effected by them
    if ($score > 100) {
      $score = 100;
    } elseif ($score < 0) {
      $score = 0;
    }
    $weightedscore = ($uptime + $score + ($active_users_monthly / 19999) - ((10 - $weight) * .12));
    //sql it

    $timenow = date('Y-m-d H:i:s');
    $sql     = 'UPDATE pods SET Hgitdate = $1, Hencoding = $2, secure = $3, hidden = $4, Hruntime = $5, Hgitref = $6, ip = $7, ipv6 = $8, monthsmonitored = $9,
  uptimelast7 = $10, status = $11, dateLaststats = $12, dateUpdated = $13, responsetimelast7 = $14, score = $15, adminrating = $16, country = $17, city = $18,
  state = $19, lat = $20, long = $21, postalcode=\'\', connection = $22, whois = $23, userrating = $24, longversion = $25, shortversion = $26,
  masterversion = $27, signup = $28, total_users = $29, active_users_halfyear = $30, active_users_monthly = $31, local_posts = $32, name = $33,
  comment_counts = $35, service_facebook = $36, service_tumblr = $37, service_twitter = $38, service_wordpress = $39, weightedscore = $40, xmpp = $41, softwarename = $42, sslvalid = $43
  WHERE domain = $34';
    $result  = pg_query_params($dbh, $sql, [$gitdate, $encoding, $secure, $hidden, $runtime, $gitrev, $ipnum, $ipv6, $months, $uptime, $live, $pingdomdate, $timenow, $responsetime, $score, $adminrating, $country, $city, $state, $lat, $long, $dver, $whois, $userrating, $xdver, $dver, $masterversion, $signup, $total_users, $active_users_halfyear, $active_users_monthly, $local_posts, $name, $domain, $comment_counts, $service_facebook, $service_tumblr, $service_twitter, $service_wordpress, $weightedscore, $service_xmpp, $softwarename, $outputsslerror]);
    $result || die('Error in SQL query3: ' . pg_last_error());

    if ($debug) {
      echo '<br>Score out of 100: ' . $score . '<br>';
    }
    if (!$debug) {
      echo 'Success';
    }
    //end foreach
  }
}
pg_free_result($result);
pg_close($dbh);
