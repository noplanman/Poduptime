<?php
$tt=0;
include('db/config.php');
$country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
if (!$dbh) {
  die("Error in connection: " . pg_last_error());
}  
$hidden = isset($_GET['hidden'])?$_GET['hidden']:null;
if ($hidden == "true") {
  $sql = "SELECT * FROM pods WHERE hidden <> 'no' ORDER BY weightedscore DESC";
} else {
  $sql = "SELECT * FROM pods WHERE adminrating <> -1 AND hidden <> 'yes' AND signup = 1 ORDER BY weightedscore DESC";
}
$result = pg_query($dbh, $sql);
if (!$result) {
  die("Error in SQL query: " . pg_last_error());
}   
$numrows = pg_num_rows($result);
echo "<meta property='og:title' content='"; 
echo $numrows;
echo " Federated Pods listed, Come see the privacy aware social networks.' />";
echo $numrows;
?>
 pods that are open for signup now.  
Click column names to sort and find a pod.  
Show as: <a href="?mapview=true">Map</a> | <a href="/">Simple Table</a> | <a href="?advancedview=true">Advanced Table</a>
<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="tablesorter zebra-striped" style="width:750px; !important">
<thead>
<tr>
<th width="220px">Federated Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
<th>Uptime %<a class="tipsy" title="Percent of the time the pod is online for <?php echo date("F") ?>.">?</a></th>
<th>Active Users<a class="tipsy" title="Number of users active last 6 months on this pod.">?</a></th>
<th>Location<a class="tipsy" title="Pod location, based on IP Geolocation">?</a></th>
<th>Services Offered<a class="tipsy" title="External Social Networks this pod can post to">?</a></th>
</tr>
</thead>
<tbody>
<?php
 while ($row = pg_fetch_array($result)) {
$tt=$tt+1;
if ($row["secure"] == "true") {
$method = "https://";
$class="green";
$tip="This pod uses SSL encryption for traffic.";} 

else {
$method = "http://";
$class="red";
$tip="This pod does not offer SSL";
} 
$verdiff =  str_replace(".", "", $row["masterversion"]) - str_replace('.', '', $row["shortversion"]);
$pod_name = htmlentities($row["name"], ENT_QUOTES);
$tip.="\n This {$row["softwarename"]} pod {$pod_name} has been watched for {$row["monthsmonitored"]} months and with an uptime of {$row["uptimelast7"]}% this month and was last checked on {$row["dateupdated"]}. ";
$tip.="On a scale of 100 this pod is a {$row["score"]} right now";
     echo "<tr><td><a class='$class' target='new' href='". $method . $row["domain"] ."'>" . $row["domain"] . "</a> <div title='$tip' class='tipsy morehover'> ?</div></td>";
"</div></td>";

     echo "<td>" . $row["uptimelast7"] . "%</td>";
     echo "<td class='tipsy' title='active six months: "  . $row["active_users_halfyear"] .  ", active one month: "  . $row["active_users_monthly"] . "'>" . $row["active_users_halfyear"] . "</td>";
	if ($country_code == $row["country"]) {
     echo "<td class='tipsy green' title='".$row["whois"]." '><b>" . $row["country"] . "</b></td>\n";
	} else {
     echo "<td class='tipsy' title='".$row["whois"]." '>" . $row["country"] . "</td>\n";
	}
     echo "<td class='' title=''>";
     if ($row["service_facebook"] == "t") {echo "<div id='facebook' class='smlogo'></div>";}
     if ($row["service_twitter"] == "t") {echo "<div id='twitter' class='smlogo'></div>";}
     if ($row["service_tumblr"] == "t") {echo "<div id='tumblr' class='smlogo'></div>";}
     if ($row["service_wordpress"] == "t") {echo "<div id='wordpress' class='smlogo'></div>";}
     if ($row["xmpp"] == "t") {echo "<div id='xmpp'><img src='/images/icon-xmpp.png' width='16px' height='16px' title='XMPP chat server' alt='XMPP chat server'></div>";}
     echo "</td></tr>\n";
if ($tt == 4) {
echo <<<EOF
<td colspan='12'>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- podup2015 -->
<ins class="adsbygoogle"
style="display:inline-block;width:728px;height:90px"
data-ad-client="ca-pub-3662181805557062"
data-ad-slot="2218925437"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</td>
EOF;
}
}
pg_free_result($result);       
pg_close($dbh);
?>
</tbody>
</table>
