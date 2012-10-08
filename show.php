<meta charset="utf-8"> 
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="tablesorter" width="98%">
<thead>
<tr>
<th width="220px">Diaspora Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
<th>Live Status<a class="tipsy" title="Up or Down according to Pingdom">?</a></th>
<th>Last Code Pull<a class="tipsy" title="Because the alpha is updated everyday pods with old software will not work correcly with pods with new software. This is the date the p
od last updated from the main Diaspora code.">?</a></th>
<th>Uptime<a class="tipsy" title="Percent of the time the pod is online for <?php echo date("F") ?>.">?</a></th>
<th>Months<a class="tipsy" title="How many months has this pod been online? Click number for more history.">?</a></th>
<th>Rating<a class="tipsy" title="User and Admin rating for this pod.">?</a></th>
<th>Response Time<a class="tipsy" title="Average response time for <?php echo date("F") ?>.">?</a></th>
<th>Ipv6<a class="tipsy" title="Does this pod look to have ipv6">?</a></th>
<th>Location<a class="tipsy" title="Pod location, based on IP Geolocation">?</a></th>
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
 if ($_GET['hidden'] == "true") {
 $sql = "SELECT * FROM pods WHERE hidden <> 'no' ORDER BY Hgitdate DESC, uptimelast7 DESC";
 } else {
 $sql = "SELECT * FROM pods WHERE adminrating <> -1 AND hidden <> 'yes' ORDER BY Hgitdate DESC, uptimelast7 DESC";
 }
 $result = pg_query($dbh, $sql);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 }   
 $numrows = pg_num_rows($result);
 while ($row = pg_fetch_array($result)) {
$tt=$tt+1;
if ($row["secure"] == "true") {$method = "https://";$class="green";$tip="This pod uses SSL encryption for traffic.";} else {$method = "http://";$class="red";$tip="This pod does not offer SSL";} 
//if ($tt == "3") {echo "<tr rowspan=9><td></td></tr>";}
     echo "<tr><td><div title='$tip' class='tipsy'><a class='$class' target='new' href='". $method . $row["domain"] ."'>" . $method . $row["domain"] . "</a></div></td>";
     echo "<td>" . $row["status"] . "</td>";
     echo "<td><div class='tipsy' title='Diaspora Version ".$row["connection"]."\n\r Git Revision ".$row["hgitref"]."'><div id='".$row["hgitdate"]."' class='utc-timestamp'>" . strtotime($row["hgitdate"]) . 
"</div></div></td>";
     echo "<td>" . $row["uptimelast7"] . "</td>";
if (strpos($row["pingdomurl"], "pingdom.com")) {$moreurl = $row["pingdomurl"];} else {$moreurl = "http://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=".$row["pingdomurl"];}
     echo "<td><div title='Last Check ".$row["dateupdated"]."' class='tipsy'><a target='new' href='".$moreurl."'>" . $row["monthsmonitored"] . "</a></div></td>";
if ($row["userrating"] >6) {$userratingclass="green";} elseif ($row["userrating"] <7) {$userratingclass="yellow";} elseif ($row["userrating"] <3) {$userratingclass="red";}
     echo "<td><a rel=\"facebox\" href=\"rate.php?domain=".$row["domain"]."\"><div class='tipsy rating ".$userratingclass."' title='User rating is ".$row["userrating"]."'>";
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
     echo "<td>" . $row["responsetimelast7"] . "</td>";
     echo "<td>" . $row["ipv6"] . "</td>\n";
     echo "<td class='tipsy' title='".$row["whois"]." '>" . $row["country"] . "</td></tr>\n";

 }
 pg_free_result($result);       
 pg_close($dbh);
?>
</tbody>
</table>
