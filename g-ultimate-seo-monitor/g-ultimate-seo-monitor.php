<?php
/*
Plugin Name: G Ultimate SEO Monitor
Description: Monitor security settings across multiple WordPress sites running G Ultimate SEO.
Version: 1.5
Author: You
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ✅ Define reusable paths and URLs
define('GSEOM_PATH', plugin_dir_path(__FILE__));
define('GSEOM_URL', plugin_dir_url(__FILE__));
define('GSEOM_INC', GSEOM_PATH . 'includes/');
define('GSEOM_INC_DB', GSEOM_INC . 'db/');
define('GSEOM_ADMIN', GSEOM_PATH . 'admin/');
define('GSEOM_ADMIN_PAGES', GSEOM_ADMIN . 'pages/');
define('GSEOM_ASSETS', GSEOM_URL . 'assets/');

// ✅ Include required DB setup files
require_once GSEOM_INC_DB . 'sites.php';

// ✅ Core includes (order matters)
require_once GSEOM_INC . 'site-handler.php';
require_once GSEOM_INC . 'helpers.php';
require_once GSEOM_INC . 'ajax.php';
require_once GSEOM_INC . 'render-site-table.php';

// ✅ Admin UI pages
require_once GSEOM_ADMIN_PAGES . 'dashboard.php';
require_once GSEOM_ADMIN_PAGES . 'add-site.php';
require_once GSEOM_ADMIN_PAGES . 'security-settings.php';
require_once GSEOM_ADMIN_PAGES . 'sites-info.php';
require_once GSEOM_ADMIN_PAGES . 'author-activity.php';

// ✅ Admin menu
require_once GSEOM_ADMIN . 'menu.php';

// ✅ Activation Hook: Create Tables and Add Foreign Keys
register_activation_hook(__FILE__, 'gseo_plugin_activate');
function gseo_plugin_activate() {
    global $wpdb;

    // Create tables
    gusm_create_sites_table();
    gusm_create_security_settings_table();
    gusm_create_authors_table();
    gusm_create_author_content_table();

    // Add foreign keys explicitly (dbDelta can't handle this reliably)
    $wpdb->query("ALTER TABLE {$wpdb->prefix}gusm_security_settings
        ADD CONSTRAINT fk_gusm_security_settings_site_id
        FOREIGN KEY (site_id) REFERENCES {$wpdb->prefix}gusm_sites(id)
        ON DELETE CASCADE");

    $wpdb->query("ALTER TABLE {$wpdb->prefix}gusm_author_content
        ADD CONSTRAINT fk_gusm_author_content_author_id
        FOREIGN KEY (author_id) REFERENCES {$wpdb->prefix}gusm_authors(id)
        ON DELETE CASCADE");

    $wpdb->query("ALTER TABLE {$wpdb->prefix}gusm_author_content
        ADD CONSTRAINT fk_gusm_author_content_site_id
        FOREIGN KEY (site_id) REFERENCES {$wpdb->prefix}gusm_sites(id)
        ON DELETE CASCADE");

    // Schedule daily cron job
    if (wp_next_scheduled('gusm_cron_sync_sites')) {
        wp_clear_scheduled_hook('gusm_cron_sync_sites');
    }
    wp_schedule_event(strtotime('tomorrow 06:00'), 'daily', 'gusm_cron_sync_sites');
}

// ✅ Deactivation Hook: Clean up
register_deactivation_hook(__FILE__, 'gseo_plugin_deactivate');
function gseo_plugin_deactivate() {
    global $wpdb;

    // Remove foreign key constraints (safe practice)
    $wpdb->query("ALTER TABLE {$wpdb->prefix}gusm_security_settings DROP FOREIGN KEY fk_gusm_security_settings_site_id");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}gusm_author_content DROP FOREIGN KEY fk_gusm_author_content_author_id");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}gusm_author_content DROP FOREIGN KEY fk_gusm_author_content_site_id");

    // Clear cron job
    wp_clear_scheduled_hook('gusm_cron_sync_sites');
}

// ✅ Enqueue admin styles
add_action('admin_enqueue_scripts', 'gseomon_enqueue_admin_styles');
function gseomon_enqueue_admin_styles($hook) {
    if (strpos($hook, 'gseo') === false) return;

    wp_enqueue_style('gseo-admin-styles', GSEOM_ASSETS . 'css/gseo-styles.css', [], '1.0.0');
}

// // ✅ Enqueue admin scripts with localization
// add_action('admin_enqueue_scripts', 'gseo_enqueue_admin_scripts');
// function gseo_enqueue_admin_scripts($hook) {
//     if (!isset($_GET['page']) || strpos($_GET['page'], 'gseo') === false) return;

//     wp_enqueue_script('admin-sites', GSEOM_ASSETS . 'js/admin-sites.js', ['jquery'], '1.0.0', true);
//     wp_localize_script('admin-sites', 'gseo_ajax', [
//         'ajax_url' => admin_url('admin-ajax.php'),
//         'security' => wp_create_nonce('gseo_sync_nonce'),
//     ]);
// }

add_action('admin_enqueue_scripts', 'gseo_enqueue_admin_scripts');
function gseo_enqueue_admin_scripts($hook) {
    if (!isset($_GET['page']) || strpos($_GET['page'], 'gseo') === false) return;

    wp_enqueue_script('admin-sites', GSEOM_ASSETS . 'js/admin-sites.js', ['jquery'], '1.0.0', true);

    wp_localize_script('admin-sites', 'gseo_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('gusm_sync_authors_nonce'),
    ]);
}


// ✅ Admin init hook for seeding (example)
add_action('admin_init', function () {
    if (current_user_can('manage_options') && isset($_GET['gseo_seed_settings'])) {
        gusm_add_default_security_settings(1);
        echo '<div class="notice notice-success"><p>Test default settings seeded for site ID 1.</p></div>';
    }
});