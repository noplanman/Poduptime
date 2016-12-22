<?php
$hidden       = isset($_GET['hidden']) ? $_GET['hidden'] : null;
$lastfile     = 'db/last.data';
$advancedview = isset($_GET['advancedview']);
$mapview      = isset($_GET['mapview']);
$cleanup      = isset($_GET['cleanup']);
$podmin       = isset($_GET['podmin']);
$podminedit   = isset($_GET['podminedit']);
$simpleview   = !($advancedview || $mapview || $cleanup || $podmin || $podminedit);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Diaspora Pod Uptime Status - Find your new social home on a hosted pod</title>
  <meta name="keywords" content="diaspora, federated pods, podupti.me, open source social, open source social network"/>
  <meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!"/>
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/newstyle.css"/>
  <link rel="stylesheet" href="bower_components/facebox/src/facebox.css"/>
  <link rel="stylesheet" href="css/dashboard.css"/>
  <link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.min.css"/>
  <meta property="og:url" content="https://podupti.me"/>
  <meta property="og:title" content="Diaspora Pod Finder"/>
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
      <a class="nav-item nav-link<?php $advancedview && print ' active'; ?>" href="/?advancedview">Advanced View</a>
      <a class="nav-item nav-link<?php $mapview && print ' active'; ?>" href="/?mapview">Map View</a>
    </nav>
  </div>
</nav>
<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li<?php $simpleview && print ' class="active"'; ?>><a href="/">Simple View<?php $simpleview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a></li>
        <li<?php $advancedview && print ' class="active"'; ?>><a href="/?advancedview">Advanced View<?php $advancedview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a></li>
        <li<?php $mapview && print ' class="active"'; ?>><a href="/?mapview">Map View<?php $mapview && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a></li>
      </ul>
      <ul class="nav nav-sidebar">
        <li<?php $podmin && print ' class="active"'; ?>><a href="/?podmin">Add a pod<?php $podmin && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a></li>
        <li<?php $podminedit && print ' class="active"'; ?>><a href="/?podminedit">Edit a pod<?php $podminedit && print ' <span class="sr-only bg-dark">(current)</span>'; ?></a></li>
        <li><a href="https://diasporafoundation.org/">Host a pod</a></li>
      </ul>
      <ul class="nav nav-sidebar">
        <li><a href="https://github.com/diasporg/Poduptime">Github</a></li>
        <li><a href="https://dia.so/support">Contact</a></li>
        <li><a href="https://github.com/diasporg/Poduptime/wiki">Wiki</a></li>
        <li><a href="https://github.com/diasporg/Poduptime/wiki/API">API</a></li>
      </ul>
      <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
      <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-3662181805557062" data-ad-slot="2195215834" data-ad-format="auto"></ins>
      <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
      </script>
      <br>
      Data last refreshed: <br><?php echo date('F d Y H:i:s.', filemtime($lastfile)); ?> EST
    </div>
    <div class="main col-md-10 offset-md-2">
      <a href="random.php" class="btn btn-sm btn-success">Confused? Auto pick a pod for you.</a>
      <div class="row placeholders">
      </div>
      <div class="table-responsive">
        <?php
        if ($advancedview) {
          include_once __DIR__ . '/showfull.php';
        } elseif ($mapview) {
          include_once __DIR__ . '/showmap.php';
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
</body>
</html>
