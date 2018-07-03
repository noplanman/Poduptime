<?php
//* Copyright (c) 2017, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */

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

$softwares = [
  'diaspora'     => ['repo' => 'diaspora/diaspora', 'gitsite' => 'api.github.com', 'gittype' => 'github', 'devbranch' => 'develop'],
  'friendica'    => ['repo' => 'friendica/friendica', 'gitsite' => 'api.github.com', 'gittype' => 'github', 'devbranch' => 'develop'],
  'hubzilla'     => ['repo' => 'hubzilla%2fcore', 'gitsite' => 'framagit.org', 'gittype' => 'gitlab', 'devbranch' => 'dev'],
  'pleroma'      => ['repo' => 'pleroma%2fpleroma', 'gitsite' => 'git.pleroma.social', 'gittype' => 'gitlab', 'devbranch' => 'develop'],
  'socialhome'   => ['repo' => 'jaywink/socialhome', 'gitsite' => 'api.github.com', 'gittype' => 'github', 'devbranch' => ''],
  'social-relay' => ['repo' => 'jaywink/social-relay', 'gitsite' => 'api.github.com', 'gittype' => 'github', 'devbranch' => ''],
  'ganggo'       => ['repo' => 'ganggo/ganggo', 'gitsite' => 'api.github.com', 'gittype' => 'github', 'devbranch' => ''],
];

$opts = [
  'http'         => ['method' => 'GET', 'header' => ['User-Agent: Poduptime']]
];

foreach ($softwares as $software => $details) {
    if ($details['gittype'] == 'github') {
        $context = stream_context_create($opts);
        $releasejson = json_decode(file_get_contents('https://' . $details["gitsite"] . '/repos/' . $details["repo"] . '/releases/latest', false, $context));
        if ($details["devbranch"]) {
            $commitjson = json_decode(file_get_contents('https://' . $details["gitsite"] . '/repos/' . $details["repo"] . '/commits/' . $details["devbranch"], false, $context));
        } else {
            $commitjson = '';
        }
        if ($masterversion = $releasejson->tag_name ? str_replace('v', '', $releasejson->tag_name) : '') {
            try {
                $m             = R::dispense('masterversions');
                $m['software'] = $software;
                $m['version']  = $masterversion;
                if ($releasedate = $releasejson ? $releasejson->published_at : '') {
                    $m['releasedate'] = $releasedate;
                }
                if ($devlastcommit = $commitjson ? $commitjson->commit->author->date : '') {
                    $m['devlastcommit'] = $devlastcommit;
                }
                R::store($m);
            } catch (\RedBeanPHP\RedException $e) {
                die('Error in SQL query: ' . $e->getMessage());
            }
        }
    } elseif ($details['gittype'] == 'gitlab') {
        $context = stream_context_create($opts);
        $releasejson = json_decode(file_get_contents('https://' . $details["gitsite"] . '/api/v4/projects/' . $details["repo"] . '/repository/tags', false, $context));
        if ($details["devbranch"]) {
            $commitjson = json_decode(file_get_contents('https://' . $details["gitsite"] . '/api/v4/projects/' . $details["repo"] . '/repository/commits/' . $details["devbranch"], false, $context));
        } else {
            $commitjson = '';
        }
        if ($masterversion = $releasejson[0]->name ? str_replace('v', '', $releasejson[0]->name) : '') {
            try {
                $m = R::dispense('masterversions');
                $m['software'] = $software;
                $m['version'] = $masterversion;
                if ($releasedate = $releasejson[0] ? $releasejson[0]->commit->created_at : '') {
                    $m['releasedate'] = $releasedate;
                }
                if ($devlastcommit = $commitjson ? $commitjson->created_at : '') {
                    $m['devlastcommit'] = $devlastcommit;
                }
                R::store($m);
            } catch (\RedBeanPHP\RedException $e) {
                die('Error in SQL query: ' . $e->getMessage());
            }
        }
    }


  printf('%s:%s:%s ', $software, $masterversion, $devlastcommit ?: 'n/a');
}
