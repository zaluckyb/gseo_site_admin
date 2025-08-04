<?php
/**
 * G SEO Analytics - Visitor Analytics with Visitor, Bot, and Admin Tracking
 */

 // admin/analytics/g_seo_analytics.php

if (!defined('ABSPATH')) exit;


function gseo_deactivate_analytics() {
    wp_clear_scheduled_hook('gseo_monthly_cleanup_event');
}

add_filter('cron_schedules', function($schedules) {
    $schedules['monthly'] = ['interval' => 2635200, 'display' => __('Monthly')];
    return $schedules;
});

// Capture HTTP status and visitor type
add_action('template_redirect', 'gseo_capture_visitor');

function gseo_capture_visitor() {
    global $wpdb;

    // Define table name with prefix dynamically
    $table_name = $wpdb->prefix . 'g_seo_analytics';
    
    // Data array to insert
    $data = [
        'visit_time'   => '2025-04-11 12:06:17',
        'ip_address'   => '102.182.149.179',
        'visited_url'  => 'https://geraldferreira.com/',
        'referrer_url' => 'https://geraldferreira.com/wp-admin/plugins.php?plugin_status=all&paged=1&s',
        'user_agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        'http_status'  => 200,
        'visitor_type' => 'admin'
    ];
    
    // Format specifiers for each field type
    $format = [
        '%s', // visit_time (datetime)
        '%s', // ip_address
        '%s', // visited_url
        '%s', // referrer_url
        '%s', // user_agent
        '%d', // http_status (integer)
        '%s'  // visitor_type
    ];
    
    // Insert data into table
    $wpdb->insert($table_name, $data, $format);
}

add_action('gseo_monthly_cleanup_event', function() {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}g_seo_analytics");
});

// Admin Menu
add_action('admin_menu', 'gseo_analytics_admin_menu');

function gseo_analytics_admin_menu() {
    add_submenu_page(
        'g-ultimate-seo-settings',
        'Visitor Analytics',
        'Visitor Analytics',
        'manage_options',
        'g-seo-analytics',
        'gseo_analytics_admin_page'
    );
}

// Admin Page with Tabs
function gseo_analytics_admin_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'visitors';
    ?>
    <div class="wrap">
        <h1>📊 G SEO Analytics</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=g-seo-analytics&tab=visitors" class="nav-tab <?=($active_tab=='visitors')?'nav-tab-active':'';?>">Visitor Analytics</a>
            <a href="?page=g-seo-analytics&tab=bots" class="nav-tab <?=($active_tab=='bots')?'nav-tab-active':'';?>">Bot Stats</a>
            <a href="?page=g-seo-analytics&tab=admins" class="nav-tab <?=($active_tab=='admins')?'nav-tab-active':'';?>">Admin Stats</a>
        </h2>
        <?php
        if ($active_tab === 'bots') {
            gseo_render_stats('bot', '🤖 Bot Traffic');
        } elseif ($active_tab === 'admins') {
            gseo_render_stats('admin', '👤 Admin Traffic');
        } else {
            gseo_render_stats('visitor', '👥 Visitor Traffic');
        }
        ?>
    </div>
    <?php
}

// Render Stats (Reusable function)
function gseo_render_stats($type, $title) {
    global $wpdb;
    $logs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}g_seo_analytics WHERE visitor_type = %s ORDER BY visit_time DESC LIMIT 100", $type
    ));
    ?>
    <h2><?=esc_html($title);?> (Last 100 Visits)</h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>Date/Time</th><th>IP Address</th><th>Visited URL</th><th>Referrer URL</th><th>User Agent</th><th>HTTP Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$logs): ?>
            <tr><td colspan="6">No data available yet.</td></tr>
        <?php else: foreach ($logs as $log): ?>
            <tr <?php if ($log->http_status == 404) echo 'style="background-color:#ffebee;"'; ?>>
                <td><?=esc_html($log->visit_time);?></td>
                <td><?=esc_html($log->ip_address);?></td>
                <td><a href="<?=esc_url($log->visited_url);?>" target="_blank"><?=esc_html($log->visited_url);?></a></td>
                <td><?=esc_html($log->referrer_url);?></td>
                <td><?=esc_html($log->user_agent);?></td>
                <td><?=esc_html($log->http_status);?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
}


// Current Month Detailed Data
function gseo_render_current_month_data() {
    global $wpdb;
    $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}g_seo_analytics ORDER BY visit_time DESC LIMIT 100");
    ?>
    <h2>📈 Current Month Visitor Details (with HTTP Status)</h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>IP Address</th>
                <th>Visited URL</th>
                <th>Referrer URL</th>
                <th>User Agent</th>
                <th>HTTP Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$logs): ?>
                <tr><td colspan="6">No detailed data yet.</td></tr>
            <?php else: foreach ($logs as $log): ?>
                <tr <?php if($log->http_status == 404) echo 'style="background-color: #ffebee;"'; ?>>
                    <td><?php echo esc_html($log->visit_time); ?></td>
                    <td><?php echo esc_html($log->ip_address); ?></td>
                    <td><a href="<?php echo esc_url($log->visited_url); ?>" target="_blank"><?php echo esc_html($log->visited_url); ?></a></td>
                    <td><?php echo esc_html($log->referrer_url); ?></td>
                    <td><?php echo esc_html($log->user_agent); ?></td>
                    <td>
                        <?php 
                        echo esc_html($log->http_status); 
                        if ($log->http_status == 404) echo ' ⚠️';
                        ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
}

