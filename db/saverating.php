<?php

/**
 * Save pod rating.
 */

declare(strict_types=1);

use RedBeanPHP\R;

// Required parameters.
($_username = $_POST['username'] ?? null) || die('Name is required');
//($_userurl = $_POST['userurl'] ?? null) || die('no userurl given');//lets not annoy people on this for now
($_domain = $_POST['domain'] ?? null) || die('no pod domain given');
($_comment = $_POST['comment'] ?? null) || die('A comment is required');
($_rating = $_POST['rating'] ?? null) || die('A rating is required');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

try {
    $r             = R::dispense('ratingcomments');
    $r['domain']   = $_domain;
    $r['comment']  = $_comment;
    $r['rating']   = $_rating;
    $r['username'] = $_username;
    //$r['userurl']  = $_userurl;
    R::store($r);
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

print 1;
