<?php

/**
 * Main entry point for podupti.me.
 */

declare(strict_types=1);

use Carbon\Carbon;
use RedBeanPHP\R;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);

// CloudFlare country code pull.
$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';

$lastfile     = 'db/last.data';
$detailedview = isset($_GET['detailedview']);
$mapview      = isset($_GET['mapview']);
$statsview    = isset($_GET['statsview']);
$podmin       = isset($_GET['podmin']);
$podminedit   = isset($_GET['podminedit']);
$edit         = isset($_GET['edit']);
$add          = isset($_GET['add']);
$gettoken     = isset($_GET['gettoken']);
$simpleview   = !($detailedview || $mapview || $podmin || $podminedit || $statsview);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Open Source Social Network Pod Uptime Status</title>
    <meta name="keywords" content="diaspora, federated pods, <?php echo $_SERVER['HTTP_HOST'] ?>, friendica, hubzilla, open source social, open source social network"/>
    <meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!"/>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/poduptime.css"/>
    <link rel="stylesheet" href="node_modules/featherlight/release/featherlight.min.css"/>
    <link rel="stylesheet" href="node_modules/tablesorter/dist/css/theme.bootstrap_4.min.css"/>
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST'] ?>"/>
    <meta property="og:title" content="Social Network Pod Finder"/>
    <meta property="og:type" content="website"/>
    <meta property="og:description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
</head>
<body>

<?php
$navs = [
    'views' => [
        ['text' => 'Simple View', 'href' => '/', 'active' => $simpleview],
        ['text' => 'Detailed View', 'href' => '/?detailedview', 'active' => $detailedview],
        ['text' => 'Map View', 'href' => '/?mapview', 'active' => $mapview],
        ['text' => 'Network Stats View', 'href' => '/?statsview', 'active' => $statsview],
        ['text' => 'Add a pod', 'href' => '/?podmin', 'active' => $podmin],
        ['text' => 'Edit a pod', 'href' => '/?podminedit', 'active' => $podminedit],
    ],
    'links' => [
        ['text' => 'Github', 'href' => 'https://github.com/diasporg/Poduptime', 'active' => false],
        ['text' => 'Contact', 'href' => 'https://dia.so/support', 'active' => false],
        ['text' => 'Wiki', 'href' => 'https://github.com/diasporg/Poduptime/wiki', 'active' => false],
        ['text' => 'API', 'href' => 'https://github.com/diasporg/Poduptime/wiki/API', 'active' => false],
        ['text' => 'How to host a pod', 'href' => 'https://diasporafoundation.org/', 'active' => false],
    ],
];
?>

<header>
    <div class="collapse bg-dark" id="navbarHeader">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-md-7 py-4">
                    <h4 class="text-white">About</h4>
                    <p class="text-muted">Poduptime helps you find a diaspora, friendica, hubzilla or socialhome pod to use and join the federated social network.</p>
                    <?php
                    foreach ($navs['links'] as $nav_item) {
                        printf(
                            '<a href="%2$s">%3$s%4$s</a> | ',
                            $nav_item['active'] ? ' active' : '',
                            $nav_item['href'],
                            $nav_item['text'],
                            $nav_item['active'] ? ' <span class="sr-only">(current)</span>' : ''
                        );
                    }
                    ?>
                </div>
                <div class="col-sm-4 offset-md-1 py-4">
                    <h4 class="text-white">Views</h4>
                    <ul class="navbar-nav">
                        <?php
                        foreach ($navs['views'] as $nav_item) {
                            printf(
                                '<li class="nav-item"><a class="nav-link%1$s" href="%2$s">%3$s%4$s</a></li>',
                                $nav_item['active'] ? ' active' : '',
                                $nav_item['href'],
                                $nav_item['text'],
                                $nav_item['active'] ? ' <span class="sr-only">(current)</span>' : ''
                            );
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="navbar navbar-dark bg-dark box-shadow">
        <div class="container d-flex justify-content-between">
            <a href="/" class="navbar-brand d-flex align-items-center">
                <strong>Poduptime</strong>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</header>
<main role="main">
    <div class="main">
        <a href="go.php" class="m-1 btn btn-sm btn-info">Confused? Auto pick a pod for you.</a>
        <div class="row">
        </div>
        <?php
        if ($detailedview) {
            include_once __DIR__ . '/showfull.php';
        } elseif ($mapview) {
            include_once __DIR__ . '/showmap.php';
        } elseif ($statsview) {
            include_once __DIR__ . '/statsview.php';
        } elseif ($podmin) {
            include_once __DIR__ . '/podmin.php';
        } elseif ($podminedit) {
            include_once __DIR__ . '/podminedit.php';
        } elseif ($edit) {
            include_once __DIR__ . '/db/edit.php';
        } elseif ($add) {
            include_once __DIR__ . '/db/add.php';
        } elseif ($gettoken) {
            include_once __DIR__ . '/db/gettoken.php';
        } else {
            include_once __DIR__ . '/show.php';
        }
        ?>
    </div>
</main>
<footer class="ml-2 text-muted">
    <small>Data refreshed <?php echo Carbon::createFromTimestamp(filemtime($lastfile))->diffForHumans(); ?></small>
</footer>
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<script src="node_modules/tablesorter/dist/js/jquery.tablesorter.combined.min.js"></script>
<script src="node_modules/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
<script src="js/podup.js"></script>
<script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="node_modules/popper.js/dist/popper.js"></script>
<script src="node_modules/featherlight/release/featherlight.min.js"></script>
<script src="node_modules/chart.js/dist/Chart.min.js"></script>
<?php $statsview && include_once __DIR__ . '/statsviewjs.php'; ?>
</body>
</html>
