<?php

if (!function_exists('gusm_add_default_security_settings')) {
    require_once GSEOM_INC . 'helpers.php';
}

// Create gusm_sites table.
function gusm_create_sites_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gusm_sites';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        url TEXT NOT NULL,
        token TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'unknown',
        seo_score INT DEFAULT NULL,
        last_checked DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        INDEX url_idx (url(255)),
        INDEX last_checked_idx (last_checked)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Corrected: gusm_create_security_settings_table (WITHOUT foreign key)
function gusm_create_security_settings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gusm_security_settings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        site_id BIGINT UNSIGNED NOT NULL,
        option_name VARCHAR(100) NOT NULL,
        option_value VARCHAR(255) NOT NULL DEFAULT '0',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX site_opt_idx (site_id, option_name)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Create gusm_authors table to track authors and associated emails.
function gusm_create_authors_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gusm_authors';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        emails TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        INDEX name_idx (name)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Corrected: gusm_create_author_content_table (WITH role field explicitly included)
function gusm_create_author_content_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gusm_author_content';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        author_id BIGINT UNSIGNED NOT NULL,
        site_id BIGINT UNSIGNED NOT NULL,
        post_title VARCHAR(255) NOT NULL,
        post_url TEXT NOT NULL,
        post_date DATETIME NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT '', -- Clearly added role field here
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX author_site_idx (author_id, site_id),
        INDEX role_idx (role)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Plugin activation: create all tables and schedule cron.
function gusm_plugin_activate() {
    gusm_create_sites_table();
    gusm_create_security_settings_table();
    gusm_create_authors_table();
    gusm_create_author_content_table();

    if (wp_next_scheduled('gusm_cron_sync_sites')) {
        wp_clear_scheduled_hook('gusm_cron_sync_sites');
    }

    wp_schedule_event(strtotime('tomorrow 06:00'), 'daily', 'gusm_cron_sync_sites');
}
register_activation_hook(__FILE__, 'gusm_plugin_activate');

// Plugin deactivation: clear scheduled cron.
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('gusm_cron_sync_sites');
});

add_action('gusm_cron_sync_sites', function () {
    global $wpdb;
    $sites = gusm_get_all_sites();

    foreach ($sites as $site) {
        $site_id = (int)$site['id'];
        $url = $site['url'];
        $token = $site['token'];

        if (!$site_id) {
            error_log("❌ ERROR: Site {$url} has invalid ID ({$site_id}). Skipped.");
            continue;
        }

        // Fetch security status
        $status_result = gseo_fetch_site_status($url, $token, 'status');
        if ($status_result['status'] !== 200 || empty($status_result['body'])) {
            $wpdb->update($wpdb->prefix . 'gusm_sites', [
                'status' => 'failed',
                'last_checked' => current_time('mysql'),
            ], ['id' => $site_id]);
            continue;
        }

        // Fetch detailed site info
        $site_info_result = gseo_fetch_site_status($url, $token, 'site-info');
        if ($site_info_result['status'] !== 200 || empty($site_info_result['body'])) {
            $wpdb->update($wpdb->prefix . 'gusm_sites', [
                'status' => 'failed',
                'last_checked' => current_time('mysql'),
            ], ['id' => $site_id]);
            continue;
        }

        // Update Security Features
        foreach ($status_result['body']['features'] as $option => $value) {
            gusm_save_security_option($site_id, $option, (int)$value);
        }

        // Update Security Headers
        foreach ($status_result['body']['security_headers'] as $header => $enabled) {
            gusm_save_security_option($site_id, 'header_' . $header, (int)$enabled);
        }

        // Update CSP Settings
        foreach ($status_result['body']['csp'] as $directive => $data) {
            gusm_save_security_option($site_id, "{$directive}_apply", (int)$data['apply']);
            gusm_save_security_option($site_id, "{$directive}_value", sanitize_text_field($data['value']));
        }

        // Explicitly save detailed site info
        gusm_save_fetched_site_data($site_id, [
            'site_info' => [
                'server_info'      => $site_info_result['body']['server_info'] ?? [],
                'user_roles'       => $site_info_result['body']['user_roles'] ?? [],
                'post_types'       => $site_info_result['body']['post_types'] ?? [],
                'security_checks'  => $site_info_result['body']['security_checks'] ?? [],
                'email_settings'   => $site_info_result['body']['email_settings'] ?? [],
                'plugin_status'    => $site_info_result['body']['plugin_status'] ?? [],
                'content_activity' => $site_info_result['body']['recent_activity'] ?? [],
                'broken_links'     => intval($site_info_result['body']['broken_links'] ?? 0),
                'errors_404'       => intval($site_info_result['body']['errors_404'] ?? 0),
            ]
        ]);

        // Mark site as successfully connected
        $wpdb->update($wpdb->prefix . 'gusm_sites', [
            'status' => 'connected',
            'last_checked' => current_time('mysql'),
        ], ['id' => $site_id]);
    }
});


