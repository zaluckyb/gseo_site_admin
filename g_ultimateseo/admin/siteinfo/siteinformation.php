<?php
// admin/siteinfo/siteinformation.php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include required files for additional status tabs
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteinformationadmin.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/security-info.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteplugins.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteemailinfo.php';

// Hook the status check event
add_action('gseo_site_status_event', 'gseo_check_website_status');

function gseo_check_website_status() {
    global $wpdb;

    // Initialize site status array
    $site_status = [];

    /*----------------------------------------------------
     | General Site Information
     -----------------------------------------------------*/
    $site_status['general_info'] = [
        'site_url'            => get_site_url(),
        'admin_email'         => get_option('admin_email'),
        'wordpress_version'   => get_bloginfo('version'),
        'active_theme'        => wp_get_theme()->get('Name'),
        'theme_version'       => wp_get_theme()->get('Version'),
        'php_version'         => phpversion(),
        'server_software'     => $_SERVER['SERVER_SOFTWARE'],
        'mysql_version'       => $wpdb->db_version(),
        'php_memory_limit'    => ini_get('memory_limit'),
        'max_execution_time'  => ini_get('max_execution_time').' seconds',
        'debug_mode'          => defined('WP_DEBUG') && WP_DEBUG ? 'On' : 'Off',
        'ssl_status'          => is_ssl() ? 'Enabled' : 'Not Enabled',
        'disk_free_space'     => size_format(disk_free_space(ABSPATH)),
        'disk_total_space'    => size_format(disk_total_space(ABSPATH)),
        'database_size'       => size_format($wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = '{$wpdb->dbname}'")),
        'check_timestamp'     => current_time('mysql')
    ];

    /*----------------------------------------------------
     | Post Types Information
     -----------------------------------------------------*/
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $post_type) {
        $counts = wp_count_posts($post_type);
        $site_status['post_types'][$post_type] = [
            'published'        => isset($counts->publish) ? $counts->publish : 0,
            'draft'            => isset($counts->draft) ? $counts->draft : 0,
            'latest_published' => gseo_get_latest_post_date($post_type)
        ];
    }

    /*----------------------------------------------------
     | Installed Plugins Information
     -----------------------------------------------------*/
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $plugins = get_plugins();
    $update_plugins = get_site_transient('update_plugins');

    foreach ($plugins as $plugin_file => $plugin_data) {
        $is_active = is_plugin_active($plugin_file) ? 'active' : 'inactive';
        $needs_update = isset($update_plugins->response[$plugin_file]) ? 'Update Available' : 'Up to Date';

        $site_status['installed_plugins'][] = [
            'name'          => $plugin_data['Name'],
            'version'       => $plugin_data['Version'],
            'status'        => $is_active,
            'update_status' => $needs_update
        ];
    }

    /*----------------------------------------------------
     | Users Information
     -----------------------------------------------------*/
    $roles = wp_roles()->roles;
    foreach ($roles as $role_key => $role_info) {
        $users_query = new WP_User_Query(['role' => $role_key]);
        $site_status['user_roles'][$role_info['name']] = $users_query->get_total();
    }

    // Save the collected data into the options table
    update_option('gseo_latest_site_status', $site_status, false);

    // Debugging: Log status if debug mode enabled
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        error_log('GSEO Site Status: ' . print_r($site_status, true));
    }
}

/**
 * Helper function to get latest published post date for a given post type
 */
function gseo_get_latest_post_date($post_type) {
    $latest_post = get_posts([
        'post_type'      => $post_type,
        'numberposts'    => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish'
    ]);

    return !empty($latest_post) ? $latest_post[0]->post_date : 'N/A';
}
