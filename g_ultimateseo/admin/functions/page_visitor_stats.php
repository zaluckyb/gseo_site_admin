<?php 

// admin/functions/page_visitor_stats.php

function chart_shortcode() {
    global $wpdb;

    // Fetch visitor data by month for the past year
    $table_name = $wpdb->prefix . 'g_ultimateseo_analytics_data';
    // Fetch visitor data by month for the past year
    $monthly_visits = $wpdb->get_results("
        SELECT YEAR(date) as year, MONTH(date) as month, SUM(visit_count) as total_visit_count
        FROM {$table_name}
        WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
        GROUP BY YEAR(date), MONTH(date)
    ");
    // Oldest date
    $oldest_date = $wpdb->get_var("SELECT MIN(DATE(date)) FROM {$table_name}");

    // Total number of visits
    $total_visits = $wpdb->get_var("SELECT SUM(visit_count) FROM {$table_name}");

    $labels = [];
    $data = [];

    foreach ($monthly_visits as $visit) {
        $month_year = date("F Y", mktime(0, 0, 0, $visit->month, 1, $visit->year));
        $labels[] = $month_year;
        $data[] = $visit->total_visit_count; // Use the sum of visit_count here
    }
    
      // Fetch daily visit data for the past 30 days
      $daily_visits_query = "
      SELECT DATE(date) as visit_date, SUM(visit_count) as daily_visit_count
      FROM {$table_name}
      WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
      GROUP BY DATE(date)
      ORDER BY DATE(date) ASC
        ";
        $daily_visits = $wpdb->get_results($daily_visits_query);

        $daily_labels = [];
        $daily_data = [];

        foreach ($daily_visits as $visit) {
            $daily_labels[] = $visit->visit_date;
            $daily_data[] = $visit->daily_visit_count;
  }

    ob_start(); // Start output buffering to capture the HTML output
    // HTML for the daily visits chart
    echo "<div>";
    echo "<h2>Web Site Statistics Overview:</h2>";
    echo "The website started keeping records of website visits and statistics on <strong>{$oldest_date}</strong>. ";
    echo "Since then, the site has received a total of <strong>{$total_visits}</strong> visits. ";
    echo "</div>";
    echo "<div>";
    echo "<h3>Daily Visitors</h3>";
    echo "<canvas id='dailyVisitorChart' style='min-height: 400px; max-height:500px;'></canvas>";
    echo "</div>";
    echo "<div>";
    echo "<h3>Monthly Visitors</h3>";
    echo "<canvas id='visitorChart' style='min-height: 400px; max-height:500px;'></canvas>";
    echo "</div>";

    ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('visitorChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: '# of Visitors',
                data: <?php echo json_encode($data); ?>,
                borderWidth: 1,
                fill: 'origin',
                borderColor: 'blue',
                backgroundColor: 'rgba(173, 216, 230, 0.5)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
        // Initialize the new chart for daily visits
        const dailyCtx = document.getElementById('dailyVisitorChart');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($daily_labels); ?>,
                datasets: [{
                    label: '# of Daily Visitors',
                    data: <?php echo json_encode($daily_data); ?>,
                    borderWidth: 1,
                    fill: 'origin',
                    borderColor: 'green', // Different color for daily visits chart
                    backgroundColor: 'rgba(144, 238, 144, 0.5)' // Light green
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });


</script>

<?php

    // Additional code for tables or other content can be added here.

    return ob_get_clean(); // End output buffering and return captured output
}

add_shortcode('chart', 'chart_shortcode');