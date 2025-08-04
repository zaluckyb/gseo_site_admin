<?php
if (!defined('ABSPATH')) exit;

// security/securitysettings.php

function gseo_render_security_settings() {
    if (isset($_POST['gseo_save_security_settings'])) {
        if (check_admin_referer('gseo_security_settings_nonce')) {

            $options = [
                'login_protection'        => !empty($_POST['login_protection']) ? 1 : 0,
                'disable_xmlrpc'          => !empty($_POST['disable_xmlrpc']) ? 1 : 0,
                'limit_admin_ip'          => !empty($_POST['limit_admin_ip']) ? 1 : 0,
                'disable_file_editing'    => !empty($_POST['disable_file_editing']) ? 1 : 0,
                'remove_wp_version'       => !empty($_POST['remove_wp_version']) ? 1 : 0,
                'block_user_enum'         => !empty($_POST['block_user_enum']) ? 1 : 0,
                'strong_passwords'        => !empty($_POST['strong_passwords']) ? 1 : 0,
                'disable_login_hints'     => !empty($_POST['disable_login_hints']) ? 1 : 0,
                'disable_rest_api'        => !empty($_POST['disable_rest_api']) ? 1 : 0,
                'block_dir_browsing'      => !empty($_POST['block_dir_browsing']) ? 1 : 0,
                'enable_security_headers' => !empty($_POST['enable_security_headers']) ? 1 : 0,
                'remove_server_header'    => !empty($_POST['remove_server_header']) ? 1 : 0,
            ];

            update_option('gseo_security_settings', $options);

            if (!empty($_POST['admin_allowed_ips'])) {
                $sanitized_ips = sanitize_text_field($_POST['admin_allowed_ips']);
                update_option('admin_allowed_ips', $sanitized_ips);
            } else {
                update_option('admin_allowed_ips', '');
            }

            echo '<div class="notice notice-success is-dismissible"><p>✅ Security settings updated successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>❌ Nonce verification failed. Please try again.</p></div>';
        }
    }

    $options = get_option('gseo_security_settings', []);
    $allowed_ips = get_option('admin_allowed_ips', '');
?>

<div class="wrap">
    <h1>🛡️ G_UltimateSEO Security Settings</h1>
    <form method="post" action="">
        <?php wp_nonce_field('gseo_security_settings_nonce'); ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(350px,1fr)); gap:20px;margin-top:30px;">
            <?php
            $settings = [
                'login_protection'        => ['Login Attempt Protection', 'Prevents brute-force login attacks.', 9],
                'disable_xmlrpc'          => ['Disable XML-RPC', 'Disables XML-RPC endpoint for enhanced security.', 8],
                'limit_admin_ip'          => ['Limit Admin by IP', 'Restricts admin area access to specific IPs.', 9],
                'disable_file_editing'    => ['Disable File Editing', 'Prevents theme/plugin file edits.', 7],
                'remove_wp_version'       => ['Remove WP Version', 'Hides WordPress version number.', 6],
                'block_user_enum'         => ['Block User Enumeration', 'Prevents username enumeration.', 8],
                'strong_passwords'        => ['Strong Passwords', 'Enforces strong passwords for users.', 9],
                'disable_login_hints'     => ['Disable Login Hints', 'Stops revealing login hints.', 7],
                'disable_rest_api'        => ['Disable REST API (Anonymous)', 'Blocks REST API for anonymous users.', 8],
                'block_dir_browsing'      => ['Block Directory Browsing', 'Prevents directory listing.', 9],
                'enable_security_headers' => ['Enable Security Headers', 'Activates chosen security headers.', 9],
                'remove_server_header'    => ['Remove Server Header', 'Prevents server software from being exposed in HTTP responses.', 9],
            ];

            uasort($settings, function ($a, $b) {
                return strcasecmp($a[0], $b[0]); // Sort by label, case-insensitive
            });
            
            foreach ($settings as $key => [$label, $description, $score]) {
             ?>
                <div style="border:1px solid #ccd0d4; padding:20px; border-radius:8px; background:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <label style="display:flex; align-items:center;">
                        <input type="checkbox" name="<?php echo esc_attr($key); ?>" 
                               <?php checked(!empty($options[$key]), true); ?> 
                               style="margin-right:10px;transform:scale(1.5);">
                        <span style="font-weight:600;font-size:16px;">
                            <?php echo esc_html($label); ?>
                        </span>
                    </label>
                    <p style="color:#646970;margin-top:10px;">
                        <?php echo esc_html($description); ?>
                    </p>
                    <span style="background:#2271b1;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;">
                        Score: <?php echo esc_html($score); ?>/10
                    </span>
                </div>
            <?php } ?>
        </div>

        <div style="margin-top:20px;">
            <label for="admin_allowed_ips">
                <strong>Allowed Admin IP Addresses</strong>
                <br>
                <small>Enter IP addresses allowed to access the admin area. Separate multiple IPs with commas.</small>
            </label><br>
            <input type="text" id="admin_allowed_ips" name="admin_allowed_ips" style="width:100%;padding:8px;margin-top:5px;"
                   value="<?php echo esc_attr($allowed_ips); ?>" 
                   placeholder="e.g. 123.456.789.001, 987.654.321.000">
        </div>

        <p style="margin-top:30px;">
            <a href="<?php echo admin_url('admin.php?page=gseo-security-headers'); ?>" class="button">
                ⚙️ Customize Security Headers
            </a>
            <input type="submit" name="gseo_save_security_settings" class="button button-primary button-large" value="💾 Save Security Settings">
        </p>
    </form>
</div>
<?php
}
