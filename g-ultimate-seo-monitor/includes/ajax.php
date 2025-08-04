<?php
// includes/ajax.php

add_action('wp_ajax_gseo_manual_sync_site', 'gseo_manual_sync_site_callback');

function gseo_manual_sync_site_callback() {
    check_ajax_referer('gseo_sync_nonce', 'security');

    $site_id = intval($_POST['site_id'] ?? 0);
    if (!$site_id) {
        wp_send_json_error('Missing site ID');
    }

    global $wpdb;
    $site = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gusm_sites WHERE id = %d",
        $site_id
    ), ARRAY_A);

    if (!$site) {
        wp_send_json_error('Site not found');
    }

    // Fetch security status
    $status_result = gseo_fetch_site_status($site['url'], $site['token'], 'status');
    if ($status_result['status'] !== 200 || empty($status_result['body'])) {
        $wpdb->update($wpdb->prefix . 'gusm_sites', [
            'status' => 'failed',
            'last_checked' => current_time('mysql'),
        ], ['id' => $site_id]);

        wp_send_json_error('Failed fetching security data from remote site.');
    }

    // Fetch detailed site info (includes email settings and plugin status)
    $site_info_result = gseo_fetch_site_status($site['url'], $site['token'], 'site-info');
    if ($site_info_result['status'] !== 200 || empty($site_info_result['body'])) {
        $wpdb->update($wpdb->prefix . 'gusm_sites', [
            'status' => 'failed',
            'last_checked' => current_time('mysql'),
        ], ['id' => $site_id]);

        wp_send_json_error('Failed fetching site info from remote site.');
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

    wp_send_json_success('Sync successful');
}


// Handle site deletion clearly
add_action('wp_ajax_gseo_delete_site', function () {
    check_ajax_referer('gseo_delete_site_nonce', 'security');

    $site_id = intval($_POST['site_id'] ?? 0);
    if (!$site_id) wp_send_json_error('Missing site ID');

    global $wpdb;
    $deleted = $wpdb->delete($wpdb->prefix . 'gusm_sites', ['id' => $site_id]);

    if ($deleted) {
        wp_send_json_success('Site deleted successfully');
    } else {
        wp_send_json_error('Failed to delete site');
    }
});

add_action('wp_ajax_gusm_sync_all_authors', 'gusm_sync_all_authors_callback');

function gusm_sync_all_authors_callback() {
    check_ajax_referer('gusm_sync_authors_nonce', 'security');

    global $wpdb;
    $sites = gusm_get_all_sites();
    $authors_table = $wpdb->prefix . 'gusm_authors';
    $author_content_table = $wpdb->prefix . 'gusm_author_content';

    $roles_to_import = ['administrator', 'editor', 'author'];
    $added_count = 0;

    foreach ($sites as $site) {
        $site_id = (int)$site['id'];
        $url = $site['url'];
        $token = $site['token'];

        // ✅ Explicitly calling the correct endpoint ('authors')
        $site_info = gseo_fetch_site_status($url, $token, 'authors');

        if ($site_info['status'] !== 200 || empty($site_info['body']['user_roles'])) {
            continue; // Skip problematic site
        }

        foreach ($roles_to_import as $role) {
            if (!empty($site_info['body']['user_roles'][$role])) {
                foreach ($site_info['body']['user_roles'][$role] as $user) {
                    $author_name = sanitize_text_field($user['display_name'] ?? $user['user_login']);
                    $author_email = sanitize_email($user['user_email']);

                    if (empty($author_email)) {
                        continue;
                    }

                    // Check if author already exists
                    $author_id = $wpdb->get_var($wpdb->prepare("
                        SELECT id FROM $authors_table WHERE emails = %s LIMIT 1
                    ", $author_email));

                    if ($author_id) {
                        // Update existing author name
                        $wpdb->update($authors_table, [
                            'name' => $author_name
                        ], ['id' => $author_id]);
                    } else {
                        // Insert new author if doesn't exist
                        $wpdb->insert($authors_table, [
                            'name' => $author_name,
                            'emails' => $author_email
                        ]);
                        $author_id = $wpdb->insert_id;
                    }

                    // Check if author-site-role combination already exists
                    $existing_entry = $wpdb->get_var($wpdb->prepare("
                        SELECT COUNT(*) FROM $author_content_table
                        WHERE author_id = %d AND site_id = %d AND role = %s
                    ", $author_id, $site_id, $role));

                    if ($existing_entry > 0) {
                        continue; // Skip duplicates
                    }

                    // ✅ Explicitly inserting author-site-role combination
                    $wpdb->insert($author_content_table, [
                        'author_id' => $author_id,
                        'site_id'   => $site_id,
                        'post_title'=> 'Imported via Sync',
                        'post_url'  => $url,
                        'post_date' => current_time('mysql'),
                        'role'      => $role
                    ]);

                    $added_count++;
                }
            }
        }
    }

    wp_send_json_success("$added_count authors successfully synchronized.");
}