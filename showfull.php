<?php
$tt = 0;
require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$hidden = isset($_GET['hidden']) ? $_GET['hidden'] : null;
if ($hidden == 'true') {
  $sql = "SELECT * FROM pods WHERE hidden <> 'no' ORDER BY uptimelast7 DESC";
} else {
  $sql = 'SELECT * FROM pods ORDER BY uptimelast7 DESC';
}
$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

$numrows = pg_num_rows($result);
?>

<meta property="og:title" content="<?php echo $numrows; ?> Federated Pods listed, Come see the privacy aware social networks."/>
<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="table table-striped table-sm tablesorter table-hover tfont">
  <thead class="thead-inverse">
  <tr>
    <th><a data-toggle="tooltip" data-placement="bottom" title="A pod is a site for you to set up your account.">Pod</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Version of software this pod runs">Version</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Percent of the time the pod is online for <?php echo date('F') ?>.">Uptime</a></th>
    <th>IPv6</th>
    <th>Response Time</th>
    <th>Signups</th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total users on this pod.">Users</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">Active Users 6</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 1 month on this pod.">Active Users 1</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total posts on this pod.">Posts</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total comments on this pod.">Comments</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="How many months has this pod been online? Click number for more history.">Months</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="User rating for this pod.">Rating</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="System Score on a 100 point scale">Score</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Pod location, based on IP Geolocation">Country</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="External Social Networks this pod can post to">Services</a></th>
  </tr>
  </thead>
  <tbody>
  <?php
  while ($row = pg_fetch_array($result)) {
    $tt = $tt + 1;
    if ($row['secure'] == 'true') {
      $method = 'https://';
      $class  = 'green';
      $tip    = 'This pod uses SSL encryption for traffic.';
    } else {
      $method = 'http://';
      $class  = 'red';
      $tip    = 'This pod does not offer SSL';
    }
    $verdiff  = str_replace('.', '', $row['masterversion']) - str_replace('.', '', $row['shortversion']);
    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $tip .= "\n This {$row['softwarename']} pod {$pod_name} has been watched for {$row['monthsmonitored']} months with an uptime of {$row['uptimelast7']}% this month and a response time average today of {$row['responsetimelast7']}ms was last checked on {$row['dateupdated']}. ";
    $tip .= "On a scale of 100 this pod is a {$row['score']} right now";

    echo '<tr><td><a title="' . $tip . '" data-toggle="tooltip" data-placement="bottom" class="' . $class . '" target="_self" href="' . $method . $row['domain'] . '">' . $row['domain'] . '</a></td>';

    if (stristr($row['shortversion'], 'head')) {
      $version = '.dev';
      $pre     = 'This pod runs pre release development code';
    } elseif (!$row['shortversion']) {
      $version = '0';
      $pre     = 'This pod runs unknown code';
    } else {
      $version = $row['shortversion'];
      $pre     = 'This pod runs production code';
    }
    if ($row['shortversion'] == $row['masterversion'] && $row['shortversion'] != '') {
      $classver = 'green';
    } elseif ($verdiff > 6) {
      $classver = 'red';
    } else {
      $classver = 'black';
    }
    echo '<td class="' . $classver . '"><div title="' . $pre . ' codename: ' . $row['longversion'] . ' master version is: ' . $row['masterversion'] . '" data-toggle="tooltip" data-placement="bottom">' . $version . '</div></td>';
    echo '<td>' . $row['uptimelast7'] . '%</td>';
    echo '<td>' . $row['ipv6'] . '</td>';
    echo '<td>' . $row['responsetimelast7'] . '</td>';
    if ($row['signup'] == 1) {
      $signup = 'Open';
    } else {
      $signup = 'Closed';
    }
    echo '<td>' . $signup . '</td>';
    echo '<td>' . $row['total_users'] . '</td>';
    echo '<td>' . $row['active_users_halfyear'] . '</td>';
    echo '<td>' . $row['active_users_monthly'] . '</td>';
    echo '<td>' . $row['local_posts'] . '</td>';
    echo '<td>' . $row['comment_counts'] . '</td>';
    if (strpos($row['pingdomurl'],
      'pingdom.com')) {
      $moreurl = $row['pingdomurl'];
    } else {
      $moreurl = '/db/showuptimerobot.php?domain=' . $row['domain'];
    }
    echo '<td><div title="Last Check ' . $row['dateupdated'] . '" data-toggle="tooltip" data-placement="bottom"><a rel="facebox" href="' . $moreurl . '">' . $row['monthsmonitored'] . '</a></div></td>';

    echo '<td><a rel="facebox" href="rate.php?domain=' . $row['domain'] . '">' . $row['userrating'] . '/10';

    echo '</a></td>';
    echo '<td>' . $row['score'] . '/100</td>';
    echo '<td>' . $row['country'] . '</td>';

    echo '<td>';
    if ($row['service_facebook'] === 't') {
      echo '<div class="smlogo smlogo-facebook"></div>';
    }
    if ($row['service_twitter'] === 't') {
      echo '<div class="smlogo smlogo-twitter"></div>';
    }
    if ($row['service_tumblr'] === 't') {
      echo '<div class="smlogo smlogo-tumblr"></div>';
    }
    if ($row['service_wordpress'] === 't') {
      echo '<div class="smlogo smlogo-wordpress"></div>';
    }
    if ($row['xmpp'] === 't') {
      echo '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';
    }
    echo '</td></tr>';
  }
  pg_free_result($result);
  pg_close($dbh);
  ?>
  </tbody>
</table>
