<?php
// Near the top of securitysettings.php or right above gseo_security_settings_page()
// admin/pages/security-settings.php
function gseo_security_settings_page() {
    gseo_handle_delete_site();

    if (isset($_POST['gseo_sync_now']) && check_admin_referer('gseo_sync_now_action', 'gseo_sync_now_nonce')) {
        do_action('gusm_cron_sync_sites');
        echo '<div class="notice notice-success"><p>✅ Security settings synchronized successfully!</p></div>';
    }

    // Load sites from DB
    $sites = gusm_get_all_sites();

    echo '<div class="wrap"><h1>Security Settings</h1>';

    // Sync Now Button
    echo '<form method="post">';
        wp_nonce_field('gseo_sync_now_action', 'gseo_sync_now_nonce');
        submit_button('Sync Now', 'primary', 'gseo_sync_now', false, ['style' => 'margin-bottom: 15px;']);
    echo '</form>';

    gseo_render_sites_table($sites);
    echo '</div>';
}


function gseo_handle_delete_site() {
    if (
        isset($_POST['gseo_delete_site'], $_POST['delete_site_url']) &&
        check_admin_referer('gseo_delete_site_action', 'gseo_delete_site_nonce')
    ) {
        $delete_url = esc_url_raw($_POST['delete_site_url']);

        global $wpdb;
        $table = $wpdb->prefix . 'gusm_sites';

        // ✅ Delete from the database, not from the option
        $deleted = $wpdb->delete($table, ['url' => $delete_url]);

        if ($deleted) {
            add_action('admin_notices', function () use ($delete_url) {
                echo '<div class="notice notice-success"><p>Deleted site: ' . esc_html($delete_url) . '</p></div>';
            });
        }
    }
}
