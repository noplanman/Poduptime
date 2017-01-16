<?php

require_once __DIR__ . '/config.php';

// Cloudflare country code pull.
$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql = 'SELECT domain,dnssec,terms,sslexpire,masterversion,shortversion,softwarename,monthsmonitored,score,signup,name,country,city,state,lat,long,uptime_alltime,active_users_halfyear,active_users_monthly,service_facebook,service_twitter,service_tumblr,service_wordpress,service_xmpp,responsetime,date_updated,ipv6,total_users,local_posts,comment_counts,stats_apikey,userrating FROM pods pods ORDER BY uptime_alltime DESC';

$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

$numrows = pg_num_rows($result);
?>

<meta property="og:title" content="<?php echo $numrows; ?> Federated Pods listed, Come see the privacy aware social networks."/>
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="table table-striped table-sm tablesorter table-hover tfont">
  <thead class="thead-inverse">
  <tr>
    <th><a data-toggle="tooltip" data-placement="bottom" title="A pod is a site for you to set up your account.">Pod</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Version of software this pod runs">Version</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Type of software this pod runs">Software</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Percent of the time the pod is online.">Uptime</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Does this pod offer ipv6 connection.">IPv6</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Average response time in ms.">Response Time</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Does this pod allow new users.">Signups</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total users on this pod.">Users</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">Active 6m</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 1 month on this pod.">Active 1m</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total posts on this pod.">Posts</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total comments on this pod.">Comments</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="How many months has this pod been online? Click number for more history.">Months</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="User rating for this pod.">Rating</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="System Score on a 100 point scale.">Score</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Does this domain use DNSSEC.">DNSSEC</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Pod location, based on IP Geolocation.">Country</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="External Social Networks this pod can post to.">Services</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Terms page for this pod.">Terms</a></th>
  </tr>
  </thead>
  <tbody>
  <?php
  while ($row = pg_fetch_array($result)) {
    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $tip = "\n This {$row['softwarename']} pod {$pod_name} has been watched for {$row['monthsmonitored']} months with an overall uptime of {$row['uptime_alltime']}% and a response time average today of {$row['responsetime']}ms was last checked on {$row['date_updated']}. ";
    $tip .= "On a scale of 100 this pod is a {$row['score']} right now";

    echo '<tr><td><a title="' . $tip . '" data-toggle="tooltip" data-placement="bottom" class="text-success" target="_self" href="/go.php?domain=' . $row['domain'] . '">' . $row['domain'] . '</a></td>';

    if ($row['shortversion'] > $row['masterversion']) {
      $version = $row['shortversion'];
      $pre     = 'This pod runs pre release development code';
    } elseif (!$row['shortversion']) {
      $version = '0';
      $pre     = 'This pod runs unknown code';
    } else {
      $version = $row['shortversion'];
      $pre     = 'This pod runs production code';
    }
    if (version_compare($row['shortversion'], $row['masterversion'], '=')) {
      $classver = 'text-success';
    } elseif (version_compare($row['shortversion'], $row['masterversion'], '<')) {
      $classver = 'text-warning';
    } else {
      $classver = 'black';
    }
    echo '<td class="' . $classver . '"><div title="' . $pre . ' version: ' . $row['shortversion'] . ' master version is: ' . $row['masterversion'] . '" data-toggle="tooltip" data-placement="bottom">' . $version . '</div></td>';
    echo '<td>' . $row['softwarename'] . '</td>';
    echo '<td>' . $row['uptime_alltime'] . '%</td>';
    echo '<td>' . ($row['ipv6'] === 't' ? '&#10003;' : '') . '</td>';
    echo '<td>' . $row['responsetime'] . '</td>';
    echo '<td>' . ($row['signup'] === 't' ? '&#10003;' : '') . '</td>';
    echo '<td>' . $row['total_users'] . '</td>';
    echo '<td>' . $row['active_users_halfyear'] . '</td>';
    echo '<td>' . $row['active_users_monthly'] . '</td>';
    echo '<td>' . $row['local_posts'] . '</td>';
    echo '<td>' . $row['comment_counts'] . '</td>';
    $moreurl = '/showstats.php?domain=' . $row['domain'];
    echo '<td><div title="Last Check ' . $row['date_updated'] . '" data-toggle="tooltip" data-placement="bottom"><a rel="facebox" href="' . $moreurl . '">' . $row['monthsmonitored'] . '</a></div></td>';
    echo '<td><a rel="facebox" href="rate.php?domain=' . $row['domain'] . '">' . $row['userrating'];
    echo '</a></td>';
    echo '<td>' . $row['score'] . '</td>';
    echo '<td>' . ($row['dnssec'] === 't' ? '&#10003;' : '') . '</td>';
    if ($country_code === $row['country']) {
      echo '<td class="text-success" data-toggle="tooltip" data-placement="bottom" title="City: ' . ($row['city'] ?? 'n/a') . ' State: ' . ($row['state'] ?? 'n/a') . '"><b>' . $row['country'] . '</b></td>';
    } else {
      echo '<td data-toggle="tooltip" data-placement="bottom" title="City: ' . ($row['city'] ?? 'n/a') . ' State: ' . ($row['state'] ?? 'n/a') . '">' . $row['country'] . '</td>';
    }
    echo '<td>';
    $row['service_facebook'] === 't' && print '<div class="smlogo smlogo-facebook" title="Publish to Facebook"></div>';
    $row['service_twitter'] === 't' && print '<div class="smlogo smlogo-twitter" title="Publish to Twitter"></div>';
    $row['service_tumblr'] === 't' && print '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr"></div>';
    $row['service_wordpress'] === 't' && print '<div class="smlogo smlogo-wordpress" title="Publish to WordPress"></div>';
    $row['service_xmpp'] === 't' && print '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';
    echo '</td>';
    echo '<td><a href="https://' . $row['domain'] . $row['terms'] . '">&#128279;</a></td></tr>';
  }
  ?>
  </tbody>
</table>
