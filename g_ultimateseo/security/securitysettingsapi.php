<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// security/securitysettingsapi.php

// // Include email checking functions
// require_once plugin_dir_path(__FILE__) . '../../admin/siteinfo/siteemailinfo.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteemailinfo.php';

/**
 * Ensure token exists on plugin load
 */
add_action('plugins_loaded', function () {
    if (!get_option('gsecurity_api_token')) {
        add_option('gsecurity_api_token', wp_generate_password(32, false, false));
    }
});

/**
 * Register REST API endpoints
 */
add_action('rest_api_init', function () {

    register_rest_route('gsecurity/v1', '/status', [
        'methods'             => 'GET',
        'callback'            => 'gsecurity_get_status',
        'permission_callback' => 'gsecurity_api_permission_check',
    ]);

    register_rest_route('gsecurity/v1', '/site-info', [
        'methods'             => 'GET',
        'callback'            => 'gseo_get_site_info',
        'permission_callback' => 'gsecurity_api_permission_check',
    ]);

    // ✅ New endpoint to fetch author details
    register_rest_route('gsecurity/v1', '/authors', [
        'methods'             => 'GET',
        'callback'            => 'gseo_get_authors',
        'permission_callback' => 'gsecurity_api_permission_check',
    ]);

    // ✅ New endpoint to fetch author activity
    register_rest_route('gsecurity/v1', '/author-activity', [
        'methods'             => 'GET',
        'callback'            => 'gseo_get_author_activity',
        'permission_callback' => 'gsecurity_api_permission_check',
        'args'                => [
            'author_id' => [
                'required'          => true,
                'validate_callback' => 'is_numeric'
            ],
            'start_date' => [
                'required'          => false,
                'validate_callback' => function($param) {
                    return (bool) strtotime($param);
                }
            ],
            'end_date' => [
                'required'          => false,
                'validate_callback' => function($param) {
                    return (bool) strtotime($param);
                }
            ],
        ],
    ]);

});


/**
 * Whitelist API routes if REST is globally blocked
 */
add_filter('rest_authentication_errors', 'gsecurity_allow_status_endpoint');

function gsecurity_allow_status_endpoint($result) {
    $whitelisted_routes = [
        '/wp-json/gsecurity/v1/status',
        '/wp-json/gsecurity/v1/site-info',
        '/wp-json/gsecurity/v1/authors'
    ];

    $request_uri = $_SERVER['REQUEST_URI'] ?? '';

    foreach ($whitelisted_routes as $allowed) {
        if (strpos($request_uri, $allowed) !== false) {
            return true;
        }
    }

    return $result;
}

/**
 * Get token from request
 */
function gsecurity_get_token(WP_REST_Request $request) {
    $token = $request->get_header('x-gsecurity-token');

    if (empty($token) && isset($_GET['x-gsecurity-token'])) {
        $token = sanitize_text_field($_GET['x-gsecurity-token']);
    }

    return is_string($token) ? trim($token) : '';
}

/**
 * Permission check using token
 */
function gsecurity_api_permission_check(WP_REST_Request $request) {
    $provided_token = gsecurity_get_token($request);
    $expected_token = trim(get_option('gsecurity_api_token'));

    if (!$expected_token || !$provided_token || !hash_equals($expected_token, $provided_token)) {
        return new WP_Error('unauthorized', 'Invalid or missing token', ['status' => 401]);
    }

    return true;
}

/**
 * API response with security features
 */
