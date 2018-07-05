<?php

/**
 * This is just a single API for a pod for the Android app to get data.
 */

declare(strict_types=1);

use Poduptime\PodStatus;
use RedBeanPHP\R;

// Required parameters.
($_domain = $_GET['domain'] ?? null) || die('no domain given');

// Other parameters.
$_format = $_GET['format'] ?? '';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
    $pod = R::getRow('
        SELECT hgitdate, id, domain, status, secure, score, userrating, adminrating, city, state, country, lat, long, ip, ipv6, pingdomurl, monthsmonitored, uptimelast7, responsetimelast7, local_posts, comment_counts, dateCreated, dateUpdated, dateLaststats, hidden
        FROM pods_apiv1
        WHERE domain = ?
    ', [$_domain]);
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

if ($_format === 'json') {
    echo json_encode($pod);
} else {
    if ($pod['status'] === PodStatus::UP) {
        echo 'Status: Up<br>';
    }
    if ($pod['status'] === PodStatus::DOWN) {
        echo 'Status: Down<br>';
    }
    echo 'Last Git Pull: ' . $pod['hgitdate'] . '<br>';
    echo 'Uptime This Month ' . $pod['uptimelast7'] . '<br>';
    echo 'Months Monitored: ' . $pod['monthsmonitored'] . '<br>';
    echo 'Response Time: ' . $pod['responsetimelast7'] . '<br>';
    echo 'User Rating: ' . $pod['userrating'] . '<br>';
    echo 'Server Location: ' . $pod['country'] . '<br>';
    echo 'Latitude: ' . $pod['lat'] . '<br>';
    echo 'Longitude: ' . $pod['long'] . '<br>';
}
