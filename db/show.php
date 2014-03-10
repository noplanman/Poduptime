<?php
/**
 * Copyright (c) 2011, David Morley.
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYRIGHT file.
 */
?>

<table id="myTable" class="tablesorter" style="width: 98%">
<thead>
<tr>
<th style="width:220px">Diaspora Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
<th>Live Status<a class="tipsy" title="Up or Down according to Pingdom">?</a></th>
<th>Last Code Pull<a class="tipsy" title="Because the alpha is updated everyday pods with old software will not work correcly with pods with new software. This is the date the p
od last updated from the main Diaspora code.">?</a></th>
<th>Uptime<a class="tipsy" title="Percent of the time the pod is online for <?php echo date("F") ?>.">?</a></th>
<th>Months<a class="tipsy" title="How many months has this pod been online? Click number for more history.">?</a></th>
<th>Rating<a class="tipsy" title="User and Admin rating for this pod.">?</a></th>
<th>Response Time<a class="tipsy" title="Average response time for <?php echo date("F") ?>.">?</a></th>
<th>Ipv6<a class="tipsy" title="Does this pod look to have ipv6">?</a></th>
</tr>
</thead>
<tbody>
<?php
require_once 'config.inc.php';
require_once 'db.class.php';

$dbConnection = DB::connectDB();
if (!$dbConnection) {
    die("Error in connection: " . $dbConnection->errorInfo()[2]);
}

if (isset($_GET['hidden']) && $_GET['hidden'] == "true") {
    $sql = "SELECT * FROM pods WHERE hidden <> 'no'";
} else {
    $sql = "SELECT * FROM pods WHERE hidden <> 'yes'";
}

$result = $dbConnection->query($sql);
if (!$result) {
    die("Error in SQL query: " . $dbConnection->errorInfo()[2]);
}

foreach ($result->fetchAll() as $row) {
    if ($row["secure"] == "true") {
        $method = "https://";$class="green";$tip="This pod uses SSL encryption for traffic.";
    } else {
        $method = "http://";$class="red";$tip="This pod does not offer SSL";
    }
    echo "<tr><td><div title='$tip' class='tipsy'><a class='$class' target='new' href='". $method . $row["domain"] ."'>" . $method . $row["domain"] . "</a></div></td>";
    echo "<td>" . $row["status"] . "</td>";
    echo "<td><div class='tipsy' title='Git Revision ".$row["hgitref"]."'><div id='".$row["hgitdate"]."' class='utc-timestamp'>" . strtotime($row["hgitdate"]) . "</div></div></td>";
    echo "<td>" . $row["uptimelast7"] . "%</td>";
    echo "<td><div title='Last Check ".$row["dateupdated"]."' class='tipsy'><a target='new' href='".$row["pingdomurl"]."'>" . $row["monthsmonitored"] . "</a></div></td>";

    if ($row["userrating"] > 6) {
        $userratingclass="green";
    } elseif ($row["userrating"] < 7) {
        $userratingclass="yellow";
    } elseif ($row["userrating"] < 3) {
        $userratingclass="red";
    }
    echo "<td><div class='tipsy rating ".$userratingclass."' title='User rating is ".$row["adminrating"]."'>";

    for ($i = 0; $i < $row["userrating"]; $i++) {
        echo "✪";
    }
    if ($row["adminrating"] > 6) {
        $adminratingclass="green";
    } elseif ($row["adminrating"] < 7) {
        $adminratingclass="yellow";
    } elseif ($row["adminrating"] < 3) {
        $adminratingclass="red";
    }
    echo "</div><br><div class='tipsy rating ".$adminratingclass."' backendscore='".$row["score"]."' title='Poduptime rating is ".$row["adminrating"]."'>";
    for ($i = 0; $i < $row["adminrating"]; $i++) {
        echo "✪";
    }

    echo "</div></td>";
    echo "<td>" . $row["responsetimelast7"] . "</td>";
    echo "<td class='tipsy' title='IP Address ".$row["ip"]." '>" . $row["ipv6"] . "</td></tr>\n";
 }
?>
</tbody>
</table>
