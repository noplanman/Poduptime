<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="tablesorter" width="98%">
	<thead>
		<tr>
			<th width="220px">Diaspora Pod<a class="tipsy"
				title="A pod is a site for you to set up your account.">?</a></th>
			<th>Version<a class="tipsy" title="Version of Diaspora this pod runs">?</a></th>
			<th>Uptime<a class="tipsy"
				title="Percent of the time the pod is online for <?php echo date("F") ?>.">?</a></th>
			<th>Signups<a class="tipsy"
				title="Open to public or Closed/Invite only.">?</a></th>
			<th>Users<a class="tipsy" title="Number of total users on this pod.">?</a></th>
			<th>Posts<a class="tipsy" title="Number of total posts on this pod.">?</a></th>
			<th>Months Online<a class="tipsy"
				title="How many months has this pod been online? Click number for more history.">?</a></th>
			<th>User Rating<a class="tipsy"
				title="User and Admin rating for this pod.">?</a></th>
			<th>Location<a class="tipsy"
				title="Pod location, based on IP Geolocation">?</a></th>
		</tr>
	</thead>
	<tbody>
<?php
require_once 'db/config.inc.php';
require_once 'db/db.class.php';

if (!$dbConnection = DB::connectDB()) {
	// Database connection failed. Do nothing
	echo "Could not connect to Database.";
} else {
	$hidden = isset ( $_GET ['hidden'] ) ? $_GET ['hidden'] : null;
	if ($hidden) {
		$sql = "SELECT * FROM pods WHERE hidden <> 'no' ORDER BY Hgitdate DESC, uptimelast7 DESC";
	} else {
		$sql = "SELECT * FROM pods WHERE hidden <> 'yes' ORDER BY Hgitdate DESC, uptimelast7 DESC";
	}
	
	if (!$result = $dbConnection->query($sql)) {
		if (DEBUG) {
			echo "Error in SQl Syntax. Error: ".$dbConnection->errorInfo[2];
		}
	} else {
		foreach ($result->fetchAll() as $row) {
			echo "<tr>\n";
			if ($row ["secure"] == "true") {
				$method = "https://";
				$class = "green";
				$tip = "This pod uses SSL encryption for traffic.<br />";
			} else {
				$method = "http://";
				$class = "red";
				$tip = "This pod does not offer SSL";
			}
			
			$versionDiff = str_replace ( ".", "", $row ["masterversion"] ) - str_replace ( '.', '', $row ["shortversion"] );
			$tip .= "This pod {$row["name"]} has been watched for {$row["monthsmonitored"]} months and its average ping time is {$row["responsetimelast7"]} with uptime of {$row["uptimelast7"]}% this month and was last checked on {$row["dateupdated"]}.<br /> ";
			$tip .= "Code base is {$row["shortversion"]} and the current github base is {$row["masterversion"]}. <br />";
			$tip .= "This pod is {$versionDiff} versions behind the current code. This pods IP {$row["ip"]} " . ($row ["ipv6"] == "yes" ? "has" : "does not have") . " IPv6 and is located in {$row["country"]}. On a score of -20 to +20 this pod is a {$row["score"]} right now, all data is checked every hour. Pod " . ($row ["signup"] == "1" ? "does" : "does not") . " allow new users. <br />";
			echo " <td><div title='$tip' class='tipsy'><a class='$class' target='new' href='" . $method . $row ["domain"] . "'>" . $method . $row ["domain"] . "</a></div></td>\n";

			if (stristr ( $row ["shortversion"], 'head' )) {
				$version = ".dev code";
				$pre = "This pod runs pre release development code";
			} elseif (! $row ["shortversion"]) {
				$version = "0";
				$pre = "This pod runs unknown code";
			} else {
				$version = $row ["shortversion"];
				$pre = "This pod runs production code";
			}
			
			if ($row ["shortversion"] == $row ["masterversion"] && $row ["shortversion"] != "") {
				$classver = "green";
			} elseif ($verdiff > 6) {
				$classver = "red";
			} else {
				$classver = "black";
			}
			
			echo " <td class='$classver'><div title='{$pre} codename: {$row["longversion"]} master version is: {$row["masterversion"]}' class='tipsy'>{$version}</div></td>\n";
			echo " <td>" . $row ["uptimelast7"] . "%</td>\n";

			$signup = $row["signup"]==1 ? "Open":"Closed";
			echo " <td>" . $signup . "</td>\n";

			echo " <td>" . $row ["total_users"] . "</td>\n";
			echo " <td>" . $row ["local_posts"] . "</td>\n";

			if (strpos($row["pingdomurl"], "pingdom.com")) {
				$moreurl = $row ["pingdomurl"];
			} else {
				$moreurl = "http://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=" . $row["pingdomurl"];
			}
			
			echo " <td><div title='Last Check " . $row ["dateupdated"] . "' class='tipsy'><a target='new' href='" . $moreurl . "'>" . $row ["monthsmonitored"] . "</a></div></td>\n";
			
			if ($row ["userrating"] > 6) {
				$userratingclass = "green";
			} elseif ($row ["userrating"] <= 6) {
				$userratingclass = "yellow";
			} elseif ($row ["userrating"] < 3) {
				$userratingclass = "red";
			}
			echo " <td><a rel=\"facebox\" href=\"rate.php?domain=" . $row ["domain"] . "\"><div class='tipsy rating " . $userratingclass . "' title='User rating is " . $row ["userrating"] . "/10 Auto Score is: " . $row ["score"] . "/20'>";
			if ($row ["userrating"] == 0) {
				echo "no rating yet";
			}
			for($i = 0; $i < $row ["userrating"]; $i ++) {
				echo "âœª";
			}
			if ($row ["adminrating"] > 6) {
				$adminratingclass = "green";
			} elseif ($row ["adminrating"] <= 6) {
				$adminratingclass = "yellow";
			} elseif ($row ["adminrating"] < 3) {
				$adminratingclass = "red";
			}
			echo "</div><br><div class='tipsy rating " . $adminratingclass . "' backendscore='" . $row ["score"] . "' title='Poduptime Approved rating is " . $row ["adminrating"] . "'>";
			for($iw = 0; $iw < $row ["adminrating"]; $iw ++) {
				echo "âœª";
			}
		
			echo "</div></a></td>\n";
			
			echo " <td class='tipsy' title='" . $row ["whois"] . " '>" . $row ["country"] . "</td>\n";
			
			echo "</tr>\n";
		}
	}
}
?>
</tbody>
</table>
