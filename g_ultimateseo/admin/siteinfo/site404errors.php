<?php

// admin/siteinfo/site404errors.php

add_action('template_redirect', 'gseo_log_404_errors');

function gseo_log_404_errors() {
    if (is_404()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gseo_404_errors';
        
        $wpdb->insert(
            $table_name,
            [
                'requested_url' => $_SERVER['REQUEST_URI'],
                'referer'       => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct',
                'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
                'date_logged'   => current_time('mysql')
            ]
        );
    }
}

if (!defined('ABSPATH')) exit;

function gfseo_display_404_errors() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gseo_404_errors';

    // Clear 404 Errors if requested
    if (isset($_POST['clear_404_errors']) && check_admin_referer('gfseo_clear_404_errors_action', 'gfseo_clear_404_errors_nonce')) {
        $wpdb->query("TRUNCATE TABLE $table_name");
        echo '<div class="notice notice-success is-dismissible"><p>404 errors have been cleared.</p></div>';
    }

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_logged DESC LIMIT 100");

    ?>
    <form method="post" style="margin-bottom:20px;">
        <?php wp_nonce_field('gfseo_clear_404_errors_action', 'gfseo_clear_404_errors_nonce'); ?>
        <input type="submit" name="clear_404_errors" class="button button-secondary" value="Clear 404 Errors">
    </form>

    <?php if (!$results): ?>
        <p>No 404 errors logged yet. This is great!</p>
        <?php return; endif; ?>

    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Requested URL</th>
                <th>Referrer</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo esc_html($row->date_logged); ?></td>
                <td><?php echo esc_html($row->requested_url); ?></td>
                <td><?php echo esc_html($row->referer); ?></td>
                <td><?php echo esc_html($row->user_agent); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}