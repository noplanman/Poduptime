<?php
//* Copyright (c) 2017, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */
require __DIR__ . '/../config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$softwares = [
  'diaspora'  => ['url' => 'https://raw.githubusercontent.com/diaspora/diaspora/master/config/defaults.yml', 'regex' => '/number:.*"(.*)"/'],
  'friendica' => ['url' => 'https://raw.githubusercontent.com/friendica/friendica/master/boot.php', 'regex' => '/define.*\'FRIENDICA_VERSION\'.*\'(.*)\'/'],
  'redmatrix'  => ['url' => 'https://raw.githubusercontent.com/redmatrix/hubzilla/master/boot.php', 'regex' => '/define.*\'STD_VERSION\'.*\'(.*)\'/'],
];

foreach ($softwares as $software => $details) {
  $mv = curl_init();
  curl_setopt($mv, CURLOPT_URL, $details['url']);
  curl_setopt($mv, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($mv, CURLOPT_RETURNTRANSFER, 1);
  $outputmv = curl_exec($mv);
  curl_close($mv);

  if ($masterversion = preg_match($details['regex'], $outputmv, $version) ? $version[1] : '') {
    $sql    = 'INSERT INTO masterversions (software,version) VALUES ($1,$2)';
    $result = pg_query_params($dbh, $sql, [$software, $masterversion]);
    $result || die('Error in SQL query: ' . pg_last_error());
  }

  printf('%s:%s ', $software, $masterversion ?: 'n/a');
}
