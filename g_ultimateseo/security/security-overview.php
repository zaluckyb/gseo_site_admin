<?php
function gseo_render_security_overview_page() {
    if (!defined('ABSPATH')) exit;

    // security/security-overview.php

    $security_settings = get_option('gseo_security_settings', []);
    $headers_settings = get_option('gseo_headers_settings', []);
    $csp_settings = get_option('gseo_csp_settings', []);

    $feature_labels = [
        'login_protection'        => 'Login Attempt Protection',
        'disable_xmlrpc'          => 'Disable XML-RPC',
        'limit_admin_ip'          => 'Limit Admin by IP',
        'disable_file_editing'    => 'Disable File Editing',
        'remove_wp_version'       => 'Remove WP Version',
        'block_user_enum'         => 'Block User Enumeration',
        'strong_passwords'        => 'Strong Passwords',
        'disable_login_hints'     => 'Disable Login Hints',
        'disable_rest_api'        => 'Disable REST API',
        'block_dir_browsing'      => 'Block Directory Browsing',
        'enable_security_headers' => 'Enable Security Headers',
        'remove_server_header'    => 'Remove Server Header',
    ];

    $security_headers_labels = [
        'x_frame_options'      => 'X-Frame-Options',
        'x_content_type'       => 'X-Content-Type-Options',
        'x_xss_protection'     => 'X-XSS-Protection',
        'referrer_policy'      => 'Referrer-Policy',
        'strict_transport'     => 'Strict-Transport-Security',
        'permissions_policy'   => 'Permissions-Policy',
        'content_security'     => 'Content-Security-Policy',
    ];

    echo '<div class="wrap">';
    echo '<h1>🔒 G Ultimate SEO Security Overview</h1>';
    ?>

    <div class="gseo-security-summary">
        <div class="summary-header">
            <span>Site URL: <a href="<?php echo esc_url(get_site_url()); ?>" target="_blank"><?php echo esc_html(get_site_url()); ?></a></span>
            <span>Status: <strong>Connected</strong></span>
        </div>

        <div class="security-grid">
            <div class="security-section">
                <h2>Main Security Settings</h2>
                <ul class="security-list">
                    <?php foreach ($feature_labels as $key => $label): ?>
                        <li>
                            <span class="label"><?php echo esc_html($label); ?></span>
                            <span class="status"><?php echo !empty($security_settings[$key]) ? '✅' : '❌'; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="security-section">
                <h2>Security Headers</h2>
                <ul class="security-list">
                    <?php foreach ($security_headers_labels as $key => $label): ?>
                        <li>
                            <span class="label"><?php echo esc_html($label); ?></span>
                            <span class="status"><?php echo !empty($headers_settings[$key]) ? '✅' : '❌'; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="security-section">
                <h2>Content Security Policy</h2>
                <ul class="csp-list">
                    <?php foreach ($csp_settings as $directive => $directive_settings): ?>
                        <?php
                        $is_applied = !empty($directive_settings['apply']);
                        $icon = $is_applied ? '✅' : '❌';
                        $display_value = $is_applied ? esc_html($directive_settings['value']) : '(disabled)';
                        ?>
                        <li>
                            <span class="csp-directive"><?php echo esc_html($directive); ?></span>
                            <span class="csp-status"><?php echo $icon; ?></span>
                            <?php if($is_applied): ?>
                                <div class="csp-value"><?php echo esc_html($display_value); ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php
    echo '</div>';
}