function gsecurity_get_status(WP_REST_Request $request) {
    $security_settings = get_option('gseo_security_settings', []);
    $features = [];

    foreach ([
        'block_dir_browsing', 'block_user_enum', 'disable_file_editing',
        'disable_login_hints', 'disable_rest_api', 'disable_xmlrpc',
        'enable_security_headers', 'limit_admin_ip', 'login_protection',
        'remove_server_header', 'remove_wp_version', 'strong_passwords'
    ] as $feature) {
        $features[$feature] = !empty($security_settings[$feature]);
    }

    $features['file_editing_disabled'] = defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT;
    $features['user_enumeration_blocked'] = defined('GSECURITY_USER_ENUM_BLOCKED') && GSECURITY_USER_ENUM_BLOCKED;

    $headers_settings = get_option('gseo_headers_settings', []);
    $security_headers = [];

    foreach ([
        'x_frame_options', 'x_content_type', 'x_xss_protection',
        'referrer_policy', 'strict_transport', 'permissions_policy', 'content_security'
    ] as $header) {
        $security_headers[$header] = !empty($headers_settings[$header]);
    }

    $csp_settings = get_option('gseo_csp_settings', []);
    $csp = [];

    foreach ([
        'csp_default_src', 'csp_script_src', 'csp_style_src',
        'csp_img_src', 'csp_font_src', 'csp_connect_src',
        'csp_media_src', 'csp_object_src', 'csp_frame_src',
        'csp_form_action', 'csp_base_uri'
    ] as $directive) {
        $csp[$directive] = [
            'apply' => !empty($csp_settings[$directive]['apply']),
            'value' => $csp_settings[$directive]['value'] ?? ''
        ];
    }

    return rest_ensure_response([
        'status'           => 'ok',
        'version'          => '1.3',
        'features'         => $features,
        'security_headers' => $security_headers,
        'csp'              => $csp
    ]);
}
function gfseo_display_email_info_api($smtp_options, $domain) {
    $smtp_email = isset($smtp_options['from_email']) ? $smtp_options['from_email'] : 'Not Configured';

    $checks = [
        'SMTP Email Configured'   => $smtp_email !== 'Not Configured',
        'SPF Record'              => gfseo_check_dns_record($domain, 'spf'),
        'DKIM Record'             => gfseo_check_dns_record('default._domainkey.' . $domain, 'dkim'),
        'DMARC Record'            => gfseo_check_dns_record('_dmarc.' . $domain, 'dmarc'),
        'Reverse DNS (rDNS)'      => check_rdns($domain),
        'SMTP Authentication'     => !empty($smtp_options['smtp_auth']),
        'SMTP TLS/SSL Encryption' => !empty($smtp_options['encryption']) && in_array(strtolower($smtp_options['encryption']), ['tls', 'ssl']),
        'IP Blacklist Check'      => check_blacklists(gethostbyname($domain)) === 'Clean ✅',
        'Email Domain Consistency'=> parse_url(site_url(), PHP_URL_HOST) === explode('@', $smtp_email)[1],
    ];

    return $checks;
}
/**
 * API response with detailed site information
 */
