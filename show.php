<?php

use RedBeanPHP\R;

defined('PODUPTIME') || die();

try {
  $pods = R::getAll('
    SELECT domain, masterversion, shortversion, softwarename, monthsmonitored, podmin_statement, score, signup, name, country, city, state, uptime_alltime, active_users_halfyear, active_users_monthly, service_facebook, service_twitter, service_tumblr, service_wordpress, service_xmpp
    FROM pods
    WHERE NOT hidden
      AND status = ?
      AND signup
    ORDER BY weightedscore DESC
  ', [PodStatus::Up]);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

?>

<meta property="og:title" content="<?php echo count($pods); ?> Federated Pods listed, Come see the privacy aware social networks."/>
<div class="hidden-sm-up">Scroll right or rotate device for more</div>
<div class="table-responsive">
<table class="table table-striped table-sm tablesorter-bootstrap table-hover">
  <thead class="thead-inverse">
  <tr>
    <th><a data-toggle="tooltip" data-placement="bottom" title="A pod is a site for you to set up your account.">Pod</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Percent of the time the pod is online.">Uptime %</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">Active Users</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Pod location, based on IP Geolocation.">Location</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="External Social Networks this pod can post to.">Services Offered</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="More information from the host of this pod.">Info</a></th>
  </tr>
  </thead>
  <tbody>

  <?php
  foreach ($pods as $pod) {
    $verdiff  = str_replace('.', '', $pod['masterversion']) - str_replace('.', '', $pod['shortversion']);
    $pod_name = htmlentities($pod['name'], ENT_QUOTES);
    $tip      = sprintf(
      'Uptime %2$s%% over %1$s months.',
      $pod['monthsmonitored'],
      $pod['uptime_alltime']
    );
    echo '<tr><td><div title="' . $tip . '" data-toggle="tooltip" data-placement="bottom"><a class="text-success url" target="_self" href="/go.php?domain=' . $pod['domain'] . '">' . $pod['domain'] . '</a></div></td>';

    echo '<td>' . $pod['uptime_alltime'] . '%</td>';
    if ($pod['active_users_halfyear'] > 0) {
    echo '<td data-toggle="tooltip" data-placement="bottom" title="Active users six months: ' . $pod['active_users_halfyear'] . ', Active users one month: ' . $pod['active_users_monthly'] . '">' . $pod['active_users_halfyear'] . '</td>';
    } else {
    echo '<td data-toggle="tooltip" data-placement="bottom" title="Pod does not share user data."></td>';
    }
    if ($country_code === $pod['country']) {
      echo '<td class="text-success" data-toggle="tooltip" data-placement="bottom" title="City: ' . ($pod['city'] ?? 'n/a') . ', State: ' . ($pod['state'] ?? 'n/a') . '"><b>' . $pod['country'] . '</b></td>';
    } else {
      echo '<td data-toggle="tooltip" data-placement="bottom" title="City: ' . ($pod['city'] ?? 'n/a') . ', State: ' . ($pod['state'] ?? 'n/a') . '">' . $pod['country'] . '</td>';
    }
    echo '<td>';
    $pod['service_facebook'] && print '<div class="smlogo smlogo-facebook" title="Publish to Facebook"></div>';
    $pod['service_twitter'] && print '<div class="smlogo smlogo-twitter" title="Publish to Twitter"></div>';
    $pod['service_tumblr'] && print '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr"></div>';
    $pod['service_wordpress'] && print '<div class="smlogo smlogo-wordpress"  title="Publish to WordPress"></div>';
    $pod['service_xmpp'] && print '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div></td>';
    echo '<td>' . ($pod['podmin_statement'] ? '<a tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="left" title="Podmin Statement" data-html="true" data-content="' . htmlentities($pod['podmin_statement'], ENT_QUOTES) . '">&#128172;</a>' : '&nbsp;') . '</td></tr>';
  }
  ?>
  </tbody>
</table>
</div>
