<?php
$tt=0;
require_once __DIR__ . '/config.php';

 $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
 if (!$dbh) {
     die("Error in connection: " . pg_last_error());
 }  
 $sql = "SELECT * FROM pods WHERE hidden <> 'no' AND score < 50 ORDER BY weightedscore";
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
 pods that are open for signup now.
<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="table table-striped table-sm tablesorter table-hover tfont">
<thead>
<tr>
<th>Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
<th>Version<a class="tipsy" title="Version of Diaspora this pod runs">?</a></th>
<th>Uptime<a class="tipsy" title="Percent of the time the pod is online for <?php echo date("F") ?>.">?</a></th>
<th>ms</th>
<th>Signups</th>
<th>Total<a class="tipsy" title="Number of total users on this pod.">?</a></th>
<th>Active 6<a class="tipsy" title="Number of users active last 6 months on this pod.">?</a></th>
<th>Active 1<a class="tipsy" title="Number of users active last 1 month on this pod.">?</a></th>
<th>Posts<a class="tipsy" title="Number of total posts on this pod.">?</a></th>
<th>Comm<a class="tipsy" title="Number of total comments on this pod.">?</a></th>
<th>Month<a class="tipsy" title="How many months has this pod been online? Click number for more history.">?</a></th>
<th>Sc<a class="tipsy" title="System Score on a 100 scale">?</a></th>
<th>conn<a class="tipsy" title="">?</a></th>
<th>Delete?<a class="tipsy" title="Delete this pod from DB?">?</a></th>
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
$tip.="\n This pod {$pod_name} has been watched for {$row["monthsmonitored"]} months and its average ping time is {$row["responsetimelast7"]} with uptime of {$row["uptimelast7"]}% this month and was last checked on {$row["dateupdated"]}. ";
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
     echo "<td>" . $row["uptimelast7"] . "</td>";
     echo "<td>" . $row["responsetimelast7"] . "</td>";
if ($row["signup"] == 1) {$signup="Open";} else {$signup="Closed";}
     echo "<td>" . $signup . "</td>";
     echo "<td>" . $row["total_users"] . "</td>";
     echo "<td>" . $row["active_users_halfyear"] . "</td>";
     echo "<td>" . $row["active_users_monthly"] . "</td>";
     echo "<td>" . $row["local_posts"] . "</td>";
     echo "<td>" . $row["comment_counts"] . "</td>";
if (strpos($row["pingdomurl"], "pingdom.com")) {$moreurl = $row["pingdomurl"];} else {$moreurl = "http://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=".$row["pingdomurl"];}
     echo "<td><div title='Last Check ".$row["dateupdated"]."' class='tipsy'><a target='new' href='".$moreurl."'>" . $row["monthsmonitored"] . "</a></div></td>";
     echo "<td>" . $row["score"] . "</td>\n";
     echo "<td><div class='tipsy' title='".$row["sslvalid"]."'>con info </td>\n";
?>
<td>
<form method="post" action="db/kill.php"  target="_blank">
<input name="comments" value="<?php echo $row["sslvalid"] ?>" size=10>
<input name="domain" value="<?php echo $row["domain"] ?>" type="hidden">
<input name="adminkey" value="<?php echo $_COOKIE["adminkey"] ?>" type="hidden">
<input name="action" type="radio" value="warn">warn
<input name="action" type="radio" value="delete">delete
<input type="submit" value="Process">
</form>
</td>
<?php
     echo "</td></tr>\n";
 }
 pg_free_result($result);       
 pg_close($dbh);
?>
</tbody>
</table>
