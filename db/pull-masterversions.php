<?php
//* Copyright (c) 2017, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

use RedBeanPHP\R;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');

$softwares = [
  'diaspora'     => ['url' => 'https://raw.githubusercontent.com/diaspora/diaspora/master/config/defaults.yml', 'regex' => '/number:.*"(.*)"/'],
  'friendica'    => ['url' => 'https://raw.githubusercontent.com/friendica/friendica/master/boot.php', 'regex' => '/define.*\'FRIENDICA_VERSION\'.*\'(.*)\'/'],
  'redmatrix'    => ['url' => 'https://raw.githubusercontent.com/redmatrix/hubzilla/master/boot.php', 'regex' => '/define.*\'STD_VERSION\'.*\'(.*)\'/'],
  'socialhome'   => ['url' => 'https://raw.githubusercontent.com/jaywink/socialhome/master/socialhome/__init__.py', 'regex' => '/__version__ =.*"(.*)"/'],
  'social-relay' => ['url' => 'https://raw.githubusercontent.com/jaywink/social-relay/master/social_relay/config.py', 'regex' => '/VERSION.*"(.*)"/'],
];

foreach ($softwares as $software => $details) {
  $mv = curl_init();
  curl_setopt($mv, CURLOPT_URL, $details['url']);
  curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
  $outputmv = curl_exec($mv);
  curl_close($mv);

  if ($masterversion = preg_match($details['regex'], $outputmv, $version) ? $version[1] : '') {
    try {
      $m             = R::dispense('masterversions');
      $m['software'] = $software;
      $m['version']  = $masterversion;
      R::store($m);
    } catch (\RedBeanPHP\RedException $e) {
      die('Error in SQL query: ' . $e->getMessage());
    }
  }

  printf('%s:%s ', $software, $masterversion ?: 'n/a');
}
