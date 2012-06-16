<!doctype html><html><head><meta charset="utf-8"><title>Diaspora Pod uptime - Find your new social home</title>
<meta name="keywords" content="diaspora, podupti.me, diasp, diasporg, diasp.org, facebook, open source social, open source facebook, open source social network" />
<meta name="description" content="Diaspora Pod Live Status. Find a Diaspora pod to sign up for, rate pods, find one close to you!" />
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.min.js"></script> 
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.tablesorter.min.js"></script> 
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.loading.1.6.4.min.js"></script> 
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery.tipsy.js"></script>
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/podup.js"></script>
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/facebox.js"></script>
<script type="text/javascript" src="http://c807316.r16.cf2.rackcdn.com/jquery-ui-1.8.21.custom.min.js"></script>
<script src="OpenLayers.js"></script>
<link href="http://c807316.r16.cf2.rackcdn.com/jquery-ui-1.8.21.custom.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="http://c790230.r16.cf2.rackcdn.com/bootstrap.min.css">
<link rel="stylesheet" href="css/newstyle.css" />
<link rel="stylesheet" href="http://c807316.r16.cf2.rackcdn.com/facebox.css" />

<?php include("vendor/Mobile_Detect.php");$detect = new Mobile_Detect();if ($detect->isMobile()) {echo '<link rel="stylesheet" href="http://c807316.r16.cf2.rackcdn.com/mobile.css" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';} ?>
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
<!-- Begin: adBrite, Generated: 2012-04-29 16:22:25  -->
<script type="text/javascript">
var AdBrite_Title_Color = '46A546';
var AdBrite_Text_Color = '000000';
var AdBrite_Background_Color = 'FFFFFF';
var AdBrite_Border_Color = 'dddddd';
var AdBrite_URL_Color = '46A546';
try{var AdBrite_Iframe=window.top!=window.self?2:1;var AdBrite_Referrer=document.referrer==''?document.location:document.referrer;AdBrite_Referrer=encodeURIComponent(AdBrite_Referrer);}catch(e){var AdBrite_Iframe='';var AdBrite_Referrer='';}
</script>
<script type="text/javascript">document.write(String.fromCharCode(60,83,67,82,73,80,84));document.write(' src="http://ads.adbrite.com/mb/text_group.php?sid=2143050&zs=3330305f323530&ifr='+AdBrite_Iframe+'&ref='+AdBrite_Referrer+'" type="text/javascript">');document.write(String.fromCharCode(60,47,83,67,82,73,80,84,62));</script>
<!-- End: adBrite -->

      </div>
      <a href="https://market.android.com/details?id=appinventor.ai_david_morley.DiasporaPoduptime"><img src="http://c807316.r16.cf2.rackcdn.com/android-dude128.png"></a>
    </div>
    <div class="content">
    <div id="map" style="width:80%;height:500px;position:absolute;display:none"></div>
      <div id="results">
        <?php include("show.php"); ?>
      </div>
      <div id="add">
        Pod Host? <u style="cursor: pointer; cursor: hand;">Click here</u> to add/manage your listing.<br>
        Poduptime is run by <a href="https://diasp.org/u/davidmorley" target=_new>David Morley</a> and is open source on <a href="https://github.com/diasporg/Poduptime">GitHub</a><br>
        I also run the pods <a href="https://diasp.org/?2" target=_new>diasp.org</a> and <a href="https://usr.io/?2" target=_new>usr.io</a> and the <a href="http://dia.so" target=_new>dia.so</a> site.<br>
        Some pods are <a href="http://podupti.me/?hidden=true">Hidden</a> That have too many issues.<br><br>
      </div>
      <div id="howto" style="display:none; margin-left:50px">
        <br>
        Want your pod listed?<br>
        Its easy start monitoring on your pod with a free <a href="http://www.pingdom.com" target="new">www.pingdom.com</a> account.<br>
        <br>Make a public report public and then enter your URL below (note its the one you view after you goto it)<br><br>
        Pingdom shows me http://stats.pingdom.com/b4gasnh1c176 when I click it to goes to http://stats.pingdom.com/b4gasnh1c176/240588
        <br> So URL should look very close to this: http://stats.pingdom.com/b4gasnh1c176/240588<br>
        <br><form action="db/add.php" method="post">
        Stats URL:<input type="text" name="url" class="xlarge span8" placeholder="http://stats.pingdom.com/b4gasnh1c176/240588"><br>
        Pod domainname:<input type="text" name="domain" class="xlarge span4" placeholder="domain.com"><br>
        Your Email:<input type="text" name="email" class="xlarge span4" placeholder="user@domain.com"><br>
        <input type="submit" value="submit">
        </form>
        <br>Is your pod missing? If the server can not get a diaspora session its on the hidden list <a href="http://podupti.me/?hidden=true">Show</a>. This
is mostly because of selfsigned or openca certs, if you need a free ssl cert get one from startssl.com.
        <br>Need help? <a href="https://diasporg.zendesk.com">Support</a>
        <br>
      </div>
				
				
<!-- Start Open Web Analytics Tracker -->
<script type="text/javascript">
//<![CDATA[
var owa_baseUrl = 'http://podupti.me/owa/';
var owa_cmds = owa_cmds || [];
owa_cmds.push(['setSiteId', '516d23035f26941cd716f6817aceb9d3']);
owa_cmds.push(['trackPageView']);
owa_cmds.push(['trackClicks']);
owa_cmds.push(['trackDomStream']);

(function() {
	var _owa = document.createElement('script'); _owa.type = 'text/javascript'; _owa.async = true;
	owa_baseUrl = ('https:' == document.location.protocol ? window.owa_baseSecUrl || owa_baseUrl.replace(/http:/, 'https:') : owa_baseUrl );
	_owa.src = owa_baseUrl + 'modules/base/js/owa.tracker-combined-min.js';
	var _owa_s = document.getElementsByTagName('script')[0]; _owa_s.parentNode.insertBefore(_owa, _owa_s);
}());
//]]>
</script>
<!-- End Open Web Analytics Code -->
						
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
