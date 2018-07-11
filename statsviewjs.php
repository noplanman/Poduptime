<?php

/**
 * Include JS for stats view.
 */

declare(strict_types=1);

use Poduptime\PodStatus;
use RedBeanPHP\R;

defined('PODUPTIME') || die();

try {
    $totals = R::getAll('
        SELECT
            softwarename,
            count(*) AS pods,
            sum(total_users) AS users,
            round(avg(uptime_alltime),2) AS uptime
        FROM pods
        WHERE status < ?
        GROUP BY softwarename
        ORDER BY softwarename
    ', [PodStatus::SYSTEM_DELETED]);
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

try {
    $check_totals = R::getAll("
        SELECT
            to_char(date_checked, 'yyyy-mm') AS yymm,
            total_users AS users
        FROM monthlystats
        GROUP BY yymm, users
        ORDER BY yymm
    ");
} catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
}

?>
<script>
    /**
     * Add a new pie chart for the passed data.
     *
     * @param id   HTML element ID to place the chart.
     * @param data Data to display on the chart.
     */
    function addPieChart(id, data) {
        new Chart(document.getElementById(id), {
            type: "pie",
            data: {
                labels: <?php echo json_encode(array_column($totals, 'softwarename')); ?>,
                datasets: [{
                    data: data,
                    backgroundColor: ["#C8412E", "#36A2EB", "#FFCE56", "#419641", "#A569BD", "#EB984E", "#AC8416", "#4F8AAA", "#19FFE2", "#87FE12", "#F3BB88"],
                    hoverBackgroundColor: ["#C8887B", "#36A2AD", "#FFCE10", "#419615", "#A569AA", "#EB980A", "#AC8456", "#4F8BAB", "#19FFE9", "#87FE32", "#F3FB88"]
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false
            }
        });
    }

    /**
     * Add a new line chart for the passed data.
     *
     * @param id   HTML element ID to place the chart.
     * @param data Data to display on the chart.
     */
    function addLineChart(id, data) {
        new Chart(document.getElementById(id), {
            type: "line",
            data: {
                labels: <?php echo json_encode(array_column($check_totals, 'yymm')); ?>,
                datasets: [{
                    data: data,
                    label: 'Users',
                    fill: false,
                    borderColor: "#2ecc71",
                    backgroundColor: "#2ecc71",
                    borderWidth: 2,
                    pointHoverRadius: 2
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false
            }
        });
    }

    addPieChart('total_network_users', <?php echo json_encode(array_column($totals, 'users')); ?>);
    addPieChart('total_network_pods', <?php echo json_encode(array_column($totals, 'pods')); ?>);
    addPieChart('total_network_uptime', <?php echo json_encode(array_column($totals, 'uptime')); ?>);
    addLineChart('user_growth', <?php echo json_encode(array_column($check_totals, 'users')); ?>);
</script>