// Existing site insertion remains unchanged.
function gusm_add_site_to_db($name, $url, $token) {
    global $wpdb;
    $table = $wpdb->prefix . 'gusm_sites';

    $wpdb->insert($table, [
        'name'          => sanitize_text_field($name),
        'url'           => esc_url_raw($url),
        'token'         => sanitize_text_field($token),
        'status'        => 'unknown',
        'last_checked'  => current_time('mysql'),
    ]);

    $site_id = $wpdb->insert_id;

    if ($site_id && function_exists('gusm_add_default_security_settings')) {
        gusm_add_default_security_settings($site_id);
    }
}

// Existing fetch all sites function unchanged.
function gusm_get_all_sites() {
    global $wpdb;
    $table = $wpdb->prefix . 'gusm_sites';

    return $wpdb->get_results(
        "SELECT id, name, url, token, status FROM $table ORDER BY created_at DESC",
        ARRAY_A
    );
}

// Existing admin init hook unchanged.
add_action('admin_init', function () {
    if (current_user_can('manage_options') && isset($_GET['gseo_seed_settings'])) {
        gusm_add_default_security_settings(1);
        echo '<div class="notice notice-success"><p>Test default settings seeded for site ID 1.</p></div>';
    }
});

// Existing function to save fetched site data unchanged.
function gusm_save_fetched_site_data($site_id, $fetched_data) {
    global $wpdb;
    $table = $wpdb->prefix . 'gusm_site_info';

    $wpdb->replace($table, [
        'site_id'          => $site_id,
        'server_info'      => maybe_serialize($fetched_data['site_info']['server_info'] ?? []),
        'user_roles'       => maybe_serialize($fetched_data['site_info']['user_roles'] ?? []),
        'post_types'       => maybe_serialize($fetched_data['site_info']['post_types'] ?? []),
        'security_checks'  => maybe_serialize($fetched_data['site_info']['security_checks'] ?? []),
        'email_settings'   => maybe_serialize($fetched_data['site_info']['email_settings'] ?? []),
        'plugin_status'    => maybe_serialize($fetched_data['site_info']['plugin_status'] ?? []),
        'content_activity' => maybe_serialize($fetched_data['site_info']['content_activity'] ?? []),
        'broken_links'     => intval($fetched_data['site_info']['broken_links'] ?? 0),
        'errors_404'       => intval($fetched_data['site_info']['errors_404'] ?? 0),
    ]);
}

// Existing creation of gusm_site_info table unchanged
global $wpdb;
$table_name = $wpdb->prefix . 'gusm_site_info';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    site_id BIGINT(20) UNSIGNED NOT NULL,
    server_info LONGTEXT NULL,
    user_roles LONGTEXT NULL,
    post_types LONGTEXT NULL,
    posts_overview LONGTEXT NULL,
    last_synced DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (site_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
