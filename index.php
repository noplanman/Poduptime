<!doctype html><html><head><meta charset="utf-8"><title>Diaspora Pod Uptime Status - Find your new social home on a hosted pod</title>
<meta name="keywords" content="diaspora, podupti.me, diasp, diasporg, diasp.org, facebook, open source social, open source facebook, open source social network" />
<meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!" />
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.min.js"></script> 
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.tablesorter.min.js"></script> 
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.loading.1.6.4.min.js"></script> 
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.tipsy.js"></script>
<script type="text/javascript" src="js/podup.js"></script>
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/facebox.js"></script>
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery-ui-1.8.21.custom.min.js"></script>
<script src="OpenLayers.js"></script>
<link href="http://c807316.r16.cf2.rackcdn.com/jquery-ui-1.8.21.custom.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/newstyle.css" />
<link rel="stylesheet" href="css/facebox.css" />

<?php 
$hidden = isset($_GET['hidden'])?$_GET['hidden']:null;
$lastfile = 'db/last.data';
include("vendor/mobiledetect/Mobile_Detect.php");
$detect = new Mobile_Detect();
if ($detect->isMobile()) {echo '<link rel="stylesheet" href="css/mobile.css" />';} 
?>
</head>
<body>
  <header>
    <div class="page-header">
      <div class="row">
        <div class="span5">
          <h2 id="title">
          Diaspora Hosted Pods
          </h2>
        </div>
      <div class="span" style="margin-top:8px;">
      </div>
      <div class="span2" style="margin-top:8px;">
<a href="http://flattr.com/thing/170048/Diaspora-Pod-Live-Uptime-watch" target="_blank"><img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
      </div>
      <div class="span2" style="margin-top:8px;">
<a onClick="map();">Map View</a>
      </div>
      <div class="span2" style="margin-top:8px;">
<a onClick="nomap();">Table View</a>
      </div>
      <div class="span2" style="margin-top:8px;">
<a href="https://diasporafoundation.org/">More Info</a>
      </div>

    </div>
  </div>
</div>
  </header>
  <div class="container-fluid">
    <div class="content">
    <div id="map" style="width:80%;height:500px;position:absolute;display:none"></div>
      <div id="results">
        <?php if ($hidden == "true") {echo "<a href='http://podupti.me' class='btn danger large'>NOTICE: These pods are Hidden and have problems, click here to go to working pods</a>";} include("show.php"); ?>
      </div>
      <div id="add">
        Pod Host? <u style="cursor: pointer; cursor: hand;">Click here</u> to add your listing.<br>
	</div>
	<div id="info">
	Data last refreshed at: <?php echo date("F d Y H:i:s.", filemtime($lastfile)) ?> Pacific Time<br>
        Poduptime is run by <a href="https://diasp.org/u/davidmorley" target=_new>David Morley</a> and is open source on <a href="https://github.com/diasporg/Poduptime">GitHub</a> Feel free to contribute with pull requests or bug reports!<br>
        Some pods are <a href="http://podupti.me/?hidden=true">Hidden</a> since they have too many issues, see the <a href="https://github.com/diasporg/Poduptime/wiki">Wiki</a> for more.<br><br>
	</div>
      <div id="howto" style="display:none; margin-left:50px">
        <br>
        Want your pod listed?<br>
        Its easy start monitoring on your pod with a free <a href="https://uptimerobot.com" target="new">Uptime Robot</a> account.<br>
	Create a monitor for your pod, then in "My Settings" create a monitor-specific API key and paste below.<br>
        <br><form action="db/add.php" method="post">
        Monitor API Key:<input type="text" name="url" class="xlarge span8" placeholder="m58978-80abdb799f6ccf15e3e4ttwe"> (don't copy the period on the end)<br>
        Pod domainname:<input type="text" name="domain" class="xlarge span4" placeholder="domain.com"><br>
        Your Email:<input type="text" name="email" class="xlarge span4" placeholder="user@domain.com"><br>
        <input type="submit" value="submit">
        </form>
        <br>Is your pod missing? If the server can not get a diaspora session its on the hidden list <a href="http://podupti.me/?hidden=true">Show</a>. This
is mostly because of selfsigned or openca certs, if you need a free ssl cert get one from startssl.com.
        <br>
      </div>
				
    </div>
  </div>
</body>
</html>
