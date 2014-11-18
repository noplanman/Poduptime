<meta charset="utf-8"> 
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="tablesorter zebra-striped" width="98%">
<thead>
<tr>
<th width="220px">Diaspora Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
<th>Version<a class="tipsy" title="Version of Diaspora this pod runs">?</a></th>
<th>Uptime<a class="tipsy" title="Percent of the time the pod is online for <?php echo date("F") ?>.">?</a></th>
<th>Signups<a class="tipsy" title="Open to public or Closed/Invite only.">?</a></th>
<th>Total Users<a class="tipsy" title="Number of total users on this pod.">?</a></th>
<th>Active Users<a class="tipsy" title="Number of users active last 6 months on this pod.">?</a></th>
<th>Posts<a class="tipsy" title="Number of total posts on this pod.">?</a></th>
<th>Comments<a class="tipsy" title="Number of total comments on this pod.">?</a></th>
<th>Months<a class="tipsy" title="How many months has this pod been online? Click number for more history.">?</a></th>
<th>Rating<a class="tipsy" title="User and Admin rating for this pod.">?</a></th>
<th>Location<a class="tipsy" title="Pod location, based on IP Geolocation">?</a></th>
<th>Services<a class="tipsy" title="External Social Networks this pod can post to">?</a></th>
</tr>
</thead>
<tbody>
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
 $sql = "SELECT * FROM pods WHERE adminrating <> -1 AND hidden <> 'yes' ORDER BY active_users_halfyear DESC NULLS LAST, uptimelast7 DESC NULLS LAST";
 }
 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }   
 $numrows = pg_num_rows($result);
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
{$version=$row["shortversion"];$pre="This pod runs production code";}
if ($row["shortversion"] == $row["masterversion"] && $row["shortversion"] != "") {$classver = "green";} elseif ($verdiff > 6) {$classver = "red";} else {$classver = "black";}
     echo "<td class='$classver'><div title='{$pre} codename: {$row["longversion"]} master version is: {$row["masterversion"]}' class='tipsy'>{$version}</div></td>";
     echo "<td>" . $row["uptimelast7"] . "%</td>";
if ($row["signup"] == 1) {$signup="Open";} else {$signup="Closed";}
     echo "<td>" . $signup . "</td>";
     echo "<td>" . $row["total_users"] . "</td>";
     echo "<td class='tipsy' title='active six months: "  . $row["active_users_halfyear"] .  ", active one month: "  . $row["active_users_monthly"] . "'>" . $row["active_users_halfyear"] . "</td>";
     echo "<td>" . $row["local_posts"] . "</td>";
     echo "<td>" . $row["comment_counts"] . "</td>";
if (strpos($row["pingdomurl"], "pingdom.com")) {$moreurl = $row["pingdomurl"];} else {$moreurl = "http://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=".$row["pingdomurl"];}
     echo "<td><div title='Last Check ".$row["dateupdated"]."' class='tipsy'><a target='new' href='".$moreurl."'>" . $row["monthsmonitored"] . "</a></div></td>";
if ($row["userrating"] >6) {$userratingclass="green";} elseif ($row["userrating"] <7) {$userratingclass="yellow";} elseif ($row["userrating"] <3) {$userratingclass="red";}
     echo "<td><a rel=\"facebox\" href=\"rate.php?domain=".$row["domain"]."\"><div class='tipsy rating ".$userratingclass."' title='User rating is ".$row["userrating"]."/10 Auto Score is: " .$row["score"]. "/20'>";
if ($row["userrating"] == 0) {echo "no rating yet";}
for ($i = 0; $i < $row["userrating"]; $i++) { 
echo "✪";
}
if ($row["adminrating"] >6) {$adminratingclass="green";} elseif ($row["adminrating"] <7) {$adminratingclass="yellow";} elseif ($row["adminrating"] <3) {$adminratingclass="red";}
     echo "</div><br><div class='tipsy rating ".$adminratingclass."' backendscore='".$row["score"]."' title='Poduptime Approved rating is ".$row["adminrating"]."'>";
for ($iw = 0; $iw < $row["adminrating"]; $iw++) {
echo "✪";
}

     echo "</div></a></td>";
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
