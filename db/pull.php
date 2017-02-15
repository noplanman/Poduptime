<?php
//* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

$debug   = isset($_GET['debug']) || (isset($argv) && in_array('debug', $argv, true));
$newline = PHP_SAPI === 'cli' ? "\n" : '<br>';

$_domain = $_GET['domain'] ?? '';

require_once __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

if ($_domain) {
  $sql = 'SELECT domain,score,date_created,adminrating,weight,hidden,podmin_notify,email FROM pods WHERE domain = $1';
  $result = pg_query_params($dbh, $sql, [$_domain]);
} elseif (PHP_SAPI === 'cli') {
  $sql = 'SELECT domain,score,date_created,adminrating,weight,hidden,podmin_notify,email FROM pods';
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
  $hiddennow = $row['hidden'];
  $email     = $row['email'];
  $notify    = $row['podmin_notify'];
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
  curl_setopt($chss, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($chss, CURLOPT_TIMEOUT, 30);
  curl_setopt($chss, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($chss, CURLOPT_CERTINFO, 1);
  curl_setopt($chss, CURLOPT_CAINFO, $cafullpath);
  $outputssl      = curl_exec($chss);
  $outputsslerror = curl_error($chss);
  $info           = curl_getinfo($chss, CURLINFO_CERTINFO);
  $conntime       = curl_getinfo($chss, CURLINFO_CONNECT_TIME);
  $nstime         = curl_getinfo($chss, CURLINFO_NAMELOOKUP_TIME);
  $latency        = $conntime - $nstime;
  $sslexpire      = $info[0]['Expire date'] ?? null;
  curl_close($chss);

  _debug('Nodeinfo output', $outputssl, true);
  _debug('Nodeinfo output error', $outputsslerror, true);
  _debug('Cert expire date', $sslexpire);
  _debug('Conntime', $conntime);
  _debug('NStime', $nstime);
  _debug('Latency', $latency);
  
  $jsonssl = json_decode($outputssl);

  $xdver                 = $jsonssl->software->version ?? 0;
  $dverr                 = explode('-', trim($xdver));
  $shortversion          = $dverr[0];
  $signup                = ($jsonssl->openRegistrations ?? false) === true;
  $softwarename          = $jsonssl->software->name ?? 'unknown';
  $name                  = $jsonssl->metadata->nodeName ?? $softwarename;
  $total_users           = $jsonssl->usage->users->total ?? 0;
  $active_users_halfyear = $jsonssl->usage->users->activeHalfyear ?? 0;
  $active_users_monthly  = $jsonssl->usage->users->activeMonth ?? 0;
  $local_posts           = $jsonssl->usage->localPosts ?? 0;
  $comment_counts        = $jsonssl->usage->localComments ?? 0;
  $service_xmpp          = ($jsonssl->metadata->xmppChat ?? false) === true;
  $service_facebook      = false;
  $service_twitter       = false;
  $service_tumblr        = false;
  $service_wordpress     = false;
  if (json_last_error() === 0) {
    (!$jsonssl->software->version) || $score += 1;
    $service_facebook      = in_array('facebook', $jsonssl->services->outbound, true);
    $service_twitter       = in_array('twitter', $jsonssl->services->outbound, true);
    $service_tumblr        = in_array('tumblr', $jsonssl->services->outbound, true);
    $service_wordpress     = in_array('wordpress', $jsonssl->services->outbound, true);
  }
    
  if ($jsonssl !== null) {
    $status        = 'Up';
    $sql_checks    = 'INSERT INTO checks (domain, online, latency, total_users, local_posts, comment_counts, shortversion) VALUES ($1, $2, $3, $4, $5, $6, $7)';
    $result_checks = pg_query_params($dbh, $sql_checks, [$domain, 1, $latency, $total_users, $local_posts, $comment_counts, $shortversion]);
    $result_checks || die('Error in SQL query: ' . pg_last_error());
  }
  
  if (!$jsonssl) {    
    _debug('Connection', 'Can not connect to pod');
    $sql_errors    = 'INSERT INTO checks (domain, online, error, latency) VALUES ($1, $2, $3, $4)';
    $result_errors = pg_query_params($dbh, $sql_errors, [$domain, 0, $outputsslerror, $latency]);
    $result_errors || die('Error in SQL query: ' . pg_last_error());
    $score         -= 1;
    $shortversion  = '0.error';
    $status        = 'Down';
  }
  
  _debug('Version code', $shortversion);
  _debug('Signup Open', $signup);

  $iplookupv4 = [];
  $ip         = '';
  exec(escapeshellcmd('delv @' . $dnsserver . ' ' . $domain . ' 2>&1'), $iplookupv4);
  $dnssec   = in_array('; fully validated', $iplookupv4, true) ?? false;
  $getaonly = array_values(preg_grep('/\s+IN\s+A\s+.*/', $iplookupv4));
  if ($getaonly) {
    preg_match('/A\s(.*)/', $getaonly[0], $aversion);
    $ip = trim($aversion[1]) ?? '';
  }
  $iplookupv6 = [];
  $ipv6 = null;
  exec(escapeshellcmd('delv @' . $dnsserver . ' ' . $domain . ' AAAA 2>&1'), $iplookupv6);
  $getaaaaonly = array_values(preg_grep('/\s+IN\s+AAAA\s+.*/', $iplookupv6));
  if ($getaaaaonly) {
    preg_match('/AAAA\s(.*)/', $getaaaaonly[0], $aaaaversion);
    $ipv6   = trim($aaaaversion[1]) ?? '';
  }
  $ip || $score -= 2;

  _debug('IPv4', $ip);
  _debug('Iplookupv4', $iplookupv4, true);
  _debug('IPv6', $ipv6);
  _debug('Iplookupv6', $iplookupv6, true);

  $location = geoip_record_by_name($ip);
  _debug('Location', $location, true);
  $country  = !empty($location['country_code']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['country_code']) : null;
  $city     = !empty($location['city']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['city']) : null;
  $state    = !empty($location['region']) ? iconv('UTF-8', 'UTF-8//IGNORE', $location['region']) : null;
  $lat      = !empty($location['latitude']) ? $location['latitude'] : 0;
  $long     = !empty($location['longitude']) ? $location['longitude'] : 0;
  
  echo $newline;
  $statslastdate = date('Y-m-d H:i:s');

  $diff         = (new DateTime())->diff(new DateTime($dateadded));
  $months       = $diff->m + ($diff->y * 12);
    
  $avglatency     = 0;
  $sqllatency     = 'SELECT round(avg(latency) * 1000) AS latency FROM checks WHERE domain = $1';
  $resultlatency  = pg_query_params($dbh, $sqllatency, [$domain]);
  $resultlatency  || die('Error in SQL query resultchecks: ' . pg_last_error());
  $avglatency     = pg_fetch_result($resultlatency, 0);

  $uptime       = 0;
  $sqlonline    = 'SELECT avg(online::int) * 100 AS online FROM checks WHERE domain = $1';
  $resultonline = pg_query_params($dbh, $sqlonline, [$domain]);
  $resultonline || die('Error in SQL query resultchecks: ' . pg_last_error());
  $uptime       = round(pg_fetch_result($resultonline, 0),2);
  
  _debug('Uptime', $uptime);

  $sqlmasters    = 'SELECT version FROM masterversions WHERE software = $1 ORDER BY date_checked LIMIT 1';
  $resultmasters = pg_query_params($dbh, $sqlmasters, [$softwarename]);
  $resultmasters || die('Error in SQL query: ' . pg_last_error());
  $masterversion = pg_fetch_result($resultmasters, 0);

  _debug('Masterversion', $masterversion);
  
  $hidden = $score <= 70;
  _debug('Hidden', $hidden ? 'yes' : 'no');

  if ($hiddennow === 'f' && $hidden && $notify === 't') {
    $to      = $email;
    $headers = ['From: ' . $adminemail, 'Bcc: ' . $adminemail];
    $subject = 'Monitoring notice from poduptime';
    $message = 'Notice for ' . $domain . '. Your score fell to ' . $score . ' and your pod is now marked as hidden.';
    @mail($to, $subject, $message, implode("\r\n", $headers));
    _debug('Mail Notice', 'sent to '.$email);
  }
  if ($score > 100) {
    $score = 100;
  } elseif ($score < 0) {
    $score = 0;
  }
  $weightedscore = ($uptime + $score - (10 - $weight)) / 2;
  _debug('Weighted Score', $weightedscore);

  $timenow    = date('Y-m-d H:i:s');
  $sql_set    = 'UPDATE pods SET secure = $2, hidden = $3, ip = $4, ipv6 = $5, monthsmonitored = $6, uptime_alltime = $7, status = $8, date_laststats = $9, date_updated = $10, latency = $11, score = $12, adminrating = $13, country = $14, city = $15, state = $16, lat = $17, long = $18, userrating = $19, shortversion = $20, masterversion = $21, signup = $22, total_users = $23, active_users_halfyear = $24, active_users_monthly = $25, local_posts = $26, name = $27, comment_counts = $28, service_facebook = $29, service_tumblr = $30, service_twitter = $31, service_wordpress = $32, weightedscore = $33, service_xmpp = $34, softwarename = $35, sslvalid = $36, dnssec = $37, sslexpire = $38 WHERE domain = $1';
  $result_set = pg_query_params($dbh, $sql_set, [$domain, 1, (int) $hidden, $ip, (int) ($ipv6 !== null), $months, $uptime, $status, $statslastdate, $timenow, $avglatency, $score, $admin_rating, $country, $city, $state, $lat, $long, $user_rating, $shortversion, $masterversion, (int) $signup, $total_users, $active_users_halfyear, $active_users_monthly, $local_posts, $name, $comment_counts, (int) $service_facebook, (int) $service_tumblr, (int) $service_twitter, (int) $service_wordpress, $weightedscore, (int) $service_xmpp, $softwarename, $outputsslerror, (int) $dnssec, $sslexpire]);
  $result_set || die('Error in SQL query3: ' . pg_last_error());

  _debug('Score out of 100', $score);

  echo 'Success '.$domain;
  
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
