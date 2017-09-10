<?php
//* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

use RedBeanPHP\R;

$debug   = isset($_GET['debug']) || (isset($argv) && in_array('debug', $argv, true));
$newline = PHP_SAPI === 'cli' ? "\n" : '<br>';

$_domain = $_GET['domain'] ?? null;

// Must have a domain, except if called from CLI.
$_domain || PHP_SAPI === 'cli' || die('No valid input');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');

try {
  $sql = '
    SELECT domain, score, date_created, adminrating, weight, hidden, podmin_notify, email
    FROM pods
  ';

  $pods = [];
  if ($_domain) {
    $sql .= ' WHERE domain = ?';
    $pods = R::getAll($sql, [$_domain]);
  } elseif (PHP_SAPI === 'cli') {
    $pods = R::getAll($sql);
  }
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

foreach ($pods as $pod) {
  $domain    = $pod['domain'];
  $score     = (int) $pod['score'];
  $dateadded = $pod['date_created'];
  $admindb   = (int) $pod['adminrating'];
  $weight    = $pod['weight'];
  $hiddennow = $pod['hidden'];
  $email     = $pod['email'];
  $notify    = $pod['podmin_notify'];

  try {
    $ratings = R::getAll('
      SELECT admin, rating
      FROM rating_comments
      WHERE domain = ?
    ', [$domain]);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  _debug('Domain', $domain);

  $user_ratings  = [];
  $admin_ratings = [];
  foreach ($ratings as $rating) {
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
    $service_facebook  = in_array('facebook', $jsonssl->services->outbound, true);
    $service_twitter   = in_array('twitter', $jsonssl->services->outbound, true);
    $service_tumblr    = in_array('tumblr', $jsonssl->services->outbound, true);
    $service_wordpress = in_array('wordpress', $jsonssl->services->outbound, true);
  }

  if ($jsonssl !== null) {
    $status = 'Up';

    try {
      $c                   = R::dispense('checks');
      $c['domain']         = $domain;
      $c['online']         = true;
      $c['latency']        = $latency;
      $c['total_users']    = $total_users;
      $c['local_posts']    = $local_posts;
      $c['comment_counts'] = $comment_counts;
      $c['shortversion']   = $shortversion;
      R::store($c);
    } catch (\RedBeanPHP\RedException $e) {
      die('Error in SQL query: ' . $e->getMessage());
    }
  }

  if (!$jsonssl) {
    _debug('Connection', 'Can not connect to pod');

    try {
      $c            = R::dispense('checks');
      $c['domain']  = $domain;
      $c['online']  = false;
      $c['error']   = $outputsslerror;
      $c['latency'] = $latency;
      R::store($c);
    } catch (\RedBeanPHP\RedException $e) {
      die('Error in SQL query: ' . $e->getMessage());
    }

    $score        -= 1;
    $shortversion = '0.error';
    $status       = 'Down';
  }

  _debug('Version code', $shortversion);
  _debug('Signup Open', $signup);

  $delv = new NPM\Xec\Command("delv @{$dnsserver} {$domain}");
  $delv->throwExceptionOnError(false);

  $ip         = '';
  $iplookupv4 = explode(PHP_EOL, trim($delv->execute([], null, 15)->stdout));
  $dnssec     = in_array('; fully validated', $iplookupv4, true) ?? false;
  $getaonly   = array_values(preg_grep('/\s+IN\s+A\s+.*/', $iplookupv4));
  if ($getaonly) {
    preg_match('/A\s(.*)/', $getaonly[0], $aversion);
    $ip = trim($aversion[1]) ?? '';
  }

  $ipv6        = false;
  $iplookupv6  = explode(PHP_EOL, trim($delv->execute(['AAAA'], null, 15)->stdout));
  $getaaaaonly = array_values(preg_grep('/\s+IN\s+AAAA\s+.*/', $iplookupv6));
  if ($getaaaaonly) {
    preg_match('/AAAA\s(.*)/', $getaaaaonly[0], $aaaaversion);
    $ipv6 = trim($aaaaversion[1]) ?? '';
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

  $diff   = (new DateTime())->diff(new DateTime($dateadded));
  $months = $diff->m + ($diff->y * 12);

  try {
    $checks = R::getRow('
      SELECT
        round(avg(latency) * 1000) AS latency,
        round(avg(online::INT) * 100, 2) AS online
      FROM checks
      WHERE domain = ?
    ', [$domain]);

    $avglatency = $checks['latency'] ?? 0;
    $uptime     = $checks['online'] ?? 0;
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  _debug('Uptime', $uptime);

  try {
    $masterversion = R::getCell('SELECT version FROM masterversions WHERE software = ? ORDER BY id DESC LIMIT 1', [$softwarename]);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  _debug('Masterversion', $masterversion);
  $masterversioncheck = explode('.',$masterversion);
  $shortversioncheck = explode('.',$shortversion);
  if (($masterversioncheck[1] - $shortversioncheck[1]) > 1) {
    _debug('Outdated', 'Yes');$score -= 2;
  }

  $hidden = $score <= 70;
  _debug('Hidden', $hidden ? 'yes' : 'no');

  if (!$hiddennow && $hidden && $notify) {
    $to      = $email;
    $headers = ['From: ' . $adminemail, 'Bcc: ' . $adminemail];
    $subject = 'Monitoring notice from poduptime';
    $message = 'Notice for ' . $domain . '. Your score fell to ' . $score . ' and your pod is now marked as hidden.';
    @mail($to, $subject, $message, implode("\r\n", $headers));
    _debug('Mail Notice', 'sent to ' . $email);
  }
  if ($score > 100) {
    $score = 100;
  } elseif ($score < 0) {
    $score = 0;
  }
  _debug('Score', $score);
  $weightedscore = ($uptime + $score - (10 - $weight)) / 2;
  _debug('Weighted Score', $weightedscore);

  try {
    $p                          = R::findOne('pods', 'domain = ?', [$domain]);
    $p['secure']                = true;
    $p['hidden']                = $hidden;
    $p['ip']                    = $ip;
    $p['ipv6']                  = ($ipv6 !== null);
    $p['monthsmonitored']       = $months;
    $p['uptime_alltime']        = $uptime;
    $p['status']                = $status;
    $p['date_laststats']        = $statslastdate;
    $p['date_updated']          = date('Y-m-d H:i:s');
    $p['latency']               = $avglatency;
    $p['score']                 = $score;
    $p['adminrating']           = $admin_rating;
    $p['country']               = $country;
    $p['city']                  = $city;
    $p['state']                 = $state;
    $p['lat']                   = $lat;
    $p['long']                  = $long;
    $p['userrating']            = $user_rating;
    $p['shortversion']          = $shortversion;
    $p['masterversion']         = $masterversion;
    $p['signup']                = $signup;
    $p['total_users']           = $total_users;
    $p['active_users_halfyear'] = $active_users_halfyear;
    $p['active_users_monthly']  = $active_users_monthly;
    $p['local_posts']           = $local_posts;
    $p['name']                  = $name;
    $p['comment_counts']        = $comment_counts;
    $p['service_facebook']      = $service_facebook;
    $p['service_tumblr']        = $service_tumblr;
    $p['service_twitter']       = $service_twitter;
    $p['service_wordpress']     = $service_wordpress;
    $p['service_xmpp']          = $service_xmpp;
    $p['weightedscore']         = $weightedscore;
    $p['softwarename']          = $softwarename;
    $p['sslvalid']              = $outputsslerror;
    $p['dnssec']                = $dnssec;
    $p['sslexpire']             = $sslexpire;
    
    // @todo Temporary fix! https://github.com/gabordemooij/redbean/issues/547
    foreach ($p->getProperties() as $key => $value) {
      $p[$key] = $value;
    }

    R::store($p);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  echo 'Success ' . $domain;

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
function _debug($label, $var = null, $dump = false)
{
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