function gseo_get_site_info(WP_REST_Request $request) {
    global $wp_roles, $wpdb;

    $theme = wp_get_theme();
    $database_size = $wpdb->get_var("
        SELECT ROUND(SUM(data_length + index_length)/1024/1024, 2)
        FROM information_schema.TABLES
        WHERE table_schema = '{$wpdb->dbname}'
    ");

    $user_roles_data = [];
    foreach ($wp_roles->roles as $role => $details) {
        $user_query = new WP_User_Query(['role' => $role]);
        $user_roles_data[$role] = $user_query->get_total();
    }

    $post_types_data = [];
    foreach (get_post_types(['public' => true]) as $post_type) {
        $counts = wp_count_posts($post_type);
        $post_types_data[$post_type] = [
            'published' => $counts->publish,
            'draft'     => $counts->draft,
            'scheduled' => $counts->future
        ];
    }

    $recent_posts_query = new WP_Query([
        'post_type'      => 'any',
        'posts_per_page' => 5,
        'post_status'    => ['publish', 'future', 'draft'],
        'orderby'        => 'date',
        'order'          => 'DESC'
    ]);

    $recent_activity = [];
    foreach ($recent_posts_query->posts as $post) {
        $recent_activity[] = [
            'date'   => get_the_date('', $post),
            'title'  => $post->post_title,
            'url'    => get_permalink($post),
            'type'   => $post->post_type,
            'author' => get_the_author_meta('display_name', $post->post_author)
        ];
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugin_data = get_plugins();
    $plugin_status = [];

    foreach ($plugin_data as $plugin_file => $data) {
        $plugin_status[] = [
            'name'          => $data['Name'],
            'version'       => $data['Version'],
            'status'        => is_plugin_active($plugin_file) ? 'active' : 'inactive',
            'update_status' => gfseo_check_plugin_update_status($plugin_file, $data['Version'])
        ];
    }

    return rest_ensure_response([
        'server_info' => [
            'Site URL'           => get_site_url(),
            'Admin Email'        => get_bloginfo('admin_email'),
            'WordPress Version'  => get_bloginfo('version'),
            'Active Theme'       => $theme->get('Name'),
            'Theme Version'      => $theme->get('Version'),
            'PHP Version'        => phpversion(),
            'Server Software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'MySQL Version'      => $wpdb->db_version(),
            'PHP Memory Limit'   => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time') . ' sec',
            'Debug Mode'         => WP_DEBUG ? 'On' : 'Off',
            'SSL Status'         => is_ssl() ? 'Enabled' : 'Disabled',
            'Disk Free Space'    => round(disk_free_space(ABSPATH)/1024/1024/1024, 2) . ' GB',
            'Disk Total Space'   => round(disk_total_space(ABSPATH)/1024/1024/1024, 2) . ' GB',
            'Database Size'      => $database_size . ' MB',
            'Check Timestamp'    => current_time('mysql'),
        ],
        'user_roles'        => $user_roles_data,
        'post_types'        => $post_types_data,
        'recent_activity'   => $recent_activity,
        'security_checks'   => get_option('gseo_security_settings', []),
        'email_settings'    => gfseo_display_email_info_api(get_option('gseo_smtp_settings', []), parse_url(site_url(), PHP_URL_HOST)),
        'plugin_status'     => $plugin_status,
        'broken_links'      => (int)get_option('gseo_broken_links_count', 0),
        'errors_404'        => (int)get_option('gseo_404_errors_count', 0),
    ]);
}
function gseo_get_authors(WP_REST_Request $request) {
    $roles_needed = ['administrator', 'editor', 'author'];
    $users_data = [];

    foreach ($roles_needed as $role) {
        $wp_users = get_users(['role' => $role]);

        foreach ($wp_users as $user) {
            $users_data[$role][] = [
                'display_name' => $user->display_name,
                'user_email'   => $user->user_email,
                'user_login'   => $user->user_login,
                'first_name'   => get_user_meta($user->ID, 'first_name', true),
                'last_name'    => get_user_meta($user->ID, 'last_name', true),
            ];
        }

        // explicitly set empty arrays for roles with no users
        if (empty($users_data[$role])) {
            $users_data[$role] = [];
        }
    }

    return rest_ensure_response([
        'status' => 'success',
        'user_roles' => $users_data
    ]);
}


/**
 * Fetch author activity
 */
function gseo_get_author_activity(WP_REST_Request $request) {
    global $wpdb;
    $author_id = (int)$request->get_param('author_id');
    $start_date = $request->get_param('start_date') ?: date('Y-m-01');
    $end_date = $request->get_param('end_date') ?: date('Y-m-t');

    $activity = $wpdb->get_results($wpdb->prepare(
        "SELECT post_title, post_url, post_date FROM {$wpdb->prefix}gusm_author_content WHERE author_id=%d AND post_date BETWEEN %s AND %s",
        $author_id, $start_date, $end_date
    ), ARRAY_A);

    return rest_ensure_response(['status'=>'success','activity'=>$activity]);
}


/**
 * Admin page showing token and endpoint
 */
function gsecurity_api_status_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $token    = esc_html(get_option('gsecurity_api_token'));
    $endpoint = esc_url(rest_url('gsecurity/v1/status'));

    echo '<div class="wrap"><h1>GSecurity API Status</h1>';
    echo '<table class="widefat striped">';
    echo '<tr><th>Endpoint</th><td><code>' . $endpoint . '</code></td></tr>';
    echo '<tr><th>Token</th><td><code>' . $token . '</code></td></tr>';
    echo '</table>';

    echo '<form method="post">';
    wp_nonce_field('gsecurity_regenerate_token_action');
    submit_button('Regenerate Token', 'primary', 'gsecurity_regenerate_token');
    echo '</form></div>';
}