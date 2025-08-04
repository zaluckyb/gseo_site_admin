<?php

// // includes/helpers.php


function gseo_log($message) {
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('[GSEO DEBUG] ' . print_r($message, true));
    }
}

function gseo_get_feature_labels() {
    $labels = [
        'block_dir_browsing' => 'Block Directory Browsing',
        'block_user_enum' => 'Block User Enumeration',
        'disable_file_editing' => 'Disable File Editing (UI)',
        'disable_login_hints' => 'Disable Login Hints',
        'disable_rest_api' => 'Disable REST API',
        'disable_xmlrpc' => 'Disable XML-RPC',
        'enable_security_headers' => 'Enable Security Headers',
        'limit_admin_ip' => 'Limit Admin Access by IP',
        'login_protection' => 'Login Attempt Protection',
        'remove_server_header' => 'Remove Server Header',
        'remove_wp_version' => 'Remove WP Version',
        'strong_passwords' => 'Strong Passwords',
        'file_editing_disabled' => 'File Editing Constant',
        'user_enumeration_blocked' => 'User Enum Constant Block',
    ];
    asort($labels);
    return $labels;
}

function gusm_add_default_security_settings($site_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'gusm_security_settings';

    if (!function_exists('gseo_get_feature_labels')) {
        require_once plugin_dir_path(__FILE__) . '/helpers.php';
    }

    $features = array_keys(gseo_get_feature_labels());

    foreach ($features as $feature) {
        // CHECK CLEARLY IF ALREADY EXISTS BEFORE INSERTING
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE site_id = %d AND option_name = %s",
            $site_id, sanitize_key($feature)
        ));

        if (!$exists) {
            $wpdb->insert($table, [
                'site_id'      => $site_id,
                'option_name'  => sanitize_key($feature),
                'option_value' => 0, // default off
            ]);
            gseo_log("✅ INSERTED default $feature for site_id $site_id");
        } else {
            gseo_log("✅ SKIPPED duplicate default $feature for site_id $site_id");
        }
    }
}



// includes/helpers.php
function gseo_get_filtered_site_rows($sites, $feature_labels) {
    $sort_by = $_GET['sort_by'] ?? 'url';
    $filter_status = $_GET['status'] ?? 'all';
    $current_page = max(1, intval($_GET['paged'] ?? 1));
    $per_page = intval($_GET['per_page'] ?? 10);

    // Validate per_page option
    if (!in_array($per_page, [5, 10, 25, 50, 100])) {
        $per_page = 10; // fallback to default
    }

    // your existing filtering and sorting logic here...
    $rows = [];
    foreach ($sites as $site) {
        $rows[] = [
            'id'     => $site['id'],
            'url'    => untrailingslashit($site['url']),
            'token'  => $site['token'],
            'status' => $site['status'],
        ];
    }

    if ($filter_status !== 'all') {
        $rows = array_filter($rows, function ($row) use ($filter_status) {
            return match ($filter_status) {
                'connected' => $row['status'] === 'connected',
                'unauthorized' => $row['status'] === 'unauthorized',
                'failed' => $row['status'] === 'failed',
                default => true,
            };
        });
    }

    usort($rows, function ($a, $b) use ($sort_by) {
        return $sort_by === 'status'
            ? strcmp($a['status'], $b['status'])
            : strcmp($a['url'], $b['url']);
    });

    $total_items = count($rows);
    $total_pages = ceil($total_items / $per_page);
    $paged = array_slice($rows, ($current_page - 1) * $per_page, $per_page);

    return [
        'paged' => $paged,
        'total_pages' => $total_pages,
        'per_page' => $per_page,
    ];
}

// includes/helpers.php
if (!function_exists('gseo_fetch_site_status')) {
    function gseo_fetch_site_status($url, $token, $endpoint = 'status') {
        $endpoint_url = trailingslashit($url) . "wp-json/gsecurity/v1/{$endpoint}?x-gsecurity-token=" . urlencode($token);

        $response = wp_remote_get($endpoint_url, [
            'timeout' => 20,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return [
                'status' => 0,
                'body'   => [],
                'error'  => $response->get_error_message(),
            ];
        }

        return [
            'status' => wp_remote_retrieve_response_code($response),
            'body'   => json_decode(wp_remote_retrieve_body($response), true),
        ];
    }
}




function gusm_get_security_option($site_id, $option_name) {
    global $wpdb;
    $table = $wpdb->prefix . 'gusm_security_settings';

    $value = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM $table WHERE site_id = %d AND option_name = %s",
        $site_id, $option_name
    ));

    // Only cast '_apply' fields to int; keep '_value' fields as string
    if (strpos($option_name, '_apply') !== false) {
        return (int)$value;
    }

    // Return strings clearly for '_value' fields
    return $value;
}


function gusm_save_security_option($site_id, $option_name, $value) {
    global $wpdb;

    if (!$site_id || $site_id <= 0) {
        gseo_log("❌ Invalid site_id: {$site_id} for {$option_name}");
        return;
    }

    $table = $wpdb->prefix . 'gusm_security_settings';

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE site_id = %d AND option_name = %s",
        $site_id, sanitize_key($option_name)
    ));

    if ($existing) {
        $wpdb->update(
            $table,
            [
                'option_value' => $value,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $existing]
        );
        gseo_log("🔵 Updated {$option_name} = {$value} for site_id {$site_id}");
    } else {
        $wpdb->insert(
            $table,
            [
                'site_id' => $site_id,
                'option_name' => sanitize_key($option_name),
                'option_value' => $value,
                'updated_at' => current_time('mysql'),
            ]
        );
        gseo_log("🟢 Inserted {$option_name} = {$value} for site_id {$site_id}");
    }
}