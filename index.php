<?php
$lastfile     = 'db/last.data';
$detailedview = isset($_GET['detailedview']);
$mapview      = isset($_GET['mapview']);
$cleanup      = isset($_GET['cleanup']);
$statsview    = isset($_GET['statsview']);
$podmin       = isset($_GET['podmin']);
$podminedit   = isset($_GET['podminedit']);
$simpleview   = !($detailedview || $mapview || $cleanup || $podmin || $podminedit || $statsview);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Open Source Social Network Pod Uptime Status</title>
  <meta name="keywords" content="diaspora, federated pods, <?php echo $_SERVER['HTTP_HOST'] ?>, friendica, hubzilla, open source social, open source social network"/>
  <meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!"/>
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/newstyle.css"/>
  <link rel="stylesheet" href="bower_components/facebox/src/facebox.css"/>
  <link rel="stylesheet" href="css/dashboard.css"/>
  <link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.min.css"/>
  <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST'] ?>"/>
  <meta property="og:title" content="Social Network Pod Finder"/>
  <meta property="og:type" content="website"/>
  <meta property="og:description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
</head>
<body>
<nav class="navbar navbar-dark navbar-fixed-top bg-primary">
  <button type="button" class="navbar-toggler hidden-sm-up" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" aria-label="Toggle navigation"></button>
  <a class="navbar-brand" href="#">Poduptime</a>
  <div id="navbar" class="text-muted collapse">
    <nav class="nav navbar-nav float-xs-left">
      <a class="nav-item nav-link<?php $simpleview && print ' active'; ?>" href="/">Simple View</a>
      <a class="nav-item nav-link<?php $detailedview && print ' active'; ?>" href="/?detailedview">Detailed View</a>
      <a class="nav-item nav-link<?php $mapview && print ' active'; ?>" href="/?mapview">Map View</a>
      <a class="nav-item nav-link<?php $statsview && print ' active'; ?>" href="/?statsview">Network Stats</a>
    </nav>
  </div>
</nav>
<div class="container-fluid">
  <div class="row">
    <div class="sidebar col-md-3">
      <div class="list-group">
        <a class="list-group-item<?php $simpleview && print ' active'; ?>" href="/">Simple View<?php $simpleview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a>
        <a class="list-group-item<?php $detailedview && print ' active'; ?>" href="/?detailedview">Detailed View<?php $detailedview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a>
        <a class="list-group-item<?php $mapview && print ' active'; ?>" href="/?mapview">Map View<?php $mapview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a>
        <a class="list-group-item<?php $statsview && print ' active'; ?>" href="/?statsview">Network Stats<?php $statsview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a>
      </div>
      <div class="list-group">
        <a class="list-group-item<?php $podmin && print ' active'; ?>" href="/?podmin">Add a pod<?php $podmin && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a>
        <a class="list-group-item<?php $podminedit && print ' active'; ?>" href="/?podminedit">Edit a pod<?php $podminedit && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a>
        <a class="list-group-item" href="https://diasporafoundation.org/">Host a pod</a>
      </div>
      <div class="list-group">
        <a class="list-group-item" href="https://github.com/diasporg/Poduptime">Github</a>
        <a class="list-group-item" href="https://dia.so/support">Contact</a>
        <a class="list-group-item" href="https://github.com/diasporg/Poduptime/wiki">Wiki</a>
        <a class="list-group-item" href="https://github.com/diasporg/Poduptime/wiki/API">API</a>
      </div>
      <p><small>Data refreshed: <br><?php echo date('M d y H:i', filemtime($lastfile)); ?> EST</small></p><br>
      <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
      <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-3662181805557062" data-ad-slot="2195215834" data-ad-format="auto"></ins>
      <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
      </script>
    </div>
    <div class="main offset-md-3">
      <a href="go.php" class="btn btn-sm btn-success">Confused? Auto pick a pod for you.</a>
      <div class="row placeholders">
      </div>
      <div class="table-responsive">
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
        } elseif ($cleanup) {
          include_once __DIR__ . '/cleanup.php';
        } else {
          include_once __DIR__ . '/show.php';
        }
        ?>
      </div>
    </div>
  </div>
</div>
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/tether/dist/js/tether.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/podup.js"></script>
<script src="bower_components/facebox/src/facebox.js"></script>
<script src="bower_components/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
<script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
<script src="bower_components/chart.js/dist/Chart.min.js"></script>
<?php $statsview && include_once __DIR__ . '/statsviewjs.php'; ?>
</body>
</html>
