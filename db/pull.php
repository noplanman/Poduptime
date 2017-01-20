<?php
//* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

$debug   = isset($_GET['debug']) || (isset($argv) && in_array('debug', $argv, true));
$newline = PHP_SAPI === 'cli' ? "\n" : '<br>';

// Other parameters.
$_domain = $_GET['domain'] ?? '';

require_once __DIR__ . '/../config.php';

//get master code version for diaspora pods
$mv = curl_init();
curl_setopt($mv, CURLOPT_URL, 'https://raw.githubusercontent.com/diaspora/diaspora/master/config/defaults.yml');
curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
$outputmv = curl_exec($mv);
curl_close($mv);
$dmasterversion = preg_match('/number:.*"(.*)"/', $outputmv, $version) ? $version[1] : '';
_debug('Diaspora Masterversion', $dmasterversion);

//get master code version for friendica pods
$mv = curl_init();
curl_setopt($mv, CURLOPT_URL, 'https://raw.githubusercontent.com/friendica/friendica/master/boot.php');
curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
$outputmv = curl_exec($mv);
curl_close($mv);
$fmasterversion = preg_match('/define.*\'FRIENDICA_VERSION\'.*\'(.*)\'/', $outputmv, $version) ? $version[1] : '';
_debug('Friendica Masterversion: ' . $fmasterversion);

//get master code version for hubzilla pods
$mv = curl_init();
curl_setopt($mv, CURLOPT_URL, 'https://raw.githubusercontent.com/redmatrix/hubzilla/master/boot.php');
curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
$outputmv = curl_exec($mv);
curl_close($mv);
$hmasterversion = preg_match('/define.*\'STD_VERSION\'.*\'(.*)\'/', $outputmv, $version) ? $version[1] : '' ;
_debug('Hubzilla Masterversion: ' . $hmasterversion);

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

//foreach pod check it and update db
if ($_domain) {
  $sql = 'SELECT domain,stats_apikey,score,date_created,adminrating,weight FROM pods WHERE domain = $1';
  $result = pg_query_params($dbh, $sql, [$_domain]);
} elseif (PHP_SAPI === 'cli') {
  $sql = 'SELECT domain,stats_apikey,score,date_created,adminrating,weight FROM pods';
  $result = pg_query($dbh, $sql);
} else {
  die('No valid input');
}
$result || die('Error in SQL query1: ' . pg_last_error());

