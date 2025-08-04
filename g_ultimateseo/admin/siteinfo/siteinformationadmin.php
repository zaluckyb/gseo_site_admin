<?php 
// admin/siteinfo/siteinformationadmin.php

if (!defined('ABSPATH')) {
    exit;
}

require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/security-info.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteplugins.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteemailinfo.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/sitebrokenlinks.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/site404errors.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteemailreport.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/activity.php';
register_activation_hook(__FILE__, 'gfseo_create_email_report_table');
register_deactivation_hook(__FILE__, 'gfseo_remove_email_report_schedule');

// Schedule weekly report on plugin activation
function gfseo_activate_email_report_schedule() {
    if (!wp_next_scheduled('gfseo_weekly_email_report')) {
        wp_schedule_event(strtotime('next Monday 08:00:00'), 'weekly', 'gfseo_weekly_email_report');
    }
}
register_activation_hook(__FILE__, 'gfseo_activate_email_report_schedule');

add_action('admin_menu', 'gseo_status_admin_page');

function gseo_status_admin_page() {
    add_submenu_page(
        'g-ultimate-seo-settings',
        'Website Status Report',
        'Site Status',
        'manage_options',
        'gseo-site-status',
        'gseo_status_page_callback'
    );
}

function gseo_status_page_callback() {
    if (isset($_POST['gseo_run_status_now']) && check_admin_referer('gseo_run_status_action', 'gseo_run_status_nonce')) {
        gseo_check_website_status();
        echo '<div class="notice notice-success is-dismissible"><p>Status check ran successfully.</p></div>';
    }

    $status = get_option('gseo_latest_site_status');
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    ?>

    <div class="wrap">
        <h1>Website Status Report</h1>

        <h2 class="nav-tab-wrapper" style="margin-bottom:20px;">
            <a href="?page=gseo-site-status&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">General & Server Information</a>
            <a href="?page=gseo-site-status&tab=security" class="nav-tab <?php echo $active_tab === 'security' ? 'nav-tab-active' : ''; ?>">Security Checks</a>
            <a href="?page=gseo-site-status&tab=email" class="nav-tab <?php echo $active_tab === 'email' ? 'nav-tab-active' : ''; ?>">Email Settings</a>
            <a href="?page=gseo-site-status&tab=plugins" class="nav-tab <?php echo $active_tab === 'plugins' ? 'nav-tab-active' : ''; ?>">Plugins Status</a>
            <a href="?page=gseo-site-status&tab=broken_links" class="nav-tab <?php echo $active_tab === 'broken_links' ? 'nav-tab-active' : ''; ?>">Broken Links</a>
            <a href="?page=gseo-site-status&tab=404_errors" class="nav-tab <?php echo $active_tab === '404_errors' ? 'nav-tab-active' : ''; ?>">404 Errors</a>
            <a href="?page=gseo-site-status&tab=email_report" class="nav-tab <?php echo $active_tab === 'email_report' ? 'nav-tab-active' : ''; ?>">Email Report</a>
            <a href="?page=gseo-site-status&tab=activity" class="nav-tab <?php echo $active_tab === 'activity' ? 'nav-tab-active' : ''; ?>">Activity</a>
            <a href="?page=gseo-site-status&tab=activity_log" class="nav-tab <?php echo $active_tab === 'activity_log' ? 'nav-tab-active' : ''; ?>">Activity Log</a>
        </h2>


        <form method="post">
            <?php wp_nonce_field('gseo_run_status_action', 'gseo_run_status_nonce'); ?>
            <input type="submit" name="gseo_run_status_now" class="button button-primary" value="Run Status Check Now">
        </form>

        <?php if (!$status): ?>
            <p>No site status data yet. Click above to run a check.</p>
            <?php return; endif; ?>
<?php
            switch ($active_tab) {
    case 'security':
        echo '<h2 style="margin-top:30px;">🔒 Security Checks Overview</h2>';
        if (function_exists('gfseo_display_security_info')) gfseo_display_security_info();
        break;

    case 'plugins':
        echo '<h2 style="margin-top:30px;">🔌 Installed Plugins Overview</h2>';
        if (function_exists('gfseo_display_plugins_info')) gfseo_display_plugins_info();
        break;

    case 'email':
        echo '<h2 style="margin-top:30px;">📧 Email Configuration</h2>';
        if (function_exists('gfseo_display_email_info')) gfseo_display_email_info();
        break;

    case 'broken_links':
        echo '<h2 style="margin-top:30px;">🔗 Broken Links Checker</h2>';
        gfseo_display_broken_links_checker();
        break;

    case '404_errors':
        echo '<h2 style="margin-top:30px;">🔗 404 Errors</h2>';
        gfseo_display_404_errors();
        break;

    case 'email_report':
        echo '<h2 style="margin-top:30px;">📧 Manage Email Reports</h2>';
        gfseo_display_email_report_admin();
        break;

        case 'activity':
            echo '<h2 style="margin-top:30px;">📈 Content Activity Overview</h2>';
            if (function_exists('gfseo_activity_page_callback')) gfseo_activity_page_callback();
            break;
    
        case 'activity_log': // 👈 New Activity Log Tab Integration
            echo '<h2 style="margin-top:30px;">🔐 Login Activity Logs</h2>';
            if (function_exists('gfseo_render_activity_log_tab')) gfseo_render_activity_log_tab();
            break;

    default: // General tab default
        ?>
        <h2 style="margin-top:30px;">🔹 General & Server Information</h2>
        <table class="wp-list-table widefat striped">
            <tbody>
                <?php foreach ($status['general_info'] as $key => $value): ?>
                <tr>
                    <th style="width:250px;"><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></th>
                    <td><?php echo esc_html($value); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 style="margin-top:30px;">👥 User Roles Overview</h2>
        <table class="wp-list-table widefat striped">
            <thead><tr><th>User Role</th><th>Total Users</th></tr></thead>
            <tbody>
                <?php foreach ($status['user_roles'] as $role => $count): ?>
                <tr>
                    <td><?php echo esc_html($role); ?></td>
                    <td><?php echo esc_html($count); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 style="margin-top:30px;">📄 Post Types Overview</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr><th>Post Type</th><th>Published Posts</th><th>Draft Posts</th><th>Latest Published</th></tr>
            </thead>
            <tbody>
                <?php foreach ($status['post_types'] as $type => $data): ?>
                <tr>
                    <td><?php echo esc_html(ucwords($type)); ?></td>
                    <td><?php echo esc_html($data['published']); ?></td>
                    <td><?php echo esc_html($data['draft']); ?></td>
                    <td><?php echo esc_html(date('F j, Y g:i a', strtotime($data['latest_published']))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        break;
}
}