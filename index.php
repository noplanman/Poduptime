<!doctype html><html><head><meta charset="utf-8"><title>Diaspora Pod uptime - Find your new social home</title>
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
if ($detect->isMobile()) {echo '<link rel="stylesheet" href="http://c807316.r16.cf2.rackcdn.com/mobile.css" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';} 
?>
<script type="text/javascript">
(function() {
var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
s.type = 'text/javascript';
s.async = true;
s.src = 'http://widgets.digg.com/buttons.js';
s1.parentNode.insertBefore(s, s1);
})();
</script></head>
<body>
  <header>
    <div class="page-header">
      <div class="row">
        <div class="span6">
          <h2 id="title">
          DIASPORA* POD UPTIME
          </h2>
        </div>
      <div class="span3" style="margin-top:8px;">
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style ">
<a class="addthis_button_preferred_1"></a>
<a class="addthis_button_preferred_2"></a>
<a class="addthis_button_preferred_3"></a>
<a class="addthis_button_preferred_4"></a>
<a class="addthis_button_compact"></a>
<a class="addthis_counter addthis_bubble_style"></a>
</div>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=davidmmorley"></script>
<!-- AddThis Button END -->
      </div>
      <div class="span4" style="margin-top:8px;">
      <a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://podupti.me"></a>
      </div>
      <div class="span2" style="margin-top:8px;">
<a onClick="map();">Show Map View</a>
      </div>
      <div class="span2" style="margin-top:8px;">
<a onClick="nomap();">Show Table View</a>
      </div>
    </div>
  </div>
</div>
  </header>
  <div class="container-fluid">
    <div class="sidebar"> 
      <div class="adsense2">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- podsky -->
<ins class="adsbygoogle"
     style="display:inline-block;width:120px;height:600px"
     data-ad-client="ca-pub-3662181805557062"
     data-ad-slot="2647650630"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
      </div>
      <a href="https://market.android.com/details?id=appinventor.ai_david_morley.DiasporaPoduptime"><img src="http://c807316.r16.cf2.rackcdn.com/android-dude128.png"></a>
    </div>
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
        Monitor API Key:<input type="text" name="url" class="xlarge span8" placeholder="m58978-80abdb799f6ccf15e3e4ttwe"><br>
        Pod domainname:<input type="text" name="domain" class="xlarge span4" placeholder="domain.com"><br>
        Your Email:<input type="text" name="email" class="xlarge span4" placeholder="user@domain.com"><br>
        <input type="submit" value="submit">
        </form>
        <br>Is your pod missing? If the server can not get a diaspora session its on the hidden list <a href="http://podupti.me/?hidden=true">Show</a>. This
is mostly because of selfsigned or openca certs, if you need a free ssl cert get one from startssl.com.
        <br>
      </div>
				
<!-- Piwik -->
<script type="text/javascript"> 
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://podupti.me/s//";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();

</script>
<noscript><p><img src="http://podupti.me/s/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Code -->

						
      <script type="text/javascript">
      /* <![CDATA[ */
          (function() {
              var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
              s.type = 'text/javascript';
              s.async = true;
              s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
              t.parentNode.insertBefore(s, t);
          })();
      /* ]]> */
      </script>
    </div>
  </div>
</body>
</html>
