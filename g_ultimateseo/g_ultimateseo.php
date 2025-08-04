<?php
/**
 * Plugin Name: G_UltimateSEO
 * Plugin URI: https://geraldferreira.com
 * Description: An SEO optimization and performance plugin for WordPress.
 * Version: 1.0.18
 * Author: Gerald Ferreira
 * Author URI: https://geraldferreira.com
 * License: Proprietary
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin paths and URLs for easy access
define('G_ULTIMATESEO_PATH', plugin_dir_path(__FILE__));
define('G_ULTIMATESEO_URL', plugin_dir_url(__FILE__));
define('G_ULTIMATESEO_INC', G_ULTIMATESEO_PATH . 'includes/');

// Centralized includes
require_once G_ULTIMATESEO_INC . 'files.php';

// Plugin activation and deactivation hooks
register_activation_hook(__FILE__, 'g_ultimateseo_activate');
register_activation_hook(__FILE__, 'gseo_schedule_site_status_check');
register_activation_hook(__FILE__, 'g_ultimateseo_create_table');
register_deactivation_hook(__FILE__, 'g_ultimateseo_deactivate');
register_deactivation_hook(__FILE__, 'gseo_remove_site_status_check');

// Activation callback function
function g_ultimateseo_activate() {
    $default_options = get_option('g_ultimateseo_options', array(
        'enable_images' => 1,
        'enable_schema' => 1,
        'enable_organization_schema' => 1,
    ));

    update_option('g_ultimateseo_options', $default_options);
}

// Deactivation callback function
function g_ultimateseo_deactivate() {
    wp_clear_scheduled_hook('gseo_site_status_event');
}

// Schedule site status check event
function gseo_schedule_site_status_check() {
    if (!wp_next_scheduled('gseo_site_status_event')) {
        wp_schedule_event(time(), 'twicedaily', 'gseo_site_status_event');
    }
}

// Remove scheduled event
function gseo_remove_site_status_check() {
    wp_clear_scheduled_hook('gseo_site_status_event');
}
// Explicitly allow auto-updates for G_UltimateSEO plugin
add_filter('auto_update_plugin', 'gseo_auto_update_plugin', 10, 2);

function gseo_auto_update_plugin($update, $item) {
    // Match your plugin basename exactly
    if ($item->plugin === plugin_basename(__FILE__)) {
        return true; // Enable auto-updates specifically for this plugin
    }
    return $update; // Leave other plugins unchanged
}

// Check for plugin updates
add_filter('pre_set_site_transient_update_plugins', 'gseo_check_for_update');
function gseo_check_for_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    $plugin_file = plugin_basename(__FILE__);
    $current_version = get_plugin_data(__FILE__)['Version'];
    $remote_json_url = 'https://geraldferreira.com/g_updates/g_ultimateseo.json';

    $response = wp_remote_get($remote_json_url, ['timeout' => 10]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return $transient;
    }

    $remote_data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($remote_data['version']) || empty($remote_data['download_url'])) {
        return $transient;
    }

    if (version_compare($current_version, $remote_data['version'], '<')) {
        $transient->response[$plugin_file] = (object)[
            'slug' => 'g_ultimateseo',
            'plugin' => $plugin_file,
            'new_version' => $remote_data['version'],
            'url' => $remote_data['download_url'],
            'package' => $remote_data['download_url'],
            'upgrade_notice' => $remote_data['upgrade_notice'] ?? '',
            'tested' => $remote_data['tested'] ?? '',
            'requires' => $remote_data['requires'] ?? '',
            'requires_php' => $remote_data['requires_php'] ?? ''
        ];
    }

    return $transient;
}

// Admin notice for plugin updates
add_action('admin_notices', 'gseo_show_upgrade_notice');
function gseo_show_upgrade_notice() {
    $plugin_file = plugin_basename(__FILE__);
    $update_plugins = get_site_transient('update_plugins');

    if (isset($update_plugins->response[$plugin_file])) {
        $update = $update_plugins->response[$plugin_file];
        $upgrade_notice = $update->upgrade_notice ?? '';

        if ($upgrade_notice) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>G_UltimateSEO Update Available (' . esc_html($update->new_version) . '):</strong> ' . esc_html($upgrade_notice) . '</p>';
            echo '<p><a href="' . esc_url(admin_url('plugins.php')) . '">Click here to update now.</a></p>';
            echo '</div>';
        }
    }
}

// Add HSTS security headers
add_action('send_headers', function() {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
});

// Override default WordPress mail function with SMTP
add_filter('wp_mail', function($args) {
    $sent = g_ultimateseo_send_email_smtp($args['to'], $args['subject'], $args['message'], $args['headers']);
    return $sent ? [] : $args;
}, 10, 1);

add_action('init', 'force_create_gseo_tables_once');

function force_create_gseo_tables_once() {
    if (!get_option('gseo_tables_created')) {
        g_ultimateseo_create_tables();
        update_option('gseo_tables_created', 1);
    }
}

register_activation_hook(__FILE__, function () {
    if (!get_option('gsecurity_api_token')) {
        add_option('gsecurity_api_token', wp_generate_password(32, false, false));
    }
});

add_action('plugins_loaded', 'gsecurity_ensure_token_exists');

function gsecurity_ensure_token_exists() {
    if (!get_option('gsecurity_api_token')) {
        add_option('gsecurity_api_token', wp_generate_password(32, false, false));
    }
}
add_action('admin_enqueue_scripts', 'gseo_enqueue_admin_styles');

function gseo_enqueue_admin_styles($hook) {
    if (strpos($hook, 'gseo-security') === false && strpos($hook, 'g-security') === false) {
        return;
    }
    wp_enqueue_style(
        'gseo-security-styles',
        plugins_url('css/styles.css', __FILE__),
        [],
        '1.0.1'
    );
}