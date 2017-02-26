<?php

require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

($_domain = $_GET['domain'] ?? null) || die('no domain given');

$sql    = "
  SELECT
    to_char(date_checked, 'yyyy MM') AS yymm,
    count(*) AS total_checks,
    round(avg(total_users)) AS users,
    round(avg(online::INT),2)*100 AS uptime,
    round(avg(latency),2) * 1000 AS latency,
    round(avg(local_posts)) AS local_posts,
    round(avg(comment_counts)) AS comment_counts,
    fpp.manualclicks,
    fpp.autoclicks
  FROM checks
  LEFT JOIN LATERAL (
    SELECT
      count(manualclick) AS manualclicks,
      count(autoclick) AS autoclicks
    FROM clicks
    WHERE checks.domain = clicks.domain
      AND EXTRACT(MONTH FROM checks.date_checked) = EXTRACT(MONTH FROM clicks.date_clicked)
      AND EXTRACT(YEAR FROM checks.date_checked) = EXTRACT(YEAR FROM clicks.date_clicked)
  ) AS fpp ON TRUE
  WHERE domain = $1
  GROUP BY yymm, fpp.manualclicks, fpp.autoclicks
  ORDER BY yymm
  LIMIT 24
";
$result = pg_query_params($dbh, $sql, [$_domain]);
$result || die('Error in SQL query: ' . pg_last_error());
$totals = pg_fetch_all($result);
?>
<canvas id="pod_chart_responses"></canvas>
<canvas id="pod_chart_counts"></canvas>
<script>
  /**
   * Add a new chart for the passed data.
   * 
   * @param id   HTML element ID to place the chart.
   * @param data Data to display on the chart.
   */
    new Chart(document.getElementById('pod_chart_responses'), {
      type: "line",
      data: {
        labels: <?php echo json_encode(array_column($totals, 'yymm')); ?>,
        datasets: [{
          data: <?php echo json_encode(array_column($totals, 'uptime')); ?>,
          label: 'Uptime %',
          fill: false,
          yAxisID: "l1",
          borderColor: "#2ecc71",
          backgroundColor: "#2ecc71",
          borderWidth: 4,
          pointHoverRadius: 6
        },
        {
          data: <?php echo json_encode(array_column($totals, 'latency')); ?>,
          label: 'Latency ms',
          fill: true,
          yAxisID: "r1",
          borderColor: "#a93226",
          backgroundColor: "#a93226",
          borderWidth: 4,
          pointHoverRadius: 6,
          pointStyle: 'rect',
        }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          yAxes: [{
            position: "left",
            "id": "l1",
            ticks: {
              min: 0,
              max: 100,
              stepSize: 20
            }
          }, {
            position: "right",
            "id": "r1",
            ticks: {
              min: 0,
              max: 500,
              stepSize: 50
            }
          }]
        }
      }
    });
        new Chart(document.getElementById('pod_chart_counts'), {
      type: "line",
      data: {
        labels: <?php echo json_encode(array_column($totals, 'yymm')); ?>,
        datasets: [{
          data: <?php echo json_encode(array_column($totals, 'users')); ?>,
          label: 'Users',
          fill: false,
          yAxisID: "l2",
          borderColor: "#e67e22",
          backgroundColor: "#e67e22",
          borderWidth: 4,
          pointHoverRadius: 6,
        },
        {
          data: <?php echo json_encode(array_column($totals, 'local_posts')); ?>,
          label: 'Local Posts',
          fill: false,
          yAxisID: "l2",
          borderColor: "#2980b9",
          backgroundColor: "#2980b9",
          borderWidth: 4,
          pointHoverRadius: 6,
        },
        {
          data: <?php echo json_encode(array_column($totals, 'comment_counts')); ?>,
          label: 'Comments',
          fill: false,
          yAxisID: "l2",
          borderColor: "#FFD700",
          backgroundColor: "#FFD700",
          borderWidth: 4,
          pointHoverRadius: 6,
        },
        {
          data: <?php echo json_encode(array_column($totals, 'manualclicks')); ?>,
          label: 'Manual Clicks',
          fill: false,
          yAxisID: "r2",
          borderColor: "#8A2BE2",
          backgroundColor: "#8A2BE2",
          borderWidth: 4,
          pointHoverRadius: 6,
          pointStyle: 'rect',
        },
        {
          data: <?php echo json_encode(array_column($totals, 'autoclicks')); ?>,
          label: 'Auto Clicks',
          fill: false,
          yAxisID: "r2",
          borderColor: "#FFA07A",
          backgroundColor: "#FFA07A",
          borderWidth: 4,
          pointHoverRadius: 6,
          pointStyle: 'rect',
        }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          yAxes: [{
            position: "left",
            "id": "l2"
          }, {
            position: "right",
            "id": "r2"
          }]
        }
      }
    });
</script>
