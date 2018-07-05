<?php

/**
 * Crawl and add all pods from the-federation.info list.
 */

declare(strict_types=1);

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
    $sql  = '
        SELECT domain, status
        FROM pods
    ';
    $pods = R::getAll($sql);
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

//get all existing pod domains
$existingpods = array_column($pods, 'domain');

$foundpods = [];

//pulling all nodes for now
if ($pods = json_decode(file_get_contents('https://the-federation.info/graphql?query=%7Bnodes%7Bhost%20platform%7Bname%7Dprotocols%7Bname%7D%7D%7D&raw'), true)) {
    foreach ($pods['data']['nodes'] ?? [] as $poddata) {
        $protocols = array_column($poddata['protocols'] ?? [], 'name');

        //limiting to diaspora compatible for now
        if (in_array('diaspora', $protocols, true)) {
            $foundpods[] = $poddata['host'];
        }
    }
}

if ($pods = json_decode(file_get_contents('https://diasp.org/pods.json'), true)) {
    foreach ($pods ?? [] as $poddata) {
        $foundpods[] = $poddata['host'];
    }
}

$results = array_diff($foundpods, $existingpods);
foreach ($results as $result) {
    echo exec("php-cgi add.php domain={$result}") . "\r\n";
}
