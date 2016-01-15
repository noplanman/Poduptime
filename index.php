<!doctype html><html><head><meta charset="utf-8"><title>Federated Pod Uptime Status - Find your new social home on a hosted pod</title>
<meta name="keywords" content="diaspora, podupti.me, diasp, diasporg, diasp.org, facebook, open source social, open source facebook, open source social network" />
<meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!" />
<script type="text/javascript" src="js/jquery-1.6.4.min.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script> 
<script type="text/javascript" src="js/jquery.loading.1.6.4.min.js"></script> 
<script type="text/javascript" src="js/jquery.tipsy.js"></script>
<script type="text/javascript" src="js/podup.js"></script>
<script type="text/javascript" src="js/facebox.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
<link href="css/jquery-ui-1.8.21.custom.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/newstyle.css" />
<link rel="stylesheet" href="css/facebox.css" />
<meta property="og:url" content="http://podupti.me" />
<meta property="og:site_name" content="Diaspora Pods" />
<?php 
$hidden = isset($_GET['hidden'])?$_GET['hidden']:null;
$lastfile = 'db/last.data';
$advancedview = isset($_GET['advancedview'])?$_GET['advancedview']:null;
$mapview = isset($_GET['mapview'])?$_GET['mapview']:null;
$cleanup = isset($_GET['cleanup'])?$_GET['cleanup']:null;
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<script type="text/javascript">
/* <![CDATA[ */
    (function() {
        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
        s.type = 'text/javascript';
        s.async = true;
        s.src = '//api.flattr.com/js/0.6/load.js?mode=auto';
        t.parentNode.insertBefore(s, t);
    })();
/* ]]> */</script>
<script type="text/javascript" src="//ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "3209f0be-147e-49fc-ac1b-2cf6740e9449", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
</head>
<body>
  <header>
    <div class="page-header">
      <div class="row">
        <div class="span5">
          <h2 id="title">
          Federated Social Pods
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
</div>

    </div>
  </div>
</div>
  </header>
  <div class="container-fluid">
    <div class="content">
<?php
if ($advancedview) {
echo <<<EOF
      <div id="adadv">
      <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- poduptimenew -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:250px"
     data-ad-client="ca-pub-3662181805557062"
     data-ad-slot="3969028081"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
      </div>
EOF;
} elseif ($cleanup) {echo "";
} else {
echo <<<EOF
      <div id="ad">
      <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- poduptimenew -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:250px"
     data-ad-client="ca-pub-3662181805557062"
     data-ad-slot="3969028081"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
      </div>
EOF;
}
?>
      <div id="results">
        <?php 
	if ($advancedview) {
	echo "<a href='http://podupti.me' class='btn danger large'>NOTICE: This view shows all pods in all states, some offline, click here to go to list of pods open for signup</a><br>";
	include("showfull.php");
	} elseif ($mapview) {
	include("showmap.php");
        } elseif ($cleanup) {
        include("cleanup.php");
        } else {
	echo "<a href='random.php' class='btn danger large'>Confused and just want to sign up?? Click Here and we will pick one for you</a><br>";
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
        Questions on how this works? <a href="https://github.com/diasporg/Poduptime/wiki">Wiki</a><br>
	<a href="https://diasporafoundation.org/">More about Diaspora</a><br><a href="http://friendica.com/">More about Friendica</a><br><a href="http://hubzilla.org/">More about Hubzilla(redmatrix)</a>
	</div>
      <div id="howto" style="display:none; margin-left:50px">
        <br>
        Want your pod listed?<br>
        Its easy start monitoring on your pod with a free <a href="https://uptimerobot.com" target="new">Uptime Robot</a> account.<br>
	Create a monitor for your pod, then in "My Settings" create a monitor-specific API key and paste below.<br>
        <br><form action="https://podupti.me/db/add.php" method="post">
        Monitor API Key:<input type="text" name="url" class="xlarge span8" placeholder="m58978-80abdb799f6ccf15e3e4ttwe"> (don't copy the period on the end)<br>
        Pod domainname:<input type="text" name="domain" class="xlarge span4" placeholder="domain.com"><br>
        Your Email:<input type="text" name="email" class="xlarge span4" placeholder="user@domain.com"><br>
        <input type="submit" value="submit">
        </form>
	Need to edit something?<br>
	<form action="https://podupti.me/db/gettoken.php" method="post">Pod Domainname:<input type="text" name="domain">Registered Email:<input type="text" name="email" placeholder="Ok to leave blank if you forgot"><input type=submit value="send"></form>
        <br>Is your pod missing? If the server can not get a diaspora session its on the hidden list <a href="http://podupti.me/?hidden=true">Show</a>. This
is mostly because of selfsigned or openca certs, if you need a free ssl cert get one from startssl.com.
        <br>
      </div>
				
    </div>
  </div>
</body>
</html>
