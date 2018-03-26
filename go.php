<?php

use RedBeanPHP\R;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

// Other parameters.
$_domain = $_GET['domain'] ?? '';

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
  if ($_domain) {
    $click  = 'manualclick';
    $domain = R::getCell('SELECT domain FROM pods WHERE domain LIKE ?', [$_domain]);
    $domain || die('unknown domain');
  } else {
    $click  = 'autoclick';
    $domain = R::getCell('
      SELECT domain
      FROM pods
      WHERE signup
        AND score > 90
        AND pods.masterversion = shortversion
      ORDER BY random()
      LIMIT 1
    ');
    $domain || die('no domains exist');
  }

  $c           = R::dispense('clicks');
  $c['domain'] = $domain;
  $c[$click]   = 1;
  if (!(new CrawlerDetect())->isCrawler()) {
    R::store($c);
  }

  header('Location: https://' . $domain);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}
