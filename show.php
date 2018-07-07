<?php

/**
 * Show pod list.
 */

declare(strict_types=1);

use Carbon\Carbon;
use Poduptime\PodStatus;
use RedBeanPHP\R;

defined('PODUPTIME') || die();

try {
    $pods = R::getAll('
        SELECT domain, masterversion, shortversion, softwarename, daysmonitored, podmin_statement, score, signup, name, country, countryname, city, state, uptime_alltime, active_users_halfyear, active_users_monthly, service_facebook, service_twitter, service_tumblr, service_wordpress, service_xmpp
        FROM pods
        WHERE NOT hidden
            AND status = ?
            AND signup
        ORDER BY weightedscore DESC
    ', [PodStatus::UP]);
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

?>

<meta property="og:title" content="<?php echo count($pods); ?> Federated Pods listed, Come see the privacy aware social networks." xmlns="http://www.w3.org/1999/html"/>
<div class="d-md-none">Scroll right or rotate device for more</div>
<table class="table table-striped table-bordered table-sm tablesorter table-hover">
    <thead class="thead-inverse">
    <tr>
        <th><a data-toggle="tooltip" data-placement="bottom" title="A pod is a site for you to set up your account.">Pod</a></th>
        <th data-placeholder="Try: >= 99.94"><a data-toggle="tooltip" data-placement="bottom" title="Percent of the time the pod is online.">Uptime %</a></th>
        <th><a data-toggle="tooltip" data-placement="bottom" title="Number of users active last 6 months on this pod.">Active Users</a></th>
        <th <?php echo($country_code ? 'data-placeholder="Try: $country_code"' : 'data-placeholder="Try: US"') ?>><a data-toggle="tooltip" data-placement="bottom" title="Pod location, based on IP Geolocation.">Location</a></th>
        <th class="filter-false"><a data-toggle="tooltip" data-placement="bottom" title="External Social Networks this pod can post to.">Services Offered</a></th>
        <th><a data-toggle="tooltip" data-placement="bottom" title="More information from the host of this pod.">Info</a></th>
    </tr>
    </thead>
    <tbody>

    <?php
    foreach ($pods as $pod) {
        $pod_name       = htmlentities($pod['name'], ENT_QUOTES);
        $humanmonitored = Carbon::now()->subDays($pod['daysmonitored'])->diffForHumans(null, true);
        $tip            = "This {$pod['softwarename']} pod's uptime is {$pod['uptime_alltime']}% over {$humanmonitored}.";
        if ($_COOKIE["domain"] === $pod['domain']) {
            echo '<tr><td class="bg-success"><div title="This is the last pod you visited from this site. ' . $tip . '" data-toggle="tooltip" data-placement="bottom"><a class="text-body url" target="_self" href="/go.php?domain=' . $pod['domain'] . '">' . $pod['domain'] . '</a></div></td>';
        } else {
            echo '<tr><td><div title="' . $tip . '" data-toggle="tooltip" data-placement="bottom"><a class="text-success url" target="_self" href="/go.php?domain=' . $pod['domain'] . '">' . $pod['domain'] . '</a></div></td>';
        }
        echo '<td>' . $pod['uptime_alltime'] . '%</td>';
        if ($pod['active_users_halfyear'] > 0) {
            echo '<td data-toggle="tooltip" data-placement="bottom" title="Active users six months: ' . $pod['active_users_halfyear'] . ', Active users one month: ' . $pod['active_users_monthly'] . '">' . $pod['active_users_halfyear'] . '</td>';
        } else {
            echo '<td data-toggle="tooltip" data-placement="bottom" title="Pod does not share user data."></td>';
        }
        if ($country_code === $pod['country']) {
            echo '<td class="text-success" data-toggle="tooltip" data-placement="bottom" title="Country: ' . ($pod['countryname'] ?? 'n/a') . '&#0010;City: ' . ($pod['city'] ?? 'n/a') . '&#0010;State: ' . ($pod['state'] ?? 'n/a') . '"><b>' . $pod['country'] . '</b></td>';
        } else {
            echo '<td data-toggle="tooltip" data-placement="bottom" title="Country: ' . ($pod['countryname'] ?? 'n/a') . '&#0010;City: ' . ($pod['city'] ?? 'n/a') . '&#0010;State: ' . ($pod['state'] ?? 'n/a') . '">' . $pod['country'] . '</td>';
        }
        echo '<td>';
        $pod['service_facebook'] && print '<div class="smlogo smlogo-facebook" title="Publish to Facebook"></div>';
        $pod['service_twitter'] && print '<div class="smlogo smlogo-twitter" title="Publish to Twitter"></div>';
        $pod['service_tumblr'] && print '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr"></div>';
        $pod['service_wordpress'] && print '<div class="smlogo smlogo-wordpress"  title="Publish to WordPress"></div>';
        $pod['service_xmpp'] && print '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';
        echo '</td>';

        $podmin_statement = htmlentities($pod['podmin_statement'] ?? '', ENT_QUOTES);
        echo '<td data-text="' . $podmin_statement . '">' . ($podmin_statement ? '<a tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="left" title="Podmin Statement" data-html="true" data-content="' . $podmin_statement . '">&#128172;</a>' : '&nbsp;') . '</td></tr>';
    }
    ?>
    </tbody>
</table>
<div class="pager">
    <span class="first pagination" title="First page">&laquo;</span>
    <span class="prev pagination" title="Previous page">&lt;</span>
    <span class="pagedisplay"></span>
    <span class="next pagination" title="Next page">&gt;</span>
    <span class="last pagination" title="Last page">&raquo;</span>
</div>
