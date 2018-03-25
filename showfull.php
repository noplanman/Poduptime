<?php

use RedBeanPHP\R;

defined('PODUPTIME') || die();

try {
  $pods = R::getAll('
    SELECT domain, dnssec, podmin_statement, sslexpire, masterversion, shortversion, softwarename, monthsmonitored, score, signup, name, country, city, state, lat, long, uptime_alltime, active_users_halfyear, active_users_monthly, service_facebook, service_twitter, service_tumblr, service_wordpress, service_xmpp, latency, date_updated, ipv6, total_users, local_posts, comment_counts, stats_apikey, userrating
    FROM pods
    WHERE uptime_alltime > 50
    ORDER BY weightedscore DESC
  ');
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}
?>

<meta property="og:title" content="<?php echo count($pods); ?> Federated Pods listed, Come see the privacy aware social networks."/>
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<div class="table-responsive">
<table class="table table-striped table-sm tablesorter-bootstrap table-hover tfont">
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
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">6m</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 1 month on this pod.">1m</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total posts on this pod.">Posts</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of total comments on this pod.">Comments</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="How many months have we been watching this pod.">Months</a></th>
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
  foreach ($pods as $pod) {
    $pod_name = htmlentities($pod['name'], ENT_QUOTES);
    $tip = "\n Over {$pod['monthsmonitored']} months uptime is {$pod['uptime_alltime']}% and response time is {$pod['latency']}ms, last check on {$pod['date_updated']}. This site is SSL/TLS encrypted with a cert that expires: " . $pod['sslexpire'];

    echo '<tr><td><a title="' . $tip . '" data-toggle="tooltip" data-placement="bottom" target="_self" href="/go.php?domain=' . $pod['domain'] . '">' . $pod['domain'] . '</a></td>';

    if ($pod['shortversion'] > $pod['masterversion']) {
      $version = $pod['shortversion'];
      $pre = 'This pod runs pre release development code';
    } elseif (!$pod['shortversion']) {
      $version = '';
      $pre = 'This pod runs unknown code';
    } else {
      $version = $pod['shortversion'];
      $pre = 'This pod runs production code';
    }
    if (version_compare($pod['shortversion'], $pod['masterversion'], '=')) {
      $classver = 'text-success';
    } elseif (version_compare($pod['shortversion'], $pod['masterversion'], '<')) {
      $classver = 'text-warning';
    } else {
      $classver = 'black';
    }
    echo '<td class="' . $classver . '"><div title="' . $pre . ' version: ' . $pod['shortversion'] . ' master version is: ' . $pod['masterversion'] . '" data-toggle="tooltip" data-placement="bottom">' . $version . '</div></td>';
    echo '<td>' . $pod['softwarename'] . '</td>';
    echo '<td><a rel="facebox" href="podstat.php?domain=' . $pod['domain'] . '">' . ($pod['uptime_alltime'] > 0 ? $pod['uptime_alltime'] . '%' : '') . '</a></td>';
    echo '<td>' . ($pod['ipv6'] ? '&#10003;' : '') . '</td>';
    echo '<td>' . ($pod['latency'] > 0 ? $pod['latency'] : '') . '</td>';
    echo '<td>' . ($pod['signup'] ? '&#10003;' : '') . '</td>';
    echo '<td>' . ($pod['total_users'] > 0 ? $pod['total_users'] : '') . '</td>';
    echo '<td>' . ($pod['active_users_halfyear'] > 0 ? $pod['active_users_halfyear'] : '') . '</td>';
    echo '<td>' . ($pod['active_users_monthly'] > 0 ? $pod['active_users_monthly'] : '') . '</td>';
    echo '<td>' . ($pod['local_posts'] > 0 ? $pod['local_posts'] : '') . '</td>';
    echo '<td>' . ($pod['comment_counts'] > 0 ? $pod['comment_counts'] : '') . '</td>';
    echo '<td><div title="Last Check ' . $pod['date_updated'] . '" data-toggle="tooltip" data-placement="bottom">' . $pod['monthsmonitored'] . '</div></td>';
    echo '<td><a rel="facebox" href="rate.php?domain=' . $pod['domain'] . '">' . $pod['userrating'] . '</a></td>';
    echo '<td>' . $pod['score'] . '</td>';
    echo '<td>' . ($pod['dnssec'] ? '&#10003;' : '') . '</td>';
    if ($country_code === $pod['country']) {
      echo '<td class="text-success" data-toggle="tooltip" data-placement="bottom" title="City: ' . ($pod['city'] ?? 'n/a') . ' State: ' . ($pod['state'] ?? 'n/a') . '"><b>' . $pod['country'] . '</b></td>';
    } else {
      echo '<td data-toggle="tooltip" data-placement="bottom" title="City: ' . ($pod['city'] ?? 'n/a') . ' State: ' . ($pod['state'] ?? 'n/a') . '">' . $pod['country'] . '</td>';
    }
    echo '<td>';
    $pod['service_facebook'] && print '<div class="smlogo smlogo-facebook" title="Publish to Facebook"></div>';
    $pod['service_twitter'] && print '<div class="smlogo smlogo-twitter" title="Publish to Twitter"></div>';
    $pod['service_tumblr'] && print '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr"></div>';
    $pod['service_wordpress'] && print '<div class="smlogo smlogo-wordpress" title="Publish to WordPress"></div>';
    $pod['service_xmpp'] && print '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';
    echo '</td>';

    echo '<td>' . ($pod['podmin_statement'] ? '<a tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="left" title="Podmin Statement" data-html="true" data-content="' . htmlentities($pod['podmin_statement'], ENT_QUOTES) . '">&#128172;</a>' : '&nbsp;') . '</td></tr>';
  }
  ?>
  </tbody>
</table>
</div>
