<?php

use RedBeanPHP\R;

defined('PODUPTIME') || die();

try {
  $pods = R::getAll('
    SELECT domain, masterversion, shortversion, monthsmonitored, score, signup, name, uptime_alltime, active_users_halfyear, active_users_monthly, latency, date_updated, total_users, local_posts, comment_counts, stats_apikey, sslvalid
    FROM pods
    WHERE score < 50
    ORDER BY weightedscore ASC
  ');
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}
?>

<meta property="og:title" content="<?php echo count($pods); ?> #Diaspora Pods listed, Come see the privacy aware social network."/><?php echo count($pods); ?> pods that are open for signup now.
<meta charset="utf-8">
<!-- /* Copyright (c) 2011, David Morley. This file is licensed under the Affero General Public License version 3 or later. See the COPYRIGHT file. */ -->
<div class="table-responsive">
<table class="table table-striped table-sm tablesorter-bootstrap table-hover tfont">
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
    <th>Month<a class="tipsy" title="How many months has this pod been online? Click number for more history.">?</a></th>
    <th>Sc<a class="tipsy" title="System Score on a 100 scale.">?</a></th>
    <th>conn<a class="tipsy" title="">?</a></th>
    <th>Delete?<a class="tipsy" title="Delete this pod from DB?">?</a></th>
  </tr>
  </thead>
  <tbody>
  <?php
  $tt = 0;

  foreach ($pods as $pod) {
    $tt++;
    $verdiff = (int) str_replace('.', '', $pod['masterversion']) - (int) str_replace('.', '', $pod['shortversion']);

    $pod_name = htmlentities($pod['name'], ENT_QUOTES);
    $tip      = sprintf(
      'This pod %1$s has been watched for %2$s months and its average ping time is %3$s with uptime of %4$s%% this month and was last checked on %5$s. On a score of -20 to +20 this pod is a %6$s right now',
      $pod_name,
      $pod['monthsmonitored'],
      $pod['latency'],
      $pod['uptime_alltime'],
      $pod['date_updated'],
      $pod['score']
    );

    echo '<tr><td><a class="text-success" target="_self" href="https://' . $pod['domain'] . '">' . $pod['domain'] . '<div title="' . $tip . '" class="tipsy" style="display: inline-block">?</div></a></td>';

    if (stristr($pod['shortversion'], 'head')) {
      $version = '.dev';
      $pre     = 'This pod runs pre release development code';
    } elseif (!$pod['shortversion']) {
      $version = '0';
      $pre     = 'This pod runs unknown code';
    } else {
      $version = $pod['shortversion'];
      $pre     = 'This pod runs production code';
    }
    if ($pod['shortversion'] === $pod['masterversion'] && $pod['shortversion'] !== '') {
      $classver = 'text-success';
    } elseif ($verdiff > 6) {
      $classver = 'text-warning';
    } else {
      $classver = 'black';
    }
    echo '<td class="' . $classver . '"><div title="' . $pre . ' codename: ' . $pod['shortversion'] . ' master version is: ' . $pod['masterversion'] . '" class="tipsy">' . $version . '</div></td>';
    echo '<td>' . $pod['uptime_alltime'] . '</td>';
    echo '<td>' . $pod['latency'] . '</td>';
    echo '<td>' . ($pod['signup'] ? 'Open' : 'Closed') . '</td>';
    echo '<td>' . $pod['total_users'] . '</td>';
    echo '<td>' . $pod['active_users_halfyear'] . '</td>';
    echo '<td>' . $pod['active_users_monthly'] . '</td>';
    echo '<td>' . $pod['local_posts'] . '</td>';
    echo '<td>' . $pod['comment_counts'] . '</td>';
    echo '<td><div title="Last Check ' . $pod['date_updated'] . '" class="tipsy">' . $pod['monthsmonitored'] . '</div></td>';
    echo '<td>' . $pod['score'] . '</td>';
    echo '<td><div class="tipsy" title="' . $pod['sslvalid'] . '">con info</td>';
    ?>
    <td>
      <form method="post" action="db/kill.php" target="_blank">
        <input type="hidden" name="domain" value="<?php echo $pod['domain']; ?>">
        <input type="hidden" name="adminkey" value="<?php echo $_COOKIE['adminkey']; ?>">
        <label>Comments<input name="comments" value="<?php echo $pod['sslvalid']; ?>" size="10"></label>
        <label><input type="radio" name="action" value="warn">warn</label>
        <label><input type="radio" name="action" value="delete">delete</label>
        <input type="submit" value="Process">
      </form>
    </td>
    <?php
    echo '</tr>';
  }
  ?>
  </tbody>
</table>
</div>
