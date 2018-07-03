<?php

if (PHP_SAPI !== 'cli') {
  header('HTTP/1.0 403 Forbidden');
  exit;
}

use RedBeanPHP\R;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
  $monthly_totals = R::getAll("
    SELECT
      to_char(date_checked, 'yyyy-mm') AS yymm,
      sum(total_users) / count(DISTINCT to_char(date_checked, 'HH24 dd')) as users,
      sum(local_posts) / count(DISTINCT to_char(date_checked, 'HH24 dd')) as posts,
      sum(comment_counts) / count(DISTINCT to_char(date_checked, 'HH24 dd')) as comments,
      count(domain) / count(DISTINCT to_char(date_checked, 'HH24 dd')) as pods,
      count(nullif(online, false)) as uptime, 
      count(nullif(online, true)) as downtime
    FROM checks
    GROUP BY yymm
  ");
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}
foreach ($monthly_totals as $monthly) {

  // Format date to timestamp.
  $timestamp = $monthly['yymm'].'-01 01:01:01-01';

  try {
    $p = R::findOrCreate('monthlystats', ['date_checked' => $timestamp]);

    $p['total_users']    = $monthly['users'];
    $p['total_posts']    = $monthly['posts'];
    $p['total_comments'] = $monthly['comments'];
    $p['total_pods']     = $monthly['pods'];
    if ($monthly['downtime']) {
      $p['total_uptime'] = round($monthly['downtime'] / $monthly['uptime'] * 100);
    } else {
      $p['total_uptime'] = 100;
    }

    R::store($p);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }
}
