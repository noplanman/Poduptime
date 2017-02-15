<?php

require_once __DIR__ . '/config.php';

// Cloudflare country code pull.
$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql = 'SELECT domain,dnssec,podmin_statement,sslexpire,masterversion,shortversion,softwarename,monthsmonitored,score,signup,name,country,city,state,lat,long,uptime_alltime,active_users_halfyear,active_users_monthly,service_facebook,service_twitter,service_tumblr,service_wordpress,service_xmpp,latency,date_updated,ipv6,total_users,local_posts,comment_counts,stats_apikey,userrating FROM pods pods ORDER BY weightedscore DESC';

$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

$numrows = pg_num_rows($result);
?>

<meta property="og:title" content="<?php echo $numrows; ?> Federated Pods listed, Come see the privacy aware social networks."/>
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<div class="table-responsive">
<table class="table table-striped table-sm tablesorter table-hover tfont">
  <thead class="thead-inverse">
  <tr>
    <th><a data-toggle="tooltip" data-placement="bottom" title="A pod is a site for you to set up your account.">Pod</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Version of software this pod runs">Version</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Type of software this pod runs">Software</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Percent of the time the pod is online.">Uptime</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Does this pod offer ipv6 connection.">IPv6</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Average connection latency time in ms from Los Angeles.">Latency</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Does this pod allow new users.">Signups</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total users on this pod.">Users</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">Active 6m</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 1 month on this pod.">Active 1m</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total posts on this pod.">Posts</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total comments on this pod.">Comments</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="How many months has this pod been online.">Months</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="User rating for this pod.">Rating</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="System Score on a 100 point scale.">Score</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Does this domain use DNSSEC.">DNSSEC</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Pod location, based on IP Geolocation.">Country</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="External Social Networks this pod can post to.">Services</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Click for more information about this pod from the pod host (podmin).">Info</a></th>
  </tr>
  </thead>
  <tbody>
  <?php
  while ($row = pg_fetch_array($result)) {
    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $tip = "\n Over {$row['monthsmonitored']} months uptime is {$row['uptime_alltime']}% and response time is {$row['latency']}ms, last check on {$row['date_updated']}. ";

    echo '<tr><td><a title="' . $tip . '" data-toggle="tooltip" data-placement="bottom" target="_self" href="/go.php?domain=' . $row['domain'] . '">' . $row['domain'] . '</a><span class="text-success" " data-toggle="tooltip" title="This site is SSL/TLS encrypted with a cert that expires: ' . $row['sslexpire'] . '"> &#128274;</span></td>';

    if ($row['shortversion'] > $row['masterversion']) {
      $version = $row['shortversion'];
      $pre     = 'This pod runs pre release development code';
    } elseif (!$row['shortversion']) {
      $version = '';
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
    echo '<td>' . ($row['uptime_alltime'] > 0 ? $row['uptime_alltime'].'%' : '-') . '</td>';
    echo '<td>' . ($row['ipv6'] === 't' ? '&#10003;' : '') . '</td>';
    echo '<td>' . ($row['latency'] > 0 ? $row['latency'] : '') . '</td>';
    echo '<td>' . ($row['signup'] === 't' ? '&#10003;' : '') . '</td>';
    echo '<td>' . ($row['total_users'] > 0 ? $row['total_users'] : '') . '</td>';
    echo '<td>' . ($row['active_users_halfyear'] > 0 ? $row['active_users_halfyear'] : '') . '</td>';
    echo '<td>' . ($row['active_users_monthly'] > 0 ? $row['active_users_monthly'] : '') . '</td>';
    echo '<td>' . ($row['local_posts'] > 0 ? $row['local_posts'] : '') . '</td>';
    echo '<td>' . ($row['comment_counts'] > 0 ? $row['comment_counts'] : '') . '</td>';
    $moreurl = '/showstats.php?domain=' . $row['domain'];
    echo '<td><div title="Last Check ' . $row['date_updated'] . '" data-toggle="tooltip" data-placement="bottom">' . $row['monthsmonitored'] . '</div></td>';
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
    
    echo '<td>' . ($row['podmin_statement'] ? '<a tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="left" title="Podmin Statement" data-html="true" data-content="' . htmlentities($row['podmin_statement'], ENT_QUOTES) . '">&#8505;</a>' : '&nbsp;') . '</td></tr>';
  }
  ?>
  </tbody>
</table>
</div>
