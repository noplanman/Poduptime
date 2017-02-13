<?php
require_once __DIR__ . '/config.php';

$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
$dbh || die('Error in connection: ' . pg_last_error());

$sql_totals    = 'SELECT softwarename, count(*) AS pods, sum(total_users) AS users, round(avg(uptime_alltime),2) AS uptime FROM pods GROUP BY softwarename';
$result_totals = pg_query($dbh, $sql_totals);
$result_totals || die('Error in SQL query: ' . pg_last_error());
$totals = pg_fetch_all($result_totals);
?>
<script>
  /**
   * Add a new chart for the passed data.
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
          backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#419641", "#A569BD", "#EB984E"],
          hoverBackgroundColor: ["#FF6360", "#36A2AD", "#FFCE10", "#419615", "#A569AA", "#EB980A"]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true
      }
    });
  }

  addPieChart('total_network_users', <?php echo json_encode(array_column($totals, 'users')); ?>);
  addPieChart('total_network_pods', <?php echo json_encode(array_column($totals, 'pods')); ?>);
  addPieChart('total_network_uptime', <?php echo json_encode(array_column($totals, 'uptime')); ?>);
</script>
