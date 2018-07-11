<?php

/**
 * Pull pod info.
 */

declare(strict_types=1);

if ($_SERVER['SERVER_ADDR'] !== $_SERVER['REMOTE_ADDR']) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

use GeoIp2\Database\Reader;
use LanguageDetection\Language;
use Poduptime\PodStatus;
use RedBeanPHP\R;

$debug    = isset($_GET['debug']) || (isset($argv) && in_array('debug', $argv, true));
$sqldebug = isset($_GET['sqldebug']) || (isset($argv) && in_array('sqldebug', $argv, true));
$write    = !(isset($_GET['nowrite']) || (isset($argv) && in_array('nowrite', $argv, true)));
$newline  = PHP_SAPI === 'cli' ? "\n\n" : '<br><br>';

$_domain = $_GET['domain'] ?? null;

// Must have a domain, except if called from CLI.
$_domain || PHP_SAPI === 'cli' || die('No valid input');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
$sqldebug && R::fancyDebug(true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

// Setup GeoIP Database
$reader = new Reader($geoip2db);

try {
    $sql = '
        SELECT domain, score, date_created, weight, podmin_notify, email, masterversion, shortversion, status
        FROM pods
    ';

    $pods = [];
    if ($_domain) {
        $sql  .= ' WHERE domain = ?';
        $pods = R::getAll($sql, [$_domain]);
    } elseif (PHP_SAPI === 'cli' && (isset($argv) && in_array('Check_System_Deleted', $argv, true))) {
        $sql  .= ' WHERE status = ?';
        $pods = R::getAll($sql, [PodStatus::SYSTEM_DELETED]);
    } elseif (PHP_SAPI === 'cli') {
        $sql  .= ' WHERE status < ?';
        $pods = R::getAll($sql, [PodStatus::PAUSED]);
    }
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

foreach ($pods as $pod) {
    $domain    = $pod['domain'];
    $score     = (int) $pod['score'];
    $dateadded = $pod['date_created'];
    $weight    = $pod['weight'];
    $email     = $pod['email'];
    $notify    = $pod['podmin_notify'];
    $masterv   = $pod['masterversion'];
    $shortv    = $pod['shortversion'];
    $dbstatus  = $pod['status'];

    try {
        $ratings = R::getAll('
            SELECT rating
            FROM ratingcomments
            WHERE domain = ?
        ', [$domain]);
    } catch (\RedBeanPHP\RedException $e) {
        die('Error in SQL query: ' . $e->getMessage());
    }

    _debug('Domain', $domain);

    $user_ratings = [];
    foreach ($ratings as $rating) {
        $admin_ratings[] = $rating['rating'];
    }

    $user_rating = empty($user_ratings) ? 0 : round(array_sum($user_ratings) / count($user_ratings), 2);

    $d = new DOMDocument;
    libxml_use_internal_errors(true);
    $d->loadHTMLFile('https://' . $domain);
    $body = $d->getElementsByTagName('body')->item(0);
    if ($body->nodeValue) {
        $ld               = new Language;
        $detectedlanguage = strtoupper(key($ld->detect($body->nodeValue)->bestResults()->close()));
        _debug('Detected Language', $detectedlanguage);
    }

    if ($infos = file_get_contents('https://' . $domain . '/.well-known/nodeinfo')) {
        $info = json_decode($infos, true);
        $link = max($info['links'])['href'];
    } else {
        $link = 'https://' . $domain . '/nodeinfo/1.0';
    }

    _debug('Nodeinfo link', $link);

    $chss = curl_init();
    curl_setopt($chss, CURLOPT_URL, $link);
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

    $jsonssl = ($outputssl ? json_decode($outputssl) : null);

    if ($jsonssl !== null) {
        $xdver = $jsonssl->software->version ?? 0;
        preg_match_all('((?:\d(.|-)?)+(\.|-)\d+\.*)', $xdver, $dverr);
        $shortversion          = $dverr[0][0] ?? '0.0.0.0';
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
            if (is_array($jsonssl->services->outbound)) {
                $service_facebook  = in_array('facebook', $jsonssl->services->outbound, true);
                $service_twitter   = in_array('twitter', $jsonssl->services->outbound, true);
                $service_tumblr    = in_array('tumblr', $jsonssl->services->outbound, true);
                $service_wordpress = in_array('wordpress', $jsonssl->services->outbound, true);
            }
        }

        try {
            $c                   = R::dispense('checks');
            $c['domain']         = $domain;
            $c['online']         = true;
            $c['latency']        = $latency;
            $c['total_users']    = $total_users;
            $c['local_posts']    = $local_posts;
            $c['comment_counts'] = $comment_counts;
            $c['shortversion']   = $shortversion;
            if ($write) {
                R::store($c);
            } else {
                echo $c;
            }
        } catch (\RedBeanPHP\RedException $e) {
            die('Error in SQL query: ' . $e->getMessage());
        }

        $status = PodStatus::UP;
    }

    if (!$jsonssl) {
        _debug('Connection', 'Can not connect to pod');

        try {
            $c            = R::dispense('checks');
            $c['domain']  = $domain;
            $c['online']  = false;
            $c['error']   = $outputsslerror;
            $c['latency'] = $latency;
            if ($write) {
                R::store($c);
            } else {
                echo $c;
            }
        } catch (\RedBeanPHP\RedException $e) {
            die('Error in SQL query: ' . $e->getMessage());
        }

        $score  -= 1;
        $status = PodStatus::DOWN;
    }

    _debug('Version code', $shortversion);
    _debug('Signup Open', $signup);

    $dnsserver = !empty($dnsserver) ? $dnsserver : '1.1.1.1';
    $delv      = new NPM\Xec\Command("delv @{$dnsserver} {$domain}");
    $delv->throwExceptionOnError(false);

    $ip         = '';
    $iplookupv4 = explode(PHP_EOL, trim($delv->execute([], null, 15)->stdout));
    $dnssec     = in_array('; fully validated', $iplookupv4, true) ?? false;
    $getaonly   = array_values(preg_grep('/\s+IN\s+A\s+.*/', $iplookupv4));
    if ($getaonly) {
        preg_match('/A\s(.*)/', $getaonly[0], $aversion);
        $ip = trim($aversion[1]) ?? '';
    }
    $ip || $score -= 2;

    $iplookupv6 = explode(PHP_EOL, trim($delv->execute(['AAAA'], null, 15)->stdout));
    $ipv6       = (bool) preg_grep('/\s+IN\s+AAAA\s+.*/', $iplookupv6);

    _debug('IPv4', $ip);
    _debug('IPv6', $ipv6);

    $geo         = $reader->city($ip);
    $countryname = $geo->country->name ?? null ?: null;
    $country     = $geo->country->isoCode ?? null ?: null;
    $city        = $geo->city->name ?? null ?: null;
    $state       = $geo->mostSpecificSubdivision->name ?? null ?: null;
    $lat         = $geo->location->latitude ?? null ?: 0;
    $long        = $geo->location->longitude ?? null ?: 0;

    _debug('Location', json_encode($geo->raw), true);

    echo $newline;
    $statslastdate = date('Y-m-d H:i:s');

    $diff   = (new DateTime())->diff(new DateTime($dateadded));
    $months = $diff->m + ($diff->y * 12);
    $days   = $diff->days;

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
        $masterdata = R::getRow('SELECT version, devlastcommit, releasedate FROM masterversions WHERE software = ? ORDER BY id DESC LIMIT 1', [$softwarename]);
    } catch (\RedBeanPHP\RedException $e) {
        die('Error in SQL query: ' . $e->getMessage());
    }

    $masterversion = ($masterdata['version'] ?? '0.0.0.0');
    $devlastcommit = ($masterdata['devlastcommit'] ?? date('Y-m-d H:i:s'));
    $releasedate   = ($masterdata['releasedate'] ?? date('Y-m-d H:i:s'));
    _debug('Masterversion', $masterversion);
    $masterversioncheck = explode('.', $masterversion);
    $shortversioncheck  = (strpos($shortversion, '.') ? explode('.', $shortversion) : $shortversion);
    //this is still off with a pod with v1 as total version. cant explode that, won't have a [0] or [1] later to use either

    _debug('Days since master code release', date_diff(new DateTime($releasedate), new DateTime())->format('%d'));

    try {
        $lastpodupdates = R::getRow('SELECT DISTINCT ON (shortversion) shortversion, date_checked FROM checks WHERE domain = ? AND shortversion IS NOT NULL ORDER BY shortversion DESC LIMIT 1', [$domain]);
    } catch (\RedBeanPHP\RedException $e) {
        die('Error in SQL query: ' . $e->getMessage());
    }

    $lastdatechecked = ($lastpodupdates['date_checked'] ?? date('Y-m-d H:i:s'));
    $devlastdays     = $devlastcommit ? date_diff(new DateTime($devlastcommit), new DateTime())->format('%a') : 30;//tmp//if no dev branch then what?

    _debug('Dev git last commit was  ', $devlastdays);
    $updategap = date_diff(new DateTime($lastdatechecked), new DateTime($devlastcommit))->format('%a');

    if (strpos($xdver, 'dev') !== false || strpos($xdver, 'rc') !== false || $shortversioncheck > $masterversioncheck) {
        //tmp//if pod is on the development branch - see when you last updated your pod and when the last commit was made to dev branch - if the repo is active and your not updating every 120 days why are you on dev branch?

        if ($updategap + $devlastdays > 130) {
            _debug('Outdated', 'Yes');
            $score -= 2;
        }
    } elseif (($masterversioncheck[1] - $shortversioncheck[1]) > 1) {
        ///tmp/If pod is two versions off AND it's been more than 60 days since that release came out AND your on the master production branch
        _debug('Outdated', 'Yes');
        $score     -= 2;
        $updategap = date_diff(new DateTime($lastdatechecked), new DateTime($releasedate))->format('%a');
    } elseif ($updategap - date_diff(new DateTime($releasedate), new DateTime())->format('%a') > 90) {
        _debug('Outdated', 'Yes');
        $score     -= 2;
        $updategap = date_diff(new DateTime($lastdatechecked), new DateTime($releasedate))->format('%a');
    } else {
        $updategap = date_diff(new DateTime($lastdatechecked), new DateTime($releasedate))->format('%a');
    }
    _debug('Pod code was updated after ', $updategap);

    if ($score < 70 && $notify && !(isset($argv) && in_array('develop', $argv, true))) {
        $to      = $email;
        $headers = ['From: ' . $adminemail, 'Bcc: ' . $adminemail];
        $subject = 'Monitoring notice from poduptime';
        $message = 'Notice for ' . $domain . '. Your score fell to ' . $score . ' and your pod is now not showing on the site.';
        @mail($to, $subject, $message, implode("\r\n", $headers));
        _debug('Mail Notice', 'sent to ' . $email);
    }
    if ($score > 100) {
        $score = 100;
    } elseif ($score < 0) {
        $score = 0;
        if ($masterv !== $shortv) {
            $status = PodStatus::SYSTEM_DELETED;
        }
    }
    _debug('Score', $score);
    $weightedscore = ($uptime + $score - (10 - $weight)) / 2;
    _debug('Weighted Score', $weightedscore);

    try {
        $p                     = R::findOne('pods', 'domain = ?', [$domain]);
        $p['ip']               = $ip;
        $p['ipv6']             = $ipv6;
        $p['daysmonitored']    = $days;
        $p['monthsmonitored']  = $months;
        $p['uptime_alltime']   = $uptime;
        $p['status']           = $status;
        $p['date_laststats']   = $statslastdate;
        $p['date_updated']     = date('Y-m-d H:i:s');
        $p['latency']          = $avglatency;
        $p['score']            = $score;
        $p['country']          = $country;
        $p['countryname']      = $countryname;
        $p['city']             = $city;
        $p['state']            = $state;
        $p['lat']              = $lat;
        $p['long']             = $long;
        $p['detectedlanguage'] = $detectedlanguage;
        $p['userrating']       = $user_rating;
        $p['masterversion']    = $masterversion;
        $p['weightedscore']    = $weightedscore;
        $p['sslvalid']         = $outputsslerror;
        $p['dnssec']           = $dnssec;
        $p['sslexpire']        = $sslexpire;
        if ($dbstatus === PodStatus::UP && $status === PodStatus::UP) {
            $p['shortversion']          = $shortversion;
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
            $p['softwarename']          = $softwarename;
        }

        if ($write) {
            R::store($p);
        } else {
            echo 'Data not saved, testing only';
            echo $newline;
        }
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

    $output = (string) $var;
    if ($dump || is_array($var)) {
        $output = print_r($var, true);
    } elseif (is_bool($var)) {
        $output = $var ? 'true' : 'false';
    }

    printf('%s: %s%s', $label, $output, $newline);
}
