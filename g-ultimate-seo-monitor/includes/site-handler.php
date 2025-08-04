<?php

// Ensure DB logic is available
if (!function_exists('gusm_add_site_to_db')) {
    require_once __DIR__ . '/db/sites.php';
}
// includes/site-handler.php



/**
 * Handles form submission for adding a new site
 */
function gseo_handle_add_site() {
    if (!isset($_POST['gseo_add_site'], $_POST['gseo_add_site_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['gseo_add_site_nonce'], 'gseo_add_site_action')) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed.', 'gusm') . '</p></div>';
        return;
    }

    $name  = sanitize_text_field($_POST['new_site_name']);
    $url   = esc_url_raw($_POST['new_site_url']);
    $token = sanitize_text_field($_POST['new_site_token']);

    global $wpdb;

    // Insert the new site into gusm_sites
    $wpdb->insert($wpdb->prefix . 'gusm_sites', [
        'name'         => $name,
        'url'          => $url,
        'token'        => $token,
        'status'       => 'unknown',
        'last_checked' => current_time('mysql'),
    ]);

    $site_id = $wpdb->insert_id;

    if (!$site_id) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Failed to add site.', 'gusm') . '</p></div>';
        return;
    }

    // Fetch user roles from the remote site
    $site_info = gseo_fetch_site_status($url, $token, 'site-info');

    if ($site_info['status'] !== 200 || empty($site_info['body']['user_roles'])) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Failed fetching user roles from the site.', 'gusm') . '</p></div>';
        return;
    }

    $roles_to_import = ['administrator', 'editor', 'author'];
    $authors_table   = $wpdb->prefix . 'gusm_authors';

    foreach ($roles_to_import as $role) {
        if (!empty($site_info['body']['user_roles'][$role])) {
            foreach ($site_info['body']['user_roles'][$role] as $user) {
                $author_name = sanitize_text_field($user['display_name'] ?? $user['user_login']);
                $author_email = sanitize_email($user['user_email']);
    
                if (empty($author_email)) {
                    continue;
                }
    
                // Check if author already exists in gusm_authors
                $author_id = $wpdb->get_var($wpdb->prepare("
                    SELECT id FROM $authors_table WHERE emails = %s LIMIT 1
                ", $author_email));
    
                if ($author_id) {
                    // ✅ Update existing author's name explicitly
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
    
                // Check if this author-site-role combination already exists
                $existing_entry = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM $author_content_table
                    WHERE author_id = %d AND site_id = %d AND role = %s
                ", $author_id, $site_id, $role));
    
                if ($existing_entry > 0) {
                    // Optionally update timestamp or other fields here if desired
                    continue; // Skip if author-site-role already exists
                }
    
                // Record author-site-role combination clearly
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

    echo '<div class="notice notice-success"><p>' . esc_html__('Site and authors successfully added!', 'gusm') . '</p></div>';
}
