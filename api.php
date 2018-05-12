<?php
//Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file.

use RedBeanPHP\R;

($_GET['key'] ?? null) === '4r45tg' || die;

// Other parameters.
$_format   = $_GET['format'] ?? '';
$_method   = $_GET['method'] ?? '';
$_callback = $_GET['callback'] ?? '';

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

if ($_format === 'georss') {
  echo <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">
<title>Diaspora Pods</title>
<subtitle>IP Locations of Diaspora pods on {$_SERVER['HTTP_HOST']}</subtitle>
<link href="https://{$_SERVER['HTTP_HOST']}/"/>

EOF;

  try {
    $pods = R::getAll('
      SELECT name, monthsmonitored, responsetimelast7, uptimelast7, dateupdated, score, domain, country, lat, long
      FROM pods_apiv1
    ');
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  foreach ($pods as $pod) {
    $summary = sprintf(
      'This pod %1$s has been watched for %2$s months and its average ping time is %3$s with uptime of %4$s%% this month and was last checked on %5$s. On a score of 100 this pod is a %6$s right now',
      htmlentities($pod['name'], ENT_QUOTES),
      $pod['monthsmonitored'],
      $pod['responsetimelast7'],
      $pod['uptimelast7'],
      $pod['dateupdated'],
      $pod['score']
    );
    echo <<<EOF
<entry>
  <title>https://{$pod['domain']}</title>
  <link href="https://{$pod['domain']}"/>
  <id>urn:{$pod['domain']}</id>
  <summary>Pod Location is: {$pod['country']}
	&#xA;
{$summary}</summary>
  <georss:point>{$pod['lat']} {$pod['long']}</georss:point>
  <georss:featureName>{$pod['domain']}</georss:featureName>
</entry>

EOF;
  }
  echo '</feed>';
} elseif ($_format === 'json') {

  try {
    $pods = R::getAll('
      SELECT id, domain, status, secure, score, userrating, adminrating, city, state, country, lat, long, ip, ipv6, pingdomurl, monthsmonitored, uptimelast7, responsetimelast7, local_posts, comment_counts, dateCreated, dateUpdated, dateLaststats, hidden
      FROM pods_apiv1
    ');
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  //json output, thx Vipul A M for fixing this
  header('Content-type: application/json');

  $obj = [
    'podcount' => count($pods),
    'pods'     => allToString($pods),
  ];
  if ($_method === 'jsonp') {
    print $_callback . '(' . json_encode($obj) . ')';
  } else {
    print json_encode($obj);
  }
} else {
  try {
    $pods = R::getAll('
      SELECT domain, uptimelast7, country
      FROM pods_apiv1
    ');
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  $i = 0;
  foreach ($pods as $pod) {
    $i++ > 0 && print ',';
    printf(
      '%1$s Up %2$s%% This Month - Located in: %3$s',
      $pod['domain'],
      $pod['uptimelast7'],
      $pod['country']
    );
  }
}

/**
 * Convert all passed items to strings.
 *
 * This method is for backwards compatibility of APIv1 only!
 * After v2 is released and stable, this can safely be removed.
 *
 * @param array $arr List of all elements to stringify.
 *
 * @return array
 */
function allToString(array $arr)
{
  $ret = $arr;
  foreach ($ret as &$item) {
    if (is_array($item)) {
      /** @var array $item */
      foreach ($item as &$field) {
        $field !== null && $field = (string) $field;
      }
    } else {
      $item !== null && $item = (string) $item;
    }
    unset($field, $item);
  }

  return $ret;
}