while ($row = pg_fetch_assoc($result)) {
  $domain    = $row['domain'];
  $score     = (int) $row['score'];
  $dateadded = $row['date_created'];
  $admindb   = (int) $row['adminrating'];
  $weight    = $row['weight'];
  $sqlforr   = 'SELECT admin,rating FROM rating_comments WHERE domain = $1';
  $ratings   = pg_query_params($dbh, $sqlforr, [$domain]);
  $ratings || die('Error in SQL query2: ' . pg_last_error());

  _debug('Domain', $domain);

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

  if ($admindb == -1) {
    $admin_rating = -1;
  }

  $chss = curl_init();
  curl_setopt($chss, CURLOPT_URL, 'https://' . $domain . '/nodeinfo/1.0');
  curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 9);
  curl_setopt($chss, CURLOPT_TIMEOUT, 9);
  curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($chss, CURLOPT_CERTINFO, 1);
  $outputssl      = curl_exec($chss);
  $outputsslerror = curl_error($chss);
  $info           = curl_getinfo($chss, CURLINFO_CERTINFO);
  $ttl            = curl_getinfo($chss, CURLINFO_CONNECT_TIME);
  $sslexpire      = $info[0]['Expire date'] ?? null;
  curl_close($chss);

  _debug('Nodeinfo output', $outputssl, true);
  _debug('Nodeinfo output error', $outputsslerror, true);
  _debug('Cert expire date', $sslexpire);
  _debug('TTL', $ttl);
  
  //get new json from nodeinfo
  $jsonssl = json_decode($outputssl);

  if (!$jsonssl) {    
    _debug('Connection', 'Can not connect to pod');

    $sql_errors    = 'INSERT INTO checks (domain, online, error, ttl) VALUES ($1, $2, $3, $4)';
    $result_errors = pg_query_params($dbh, $sql_errors, [$domain, 0, $outputsslerror, $ttl]);
    $result_errors || die('Error in SQL query: ' . pg_last_error());
  }

  if ($jsonssl !== null) {
    $sql_checks    = 'INSERT INTO checks (domain, online, ttl) VALUES ($1, $2, $3)';
    $result_checks = pg_query_params($dbh, $sql_checks, [$domain, 1, $ttl]);
    $result_checks || die('Error in SQL query: ' . pg_last_error());
    
    (!$jsonssl->software->version) || $score += 1;
    $xdver        = $jsonssl->software->version ?? 0;
    $dverr        = explode('-', trim($xdver));
    $shortversion = $dverr[0];
    _debug('Version code', $shortversion);
    $signup                = ($jsonssl->openRegistrations === true);
    $softwarename          = $jsonssl->software->name ?? 'null';
    $name                  = $jsonssl->metadata->nodeName ?? 'null';
    $total_users           = $jsonssl->usage->users->total ?? 0;
    $active_users_halfyear = $jsonssl->usage->users->activeHalfyear ?? 0;
    $active_users_monthly  = $jsonssl->usage->users->activeMonth ?? 0;
    $local_posts           = $jsonssl->usage->localPosts ?? 0;
    $comment_counts        = $jsonssl->usage->localComments ?? 0;
    $service_facebook      = in_array('facebook', $jsonssl->services->outbound, true);
    $service_twitter       = in_array('twitter', $jsonssl->services->outbound, true);
    $service_tumblr        = in_array('tumblr', $jsonssl->services->outbound, true);
    $service_wordpress     = in_array('wordpress', $jsonssl->services->outbound, true);
    $service_xmpp          = $jsonssl->metadata->xmppChat === true ?? false;
  } else {
    $score -= 1;
    $dver         = '.connect error';
    $shortversion = 0;
  }

  _debug('Signup Open', $signup);
  $ip6 = exec(escapeshellcmd('dig @74.82.42.42 +nocmd ' . $domain . ' aaaa +noall +short'));
  $iplookup = [];
  exec(escapeshellcmd('delv @74.82.42.42 ' . $domain), $iplookup);
  if ($iplookup) {
    _debug('Iplookup', $iplookup, true);
    $dnssec = in_array('; fully validated', $iplookup) ?? false ;
    $getaonly = array_values(preg_grep('/A\s.*/', $iplookup));
    preg_match('/A\s(.*)/', $getaonly[0], $version);
    $ip   = trim($version[1]);
  }
  $ip || $score -= 2;
  $ipv6 = strpos($ip6, ':') !== false;
  _debug('IP', $ip);
  _debug('IPv6', $ip6);

  $location = geoip_record_by_name($ip);
  _debug('Location', $location, true);

  if ($location) {
    $country = !empty($location['country_code']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['country_code']) : null;
    $city    = !empty($location['city']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['city']) : null;
    $state   = !empty($location['region']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['region']) : null;
    $lat     = !empty($location['latitude']) ? $location['latitude'] : null;
    $long    = !empty($location['longitude']) ? $location['longitude'] : null;
  }
  echo $newline;
  $statslastdate = date('Y-m-d H:i:s');
  $ping          = curl_init();
  curl_setopt($ping, CURLOPT_URL, 'https://api.uptimerobot.com/getMonitors?format=json&noJsonCallback=1&customUptimeRatio=7-30-60-90&responseTimes=1&responseTimesAverage=86400&apiKey=' . $row['stats_apikey']);
  curl_setopt($ping, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ping, CURLOPT_CONNECTTIMEOUT, 8);
  $uptr = json_decode(curl_exec($ping));
  curl_close($ping);
  _debug('Uptime Robot', $uptr, true);
  
  if ($uptr->stat === 'ok') {
    $responsetime    = $uptr->monitors->monitor{'0'}->responsetime{'0'}->value ?? 'n/a';
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
      $statslastdate = date('Y-m-d H:i:s');
  }

  if ($softwarename === 'diaspora') {
    $masterversion = $dmasterversion;
  } elseif ($softwarename === 'friendica') {
    $masterversion = $fmasterversion;
  } elseif ($softwarename === 'redmatrix') {
    $masterversion = $hmasterversion;
  }
  $hidden = $score <= 70;
  _debug('Hidden', $hidden ? 'yes' : 'no');
  // lets cap the scores or you can go too high or too low to never be effected by them
  if ($score > 100) {
    $score = 100;
  } elseif ($score < 0) {
    $score = 0;
  }
  $weightedscore = ($uptime + $score + ($active_users_monthly / 19999) - ((10 - $weight) * .12));
  //sql it

  $timenow = date('Y-m-d H:i:s');
  $sql_set     = 'UPDATE pods SET secure = $2, hidden = $3, ip = $4, ipv6 = $5, monthsmonitored = $6, uptime_alltime = $7, status = $8, date_laststats = $9, date_updated = $10, responsetime = $11, score = $12, adminrating = $13, country = $14, city = $15, state = $16, lat = $17, long = $18, userrating = $19, shortversion = $20, masterversion = $21, signup = $22, total_users = $23, active_users_halfyear = $24, active_users_monthly = $25, local_posts = $26, name = $27, comment_counts = $28, service_facebook = $29, service_tumblr = $30, service_twitter = $31, service_wordpress = $32, weightedscore = $33, service_xmpp = $34, softwarename = $35, sslvalid = $36, uptime_custom = $37, dnssec = $38, sslexpire = $39 WHERE domain = $1';
  $result_set  = pg_query_params($dbh, $sql_set, [$domain, 1, (int) $hidden, $ip, (int) $ipv6, $months, $uptime, $status, $statslastdate, $timenow, $responsetime, $score, $admin_rating, $country, $city, $state, $lat, $long, $user_rating, $shortversion, $masterversion, (int) $signup, $total_users, $active_users_halfyear, $active_users_monthly, $local_posts, $name, $comment_counts, (int) $service_facebook, (int) $service_tumblr, (int) $service_twitter, (int) $service_wordpress, $weightedscore, (int) $service_xmpp, $softwarename, $outputsslerror, $uptime_custom, (int) $dnssec, $sslexpire]);
  $result_set || die('Error in SQL query3: ' . pg_last_error());

  _debug('Score out of 100', $score);

  echo 'Success';
  
  echo $newline;
  echo $newline;
}

/**
 * Output a debug message and variable value
 * 
 * @param string $label
 * @param mixed  $var
 * @param bool   $dump
 */
function _debug($label, $var = null, $dump = false) {
  global $debug, $newline;

  if (!$debug) {
    return;
  }
  
  if ($dump || is_array($var)) {
    $output = print_r($var, true); 
  } elseif (is_bool($var)) {
    $output = $var ? 'true' : 'false';
  } else {
    $output = (string) $var;
  }
  
  printf('%s: %s%s', $label, $output, $newline);
}
