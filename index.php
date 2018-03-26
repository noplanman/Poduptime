<?php

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
$cleanup      = isset($_GET['cleanup']);
$statsview    = isset($_GET['statsview']);
$podmin       = isset($_GET['podmin']);
$podminedit   = isset($_GET['podminedit']);
$edit         = isset($_GET['edit']);
$simpleview   = !($detailedview || $mapview || $cleanup || $podmin || $podminedit || $statsview);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Open Source Social Network Pod Uptime Status</title>
  <meta name="keywords" content="diaspora, federated pods, <?php echo $_SERVER['HTTP_HOST'] ?>, friendica, hubzilla, open source social, open source social network"/>
  <meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!"/>
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/poduptime.css"/>
  <link rel="stylesheet" href="bower_components/facebox/src/facebox.css"/>
  <link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.min.css"/>
  <link rel="stylesheet" href="bower_components/tablesorter/dist/css/theme.bootstrap_4.min.css"/>
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
  'views'  => [
    ['text' => 'Simple View', 'href' => '/', 'active' => $simpleview],
    ['text' => 'Detailed View', 'href' => '/?detailedview', 'active' => $detailedview],
    ['text' => 'Map View', 'href' => '/?mapview', 'active' => $mapview],
    ['text' => 'Network Stats', 'href' => '/?statsview', 'active' => $statsview],
  ],
  'podmin' => [
    ['text' => 'Add a pod', 'href' => '/?podmin', 'active' => $podmin],
    ['text' => 'Edit a pod', 'href' => '/?podminedit', 'active' => $podminedit],
    ['text' => 'Host a pod', 'href' => 'https://diasporafoundation.org/', 'active' => false],
  ],
  'links'  => [
    ['text' => 'Github', 'href' => 'https://github.com/diasporg/Poduptime', 'active' => false],
    ['text' => 'Contact', 'href' => 'https://dia.so/support', 'active' => false],
    ['text' => 'Wiki', 'href' => 'https://github.com/diasporg/Poduptime/wiki', 'active' => false],
    ['text' => 'API', 'href' => 'https://github.com/diasporg/Poduptime/wiki/API', 'active' => false],
  ],
];
?>

<nav class="navbar navbar-inverse bg-primary fixed-top">
  <button class="navbar-toggler navbar-toggler-right hidden-md-up" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <a class="navbar-brand" href="/">Poduptime</a>
  <div class="collapse navbar-toggleable hidden-md-up" id="navbar">
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
</nav>
<div class="container-fluid">
  <div class="row">
    <div class="sidebar col-md-3 col-lg-2 hidden-sm-down">

      <?php foreach ($navs as $nav) : ?>
        <ul class="nav nav-pills flex-column">
          <?php
          /** @var array $nav */
          /** @var array $nav_item */
          foreach ($nav as $nav_item) {
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
        <hr>
      <?php endforeach; ?>

      <p>
        <small>Data refreshed: <br><?php echo date('M d y H:i', filemtime($lastfile)); ?> EST</small>
      </p>
    </div>
    <div class="main col-md-9 col-lg-10 offset-md-3 offset-lg-2">
      <a href="go.php" class="btn btn-sm btn-success">Confused? Auto pick a pod for you.</a>
      <div class="row placeholders">
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
        } elseif ($cleanup) {
          include_once __DIR__ . '/cleanup.php';
        } else {
          include_once __DIR__ . '/show.php';
        }
        ?>
    </div>
  </div>
</div>
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
<script src="js/podup.js"></script>
<script src="bower_components/tether/dist/js/tether.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="bower_components/facebox/src/facebox.js"></script>
<script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
<script src="bower_components/chart.js/dist/Chart.min.js"></script>
<?php $statsview && include_once __DIR__ . '/statsviewjs.php'; ?>
</body>
</html>
