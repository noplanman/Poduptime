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
<meta property="og:url" content="http://podupti.me" />
<meta property="og:site_name" content="Diaspora Pods" />
<?php 
$hidden = isset($_GET['hidden'])?$_GET['hidden']:null;
$lastfile = 'db/last.data';
include("vendor/mobiledetect/Mobile_Detect.php");
$detect = new Mobile_Detect();
if ($detect->isMobile()) {echo '<link rel="stylesheet" href="css/mobile.css" />';} 
?>
<script type="text/javascript">
/* <![CDATA[ */
    (function() {
        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
        s.type = 'text/javascript';
        s.async = true;
        s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
        t.parentNode.insertBefore(s, t);
    })();
/* ]]> */</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "3209f0be-147e-49fc-ac1b-2cf6740e9449", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
</head>
<body>
  <header>
    <div class="page-header">
      <div class="row">
        <div class="span5">
          <h2 id="title">
          Diaspora Pods
          </h2>
        </div>
      <div class="span2" style="margin-top:8px;">
<span class='st_facebook_hcount' displayText='Facebook' st_summary='test'></span>
      </div>
<div class="span2" style="margin-top:8px;">

<span class='st_twitter_hcount' displayText='Tweet' text='test'></span>
      </div>
<div class="span2" style="margin-top:8px;">

<span class='st_plusone_hcount' displayText='Google +1'></span>
      </div>
      <div class="span2" style="margin-top:8px;">
<a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://podupti.me"></a>
	</div>
	<div class="span2" style="margin-top:8px;">
<div class="cb-tip-button" data-content-location="http://podupti.me" data-href="//www.coinbase.com/tip_buttons/show_tip" data-to-user-id="528d8ff6f8f028e269000067"></div>
<script>!function(d,s,id) {var js,cjs=d.getElementsByTagName(s)[0],e=d.getElementById(id);if(e){return;}js=d.createElement(s);js.id=id;js.src="https://www.coinbase.com/assets/tips.js";cjs.parentNode.insertBefore(js,cjs);}(document, 'script', 'coinbase-tips');</script>
</div>

    </div>
  </div>
</div>
  </header>
  <div class="container-fluid">
    <div class="content">
      <div id="results">
	<a href='random.php' class='btn danger large'>Confused and just want to sign up?? Click Here</a><br>
        <?php 
	if ($hidden == "true") {echo "<a href='http://podupti.me' class='btn danger large'>NOTICE: These pods are Hidden and have problems, click here to go to working pods</a>";} 
        $advancedview = isset($_GET['advancedview'])?$_GET['advancedview']:null;
	$mapview = isset($_GET['mapview'])?$_GET['mapview']:null;
	if ($advancedview) {
	include("showfull.php");
	} elseif ($mapview) {
	include("showmap.php");
        } else {
        include("show.php");
	} 
	?>
      </div>
      <div id="add">
        Pod Host? <u style="cursor: pointer; cursor: hand;">Click here</u> to add/edit your listing.<br>
	</div>
	<div id="info">
	Data last refreshed at: <?php echo date("F d Y H:i:s.", filemtime($lastfile)) ?> Pacific Time<br>
        Poduptime is run by <a href="https://diasp.org/u/davidmorley" target=_new>David Morley</a> and is open source on <a href="https://github.com/diasporg/Poduptime">GitHub</a> Feel free to contribute with pull requests or bug reports!<br>
        Some pods are <a href="http://podupti.me/?hidden=true">Hidden</a> since they have too many issues, see the <a href="https://github.com/diasporg/Poduptime/wiki">Wiki</a> for more.<br><br>
	<a href="https://diasporafoundation.org/">More about Diaspora</a>
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
	Need to edit somehing?<br>
	<form action="db/gettoken.php" method="post">Pod Domainname:<input type="text" name="domain">Registered Email:<input type="text" name="email"><input type=submit value="send"></form>
        <br>Is your pod missing? If the server can not get a diaspora session its on the hidden list <a href="http://podupti.me/?hidden=true">Show</a>. This
is mostly because of selfsigned or openca certs, if you need a free ssl cert get one from startssl.com.
        <br>
      </div>
				
    </div>
  </div>
</body>
</html>
