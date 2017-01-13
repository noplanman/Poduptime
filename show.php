<?php

require_once __DIR__ . '/config.php';

// Cloudflare country code pull.
$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql = "SELECT domain,masterversion,shortversion,softwarename,monthsmonitored,score,signup,name,country,city,state,lat,long,uptime_alltime,active_users_halfyear,active_users_monthly,service_facebook,service_twitter,service_tumblr,service_wordpress,service_xmpp FROM pods WHERE NOT hidden AND signup ORDER BY uptime_alltime DESC";

$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());

$numrows = pg_num_rows($result);
?>

<meta property="og:title" content="<?php echo $numrows; ?> Federated Pods listed, Come see the privacy aware social networks."/>
<div class="hidden-sm-up">Scroll right or rotate device for more</div>
<table class="table table-striped table-sm tablesorter table-hover" id="myTable">
  <thead class="thead-inverse">
  <tr>
    <th><a data-toggle="tooltip" data-placement="bottom" title="A pod is a site for you to set up your account.">Pod</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Percent of the time the pod is online.">Uptime %</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">Active Users</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="Pod location, based on IP Geolocation">Location</a></th>
    <th><a data-toggle="tooltip" data-placement="bottom" title="External Social Networks this pod can post to">Services Offered</a></th>
  </tr>
  </thead>
  <tbody>

  <?php
  while ($row = pg_fetch_array($result)) {
    $verdiff  = str_replace('.', '', $row['masterversion']) - str_replace('.', '', $row['shortversion']);
    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $tip      = sprintf(
      'This %1$s pod %2$s has been watched for %3$s months and with an uptime of %4$s%% this month. On a scale of 100 this pod is a %5$s right now',
      $row['softwarename'],
      $pod_name,
      $row['monthsmonitored'],
      $row['uptime_alltime'],
      $row['score']
    );
    echo '<tr><td><div title="' . $tip . '" data-toggle="tooltip" data-placement="bottom"><a class="' . $class . ' url" target="_self" href="/go.php?url=https://' . $row['domain'] . '">' . $row['domain'] . '</a></div></td>';

    echo '<td>' . $row['uptime_alltime'] . '%</td>';
    echo '<td data-toggle="tooltip" data-placement="bottom" title="active six months: ' . $row['active_users_halfyear'] . ', active one month: ' . $row['active_users_monthly'] . '">' . $row['active_users_halfyear'] . '</td>';
    if ($country_code === $row['country']) {
      echo '<td class="text-success" data-toggle="tooltip" data-placement="bottom" title="City: ' . ($row['city'] ?? 'n/a') . ' State: ' . ($row['state'] ?? 'n/a') . '"><b>' . $row['country'] . '</b></td>';
    } else {
      echo '<td data-toggle="tooltip" data-placement="bottom" title="City: ' . ($row['city'] ?? 'n/a') . ' State: ' . ($row['state'] ?? 'n/a') . '">' . $row['country'] . '</td>';
    }
    echo '<td>';
    $row['service_facebook'] === 't' && print '<div class="smlogo smlogo-facebook" title="Publish to Facebook" alt="Publish to Facebook"></div>';
    $row['service_twitter'] === 't' && print '<div class="smlogo smlogo-twitter" title="Publish to Twitter" alt="Publish to Twitter"></div>';
    $row['service_tumblr'] === 't' && print '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr" alt="Publish to Tumblr"></div>';
    $row['service_wordpress'] === 't' && print '<div class="smlogo smlogo-wordpress"  title="Publish to Wordpress" alt="Publish to Wordpress"></div>';
    $row['service_xmpp'] === 't' && print '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';
    echo '</td></tr>';
  }
  pg_free_result($result);
  pg_close($dbh);
  ?>
  </tbody>
</table>
