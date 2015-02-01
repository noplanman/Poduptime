<?php
$tt=0;
 include('db/config.php');
 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
 if (!$dbh) {
     die("Error in connection: " . pg_last_error());
 }  
 $hidden = isset($_GET['hidden'])?$_GET['hidden']:null;
 if ($hidden == "true") {
 $sql = "SELECT * FROM pods WHERE hidden <> 'no' ORDER BY active_users_halfyear DESC NULLS LAST, uptimelast7 DESC NULLS LAST";
 } else {
 $sql = "SELECT * FROM pods WHERE adminrating <> -1 AND hidden <> 'yes' AND signup = 1 ORDER BY active_users_halfyear DESC NULLS LAST, uptimelast7 DESC NULLS LAST";
 }
 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }   
 $numrows = pg_num_rows($result);
echo "<meta property='og:title' content='"; 
echo $numrows;
echo " #Diaspora Pods listed, Come see the privacy aware social network.' />";
echo $numrows;
?>
 pods that are open for signup now.  Click column names to sort and find a pod.  Show as: <a onClick="map();">Map</a> <a onClick="nomap();">Table</a> 
<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="tablesorter zebra-striped" width="98%">
<thead>
<tr>
<th width="220px">Diaspora Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
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


$tip.="\n This pod {$row["name"]} has been watched for {$row["monthsmonitored"]} months and its average ping time is {$row["responsetimelast7"]} with uptime of {$row["uptimelast7"]}% this month and was last checked on {$row["dateupdated"]}. "; 
$tip.="On a score of -20 to +20 this pod is a {$row["score"]} right now";

     echo "<tr><td><a class='$class' target='new' href='". $method . $row["domain"] ."'>" . $row["domain"] . " <div title='$tip' class='tipsy' style='display: inline-block'>?</div></a></td>";
"</div></td>";

if (stristr($row["shortversion"],'head')) 
{$version=".dev";$pre = "This pod runs pre release 
development code";} elseif (!$row["shortversion"]) 
{$version="0";$pre = "This pod runs 
unknown code";} 
else 
     echo "<td>" . $row["uptimelast7"] . "%</td>";
     echo "<td class='tipsy' title='active six months: "  . $row["active_users_halfyear"] .  ", active one month: "  . $row["active_users_monthly"] . "'>" . $row["active_users_halfyear"] . "</td>";
     echo "<td class='tipsy' title='".$row["whois"]." '>" . $row["country"] . "</td>\n";
     echo "<td class='' title=''>";
     if ($row["service_facebook"] == "t") {echo "<div id='facebook' class='smlogo'></div>";}
     if ($row["service_twitter"] == "t") {echo "<div id='twitter' class='smlogo'></div>";}
     if ($row["service_tumblr"] == "t") {echo "<div id='tumblr' class='smlogo'></div>";}
     if ($row["service_wordpress"] == "t") {echo "<div id='wordpress' class='smlogo'></div>";}
     echo "</td></tr>\n";
if ($tt == 5) {
echo <<<EOF
<td colspan='12' style='padding-left:200px;'>
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
$country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
//echo $country_code;
?>
</tbody>
</table>
