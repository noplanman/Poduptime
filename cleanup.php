<?php
require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql    = "SELECT domain,masterversion,shortversion,softwarename,monthsmonitored,score,signup,secure,name,country,city,state,lat,long,uptime_alltime,active_users_halfyear,active_users_monthly,service_facebook,service_twitter,service_tumblr,service_wordpress,service_xmpp,responsetime,dateupdated,ipv6,total_users,local_posts,comment_counts,stats_apikey,userrating,sslvalid FROM pods WHERE score < 50 ORDER BY weightedscore";
$result = pg_query($dbh, $sql);
$result || die('Error in SQL query: ' . pg_last_error());
$numrows = pg_num_rows($result);
?>

<meta property="og:title" content="<?php echo $numrows; ?> #Diaspora Pods listed, Come see the privacy aware social network."/><?php echo $numrows; ?> pods that are open for signup now.
<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<table id="myTable" class="table table-striped table-sm tablesorter table-hover tfont">
  <thead>
  <tr>
    <th>Pod<a class="tipsy" title="A pod is a site for you to set up your account.">?</a></th>
    <th>Version<a class="tipsy" title="Version of Diaspora this pod runs">?</a></th>
    <th>Uptime<a class="tipsy" title="Percent of the time the pod is online for <?php echo date('F') ?>.">?</a></th>
    <th>ms</th>
    <th>Signups</th>
    <th>Total<a class="tipsy" title="Number of total users on this pod.">?</a></th>
    <th>Active 6<a class="tipsy" title="Number of users active last 6 months on this pod.">?</a></th>
    <th>Active 1<a class="tipsy" title="Number of users active last 1 month on this pod.">?</a></th>
    <th>Posts<a class="tipsy" title="Number of total posts on this pod.">?</a></th>
    <th>Comm<a class="tipsy" title="Number of total comments on this pod.">?</a></th>
    <th>Month<a class="tipsy" title="How many months has this pod been online? Click number for more history.">?</a>
    </th>
    <th>Sc<a class="tipsy" title="System Score on a 100 scale.">?</a></th>
    <th>conn<a class="tipsy" title="">?</a></th>
    <th>Delete?<a class="tipsy" title="Delete this pod from DB?">?</a></th>
  </tr>
  </thead>
  <tbody>
  <?php
  $tt = 0;
  while ($row = pg_fetch_array($result)) {
    $tt++;
    $verdiff = str_replace('.', '', $row['masterversion']) - str_replace('.', '', $row['shortversion']);

    $pod_name = htmlentities($row['name'], ENT_QUOTES);
    $tip = sprintf(
      "\n" . 'This pod %1$s has been watched for %2$s months and its average ping time is %3$s with uptime of %4$s%% this month and was last checked on %5$s. On a score of -20 to +20 this pod is a %6$s right now',
      $pod_name,
      $row['monthsmonitored'],
      $row['responsetime'],
      $row['uptime_alltime'],
      $row['dateupdated'],
      $row['score']
    );

    echo '<tr><td><a class="text-success" target="_self" href="https://' $row['domain'] . '">' . $row['domain'] . '<div title="' . $tip . '" class="tipsy" style="display: inline-block">?</div></a></td>';

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
    if ($row['shortversion'] === $row['masterversion'] && $row['shortversion'] !== '') {
      $classver = 'text-success';
    } elseif ($verdiff > 6) {
      $classver = 'text-warning';
    } else {
      $classver = 'black';
    }
    echo '<td class="' . $classver . '"><div title="' . $pre . ' codename: ' . $row['shortversion'] . ' master version is: ' . $row['masterversion'] . '" class="tipsy">' . $version . '</div></td>';
    echo '<td>' . $row['uptime_alltime'] . '</td>';
    echo '<td>' . $row['responsetime'] . '</td>';
    echo '<td>' . ($row['signup'] === 't' ? 'Open' : 'Closed') . '</td>';
    echo '<td>' . $row['total_users'] . '</td>';
    echo '<td>' . $row['active_users_halfyear'] . '</td>';
    echo '<td>' . $row['active_users_monthly'] . '</td>';
    echo '<td>' . $row['local_posts'] . '</td>';
    echo '<td>' . $row['comment_counts'] . '</td>';
    $moreurl = 'https://api.uptimerobot.com/getMonitors?format=json&customUptimeRatio=7-30-60-90&apiKey=' . $row['stats_apikey'];
    echo '<td><div title="Last Check ' . $row['dateupdated'] . '" class="tipsy"><a target="_self" href="' . $moreurl . '">' . $row['monthsmonitored'] . '</a></div></td>';
    echo '<td>' . $row['score'] . '</td>';
    echo '<td><div class="tipsy" title="' . $row['sslvalid'] . '">con info </td>';
    ?>
    <td>
      <form method="post" action="db/kill.php" target="_blank">
        <input name="comments" value="<?php echo $row['sslvalid']; ?>" size=10>
        <input name="domain" value="<?php echo $row['domain']; ?>" type="hidden">
        <input name="adminkey" value="<?php echo $_COOKIE['adminkey']; ?>" type="hidden">
        <input name="action" type="radio" value="warn">warn
        <input name="action" type="radio" value="delete">delete
        <input type="submit" value="Process">
      </form>
    </td>
    <?php
    echo '</td></tr>';
  }
  pg_free_result($result);
  pg_close($dbh);
  ?>
  </tbody>
</table>
