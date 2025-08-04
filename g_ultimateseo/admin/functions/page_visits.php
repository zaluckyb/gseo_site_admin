<?php

// admin/functions/page_visits.php
require_once G_ULTIMATESEO_PATH . 'admin/functions/page_visitor_stats.php';
function g_ultimateseo_page_visits_page() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'g-ultimate-seo'));
        }
    echo '<h1>Page Visits</h1>';
    echo chart_shortcode();
?>
    <div class="wrap">
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="run_g_ultimateseo_cron">
            <input type="submit" class="button button-primary" value="Update Page Visits">
        </form>
    </div>

    <div class="wrap">
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="reset_g_ultimateseo_analytics">
            <input type="submit" class="button button-primary" value="RESET ANALYTICS">
        </form>
    </div>

    <div class="wrap">
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <?php wp_nonce_field('g_ultimateseo_aggregate_action', 'g_ultimateseo_aggregate_nonce'); ?>
            <input type="hidden" name="action" value="run_g_ultimateseo_aggregate">
            <input type="submit" class="button button-primary" value="Run Aggregate Function">
        </form>
    </div>
    <?php 
 } 

function run_g_ultimateseo_cron() {
    // Call your cron job function here
    g_ultimateseo_batch_process_data();

    // Redirect back to the admin page with a success message
    wp_redirect(add_query_arg('g_ultimateseo_cron_run', '1', admin_url('admin.php?page=g-ultimateseo-page-visits')));
    exit;
}
add_action('admin_post_run_g_ultimateseo_cron', 'run_g_ultimateseo_cron');

function g_ultimateseo_auto_aggregate_data() {
    global $wpdb;
    $analytics_table = $wpdb->prefix . 'g_ultimateseo_analytics';
    $analytics_data_table = $wpdb->prefix . 'g_ultimateseo_analytics_data';

    while (true) {
        $earliest_date = $wpdb->get_var("SELECT MIN(DATE(date)) FROM $analytics_table WHERE counted = 0");

        if (empty($earliest_date)) {
            break;
        }

        $results = $wpdb->get_results($wpdb->prepare("SELECT post_id, COUNT(*) as visit_count FROM $analytics_table WHERE DATE(date) = %s AND counted = 0 GROUP BY post_id", $earliest_date));

        foreach ($results as $row) {
            $existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $analytics_data_table WHERE post_id = %d AND date = %s", $row->post_id, $earliest_date));

            if ($existing_record) {
                $new_visit_count = $existing_record->visit_count + $row->visit_count;
                $update_result = $wpdb->update($analytics_data_table, ['visit_count' => $new_visit_count], ['id' => $existing_record->id]);

                if ($update_result === false) {
                    error_log("Error updating g_ultimateseo_analytics_data: " . $wpdb->last_error);
                }
            } else {
                $insert_result = $wpdb->insert($analytics_data_table, ['post_id' => $row->post_id, 'date' => $earliest_date, 'visit_count' => $row->visit_count]);

                if ($insert_result === false) {
                    error_log("Error inserting into g_ultimateseo_analytics_data: " . $wpdb->last_error);
                }
            }

            $wpdb->query($wpdb->prepare("UPDATE $analytics_table SET counted = 1 WHERE DATE(date) = %s AND post_id = %d", $earliest_date, $row->post_id));
        }
    }
}


// Example usage
g_ultimateseo_auto_aggregate_data();


function g_ultimateseo_reset_counted_values() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'g_ultimateseo_analytics';

    $wpdb->query("UPDATE $table_name SET counted = 0");
}

function handle_reset_g_ultimateseo_analytics() {
    g_ultimateseo_reset_counted_values();

    // Redirect back to the admin page
    wp_redirect(admin_url('admin.php?page=g-ultimateseo-page-visits'));
    exit;
}
add_action('admin_post_reset_g_ultimateseo_analytics', 'handle_reset_g_ultimateseo_analytics');

function handle_g_ultimateseo_aggregate() {
    // Check nonce for security
    check_admin_referer('g_ultimateseo_aggregate_action', 'g_ultimateseo_aggregate_nonce');

    // Run the aggregate function
    g_ultimateseo_auto_aggregate_data();

    // Redirect back to the admin page
    wp_redirect(admin_url('admin.php?page=g-ultimateseo-page-visits'));
    exit;
}
add_action('admin_post_run_g_ultimateseo_aggregate', 'handle_g_ultimateseo_aggregate');