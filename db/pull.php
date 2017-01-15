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
  $sql    = 'SELECT domain,stats_apikey,score,date_created,weight FROM pods WHERE domain = $1';
  //$sleep  = '0';
  $result = pg_query_params($dbh, $sql, [$_domain]);
} elseif (PHP_SAPI === 'cli') {
  $sql    = 'SELECT domain,stats_apikey,score,date_created,adminrating,weight FROM pods';
  //$sleep  = '1';
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
    $dateadded = $row[$i]['date_created'];
    $admindb   = (int) $row[$i]['adminrating'];
    $weight    = $row[$i]['weight'];
    $sqlforr       = 'SELECT admin,rating FROM rating_comments WHERE domain = $1';
    $ratings       = pg_query_params($dbh, $sqlforr, [$domain]);
    $ratings || die('Error in SQL query2: ' . pg_last_error());

    if ($debug) {
      echo 'Domain: ' . $domain . '<br>';
    }

    $user_ratings  = [];
    $admin_ratings = [];
    while ($rating = pg_fetch_assoc($ratings)) {
      if ($rating['admin'] == 0) {
        $user_ratings[] = $rating['rating'];
      } elseif ($rating['admin'] == 1) {
        $admin_ratings[] = $rating['rating'];
      }
    }
    $user_rating  = empty($user_ratings) ? 0 : max(10, round(array_sum($user_ratings) / count($user_ratings), 2));
    $admin_rating = empty($admin_ratings) ? 0 : max(10, round(array_sum($admin_ratings) / count($admin_ratings), 2));

    if ($admindb == - 1) {
      $admin_rating = - 1;
    }
    pg_free_result($ratings);
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
    unset($shortversion);
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

    if ($debug) {
      var_dump($outputssl);
    }
    if (!$outpulssl && !$domain) {
      continue;
      echo 'no connection to pod';
      
      $sql    = 'INSERT INTO checks (domain, online, error) VALUES ($1, $2, $3)';
      $result = pg_query_params($dbh, $sql, [$domain, false, $outputsslerror]);
      $result || die('Error in SQL query: ' . pg_last_error());
      
    }
    if ($outputssl) {
      $secure        = 'true';
      $outputresults = $outputssl;
      
      $sql    = 'INSERT INTO checks (domain, online) VALUES ($1, $2)';
      $result = pg_query_params($dbh, $sql, [$domain, true]);
      $result || die('Error in SQL query: ' . pg_last_error());
  
    } else {
      $secure        = 'false';
      $outputresults = $output;
    }
    if (stristr($outputresults, 'openRegistrations')) {
      $score += 1;
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
      $shortversion  = $dverr[0];
      if ($debug) {
        echo ' <br> Version code: ' . $shortversion . '<br>';
      }
      $shortversion || $score -= 2;

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
      $score -= 1;
      $dver   = '.connect error';
      $shortversion  = 0;
      //could also be a ssl pod with a bad cert, I think its ok to call that a dead pod now
    }
    $signup = $registrations_open;
    if ($debug) {
      echo '<br>Signup Open: ' . $signup . '<br>';
    }
    $ip6    = exec(escapeshellcmd('dig +nocmd ' . $domain . ' aaaa +noall +short'));
    $ip     = exec(escapeshellcmd('dig +nocmd ' . $domain . ' a +noall +short'));
    $test   = strpos($ip6, ':');
    $ipv6   = $test === false ? 'no' : 'yes';

    if ($debug) {
      echo 'IP: ' . $ip . '<br>';
    }
    $location = geoip_record_by_name($ip);
    if ($debug) {
      echo ' Location: ';
      var_dump($location);
      echo '<br>';
    }
    if ($location) {
      $country = !empty($location['country_code']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['country_code']) : null;
      $city    = !empty($location['city']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['city']) : null;
      $state   = !empty($location['region']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['region']) : null;
      $lat     = !empty($location['latitude']) ? $location['latitude'] : null;
      $long    = !empty($location['longitude']) ? $location['longitude'] : null;

      // If lat and long are just a generic country with no detail lets make some tail up
      // else openmap just stacks them all on top of each other.
      strlen($lat) < 4 && $lat += (random_int(1, 15) / 10);
      strlen($long) < 4 && $long += (random_int(1, 15) / 10);
    }
    echo '<br>';
    $statslastdate = date('Y-m-d H:i:s');
      $ping = curl_init();
      curl_setopt($ping, CURLOPT_URL, 'https://api.uptimerobot.com/getMonitors?format=json&noJsonCallback=1&customUptimeRatio=7-30-60-90&responseTimes=1&responseTimesAverage=86400&apiKey=' . $row[$i]['stats_apikey']);
      curl_setopt($ping, CURLOPT_POST, 0);
      curl_setopt($ping, CURLOPT_HEADER, 0);
      curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
      curl_setopt($ping, CURLOPT_NOBODY, 0);
      curl_setopt($ping, CURLOPT_MAXCONNECTS, 5);
      curl_setopt($ping, CURLOPT_FOLLOWLOCATION, true);
      $uptr = json_decode(curl_exec($ping));
      curl_close($ping);
      if ($debug) {
        print_r($uptr);
        echo '<br>';
      }
      $uptr || $score -= 2;

      $responsetime    = $uptr->monitors->monitor{'0'}->responsetime{'0'}->value;
      $uptimerobotstat = $uptr->stat;
      $uptime          = $uptr->monitors->monitor{'0'}->alltimeuptimeratio;
      $uptime_custom   = $uptr->monitors->monitor{'0'}->customuptimeratio;
      $diff            = (new DateTime())->diff(new DateTime($dateadded));
      $months          = $diff->m + ($diff->y * 12);
      if ($uptr->monitors->monitor{'0'}->status == 2) {
        $status = 'Up';
      }
      if ($uptr->monitors->monitor{'0'}->status == 0) {
        $status = 'Paused';
      }
      if ($uptr->monitors->monitor{'0'}->status == 1) {
        $status = 'Not Checked Yet';
      }
      if ($uptr->monitors->monitor{'0'}->status == 8) {
        $status = 'Seems Down';
      }
      if ($uptr->monitors->monitor{'0'}->status == 9) {
        $status = 'Down';
      }
      if ($uptr) {
        $statslastdate = date('Y-m-d H:i:s');
      }
      ($uptimerobotstat !== 'fail' && $status === 'Up') || $score -= 2;

    if ($softwarename === 'diaspora') {
      $masterversion = $dmasterversion;
    } elseif ($softwarename === 'friendica') {
      $masterversion = $fmasterversion;
    }
    $hidden = $score > 70 ? 'no' : 'yes';
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
    $sql     = 'UPDATE pods SET secure = $2, hidden = $3, ip = $4, ipv6 = $5, monthsmonitored = $6, uptime_alltime = $7, status = $8, date_laststats = $9, date_updated = $10, responsetime = $11, score = $12, adminrating = $13, country = $14, city = $15, state = $16, lat = $17, long = $18, userrating = $19, shortversion = $20, masterversion = $21, signup = $22, total_users = $23, active_users_halfyear = $24, active_users_monthly = $25, local_posts = $26, name = $27, comment_counts = $28, service_facebook = $29, service_tumblr = $30, service_twitter = $31, service_wordpress = $32, weightedscore = $33, service_xmpp = $34, softwarename = $35, sslvalid = $36, uptime_custom = $37
  WHERE domain = $1';
    $result  = pg_query_params($dbh, $sql, [$domain, $secure, $hidden, $ip, $ipv6, $months, $uptime, $status, $statslastdate, $timenow, $responsetime, $score, $admin_rating, $country, $city, $state, $lat, $long, $user_rating, $shortversion, $masterversion, $signup, $total_users, $active_users_halfyear, $active_users_monthly, $local_posts, $name, $comment_counts, $service_facebook, $service_tumblr, $service_twitter, $service_wordpress, $weightedscore, $service_xmpp, $softwarename, $outputsslerror, $uptime_custom]);
    $result || die('Error in SQL query3: ' . pg_last_error());

    if ($debug) {
      echo '<br>Score out of 100: ' . $score . '<br>';
    } else {
      echo 'Success';
    }
    //end foreach
  }
}
pg_free_result($result);
pg_close($dbh);
