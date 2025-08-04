<?php
if (!defined('ABSPATH')) exit;

// /security/security-headers-settings.php

function gseo_render_security_headers_settings() {
    // Save logic
    if (isset($_POST['gseo_save_headers_settings'])) {
        check_admin_referer('gseo_headers_settings_nonce');

        // Unslash to avoid extra backslashes for 'self'
        $post_data = wp_unslash($_POST);

        // Optional global toggle
        $enable_security_headers = !empty($post_data['enable_security_headers']) ? 1 : 0;

        // Basic per-header checkboxes
        $headers = [
            'x_frame_options'    => !empty($post_data['x_frame_options']) ? 1 : 0,
            'x_content_type'     => !empty($post_data['x_content_type']) ? 1 : 0,
            'x_xss_protection'   => !empty($post_data['x_xss_protection']) ? 1 : 0,
            'referrer_policy'    => !empty($post_data['referrer_policy']) ? 1 : 0,
            'strict_transport'   => !empty($post_data['strict_transport']) ? 1 : 0,
            'permissions_policy' => !empty($post_data['permissions_policy']) ? 1 : 0,
            'content_security'   => !empty($post_data['content_security']) ? 1 : 0, // toggles CSP on/off
        ];

        // Handle Referrer-Policy custom logic (radio + text field)
        $referrer_policy_custom = sanitize_text_field($post_data['referrer_policy_custom'] ?? '');
        if ($referrer_policy_custom === 'custom') {
            $headers['referrer_policy_custom'] = sanitize_text_field($post_data['referrer_policy_custom_input'] ?? '');
        } else {
            $headers['referrer_policy_custom'] = $referrer_policy_custom;
        }

        // Store global toggle in same option if you wish
        $headers['enable_security_headers'] = $enable_security_headers;

        // Save
        update_option('gseo_headers_settings', $headers);

        echo '<div class="notice notice-success is-dismissible"><p>✅ Security headers updated successfully.</p></div>';
    }

    // Retrieve saved settings
    $headers = get_option('gseo_headers_settings', []);
    $is_enabled = !empty($headers['enable_security_headers']);
    ?>
    <div class="wrap">
        <h1>🛡️ G_UltimateSEO Security Headers Settings</h1>

        <p style="font-size:15px; color:#333; background-color:#f3f9ff; padding:15px; border-left:4px solid #2271b1; border-radius:4px;">
            <strong>Why are Security Headers Important?</strong><br>
            Security headers protect your website and users from common attacks by instructing browsers how to handle aspects of security.
            You can test your security headers using the
            <a href="https://securityheaders.com/" target="_blank" rel="noopener noreferrer">Security Headers Testing Tool</a>. You can also register an account with <a href="https://securityscorecard.com/" target="_blank" rel="noopener noreferrer">Security Scorecard</a>
        </p>

        <form method="post">
            <?php wp_nonce_field('gseo_headers_settings_nonce'); ?>

            <!-- (Optional) A global toggle checkbox to enable or disable all headers. -->
            <p>
                <label>
                    <input type="checkbox" name="enable_security_headers" value="1" <?php checked($is_enabled, 1); ?>>
                    <strong>Enable Security Headers Globally?</strong>
                </label>
            </p>

            <?php
            /**
             * Updated: Merged each header's short & long descriptions
             * into a single string, so everything appears in one place.
             */
            $header_settings = [
                'x_frame_options' => [
                    'X-Frame-Options',
                    // Combined short + long:
                    'Prevents clickjacking attacks by restricting iframe embedding. ' .
                    'X-Frame-Options tells the browser whether you want to allow your site to be framed or not. ' .
                    'By preventing a browser from framing your site you can defend against attacks like clickjacking.',
                    9
                ],
                'x_content_type' => [
                    'X-Content-Type-Options',
                    // Combined short + long:
                    'Prevents MIME-sniffing attacks. ' .
                    'X-Content-Type-Options stops a browser from trying to MIME-sniff the content type and forces it to ' .
                    'stick with the declared content-type. The only valid value is "X-Content-Type-Options: nosniff".',
                    9
                ],
                'x_xss_protection' => [
                    'X-XSS-Protection',
                    // Combined short + long:
                    'Legacy header that helps prevent XSS attacks. ' .
                    'X-XSS-Protection configures the XSS Auditor in older browsers. The recommended value was ' .
                    '"X-XSS-Protection: 1; mode=block," but you should now rely on Content Security Policy instead.',
                    7
                ],
                'referrer_policy' => [
                    'Referrer-Policy',
                    // Combined short + long:
                    'Controls how much referrer info is sent. ' .
                    'Referrer Policy allows a site to control how much information the browser includes with ' .
                    'navigations away from a document and should be set by all sites.',
                    8
                ],
                'strict_transport' => [
                    'Strict-Transport-Security',
                    // Combined short + long:
                    'Forces browsers to use HTTPS. ' .
                    'HTTP Strict Transport Security is an excellent feature that strengthens your TLS implementation ' .
                    'by forcing the browser to use HTTPS.',
                    9
                ],
                'permissions_policy' => [
                    'Permissions-Policy',
                    // Combined short + long:
                    'Restricts browser features (geolocation, etc). ' .
                    'Permissions Policy is a new header that allows a site to control which features and APIs ' .
                    'can be used in the browser.',
                    8
                ],
                'content_security' => [
                    'Content-Security-Policy',
                    // Combined short + long:
                    'Activates CSP (configure separately). ' .
                    'Content Security Policy helps protect your site from XSS attacks by whitelisting sources of approved content. ' .
                    'Consider signing up for a free account on Report URI to collect CSP violation reports.',
                    9
                ],
            ];
            ?>

            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(350px,1fr)); gap:20px; margin-top:30px;">
                <?php foreach ($header_settings as $key => [$label, $description, $score]) : ?>
                    <?php
                    // We'll build any dynamic form inputs (like radio groups) in $customInput
                    $customInput = '';

                    // If it's referrer_policy, show the radio group
                    if ($key === 'referrer_policy') {
                        $savedReferrer = $headers['referrer_policy_custom'] ?? '';
                        $is_custom = !in_array($savedReferrer, [
                            'no-referrer',
                            'strict-origin-when-cross-origin',
                            'origin-when-cross-origin'
                        ]);
                        $custom_value = $is_custom ? $savedReferrer : '';

                        ob_start(); ?>
                        <div style="margin-top:10px;">
                            <label>
                                <input type="radio" name="referrer_policy_custom" value="no-referrer"
                                       <?php checked($savedReferrer, 'no-referrer'); ?>>
                                no-referrer
                            </label><br>
                            <label>
                                <input type="radio" name="referrer_policy_custom" value="strict-origin-when-cross-origin"
                                       <?php checked($savedReferrer, 'strict-origin-when-cross-origin'); ?>>
                                strict-origin-when-cross-origin
                            </label><br>
                            <label>
                                <input type="radio" name="referrer_policy_custom" value="origin-when-cross-origin"
                                       <?php checked($savedReferrer, 'origin-when-cross-origin'); ?>>
                                origin-when-cross-origin
                            </label><br>
                            <label>
                                <input type="radio" name="referrer_policy_custom" value="custom"
                                       <?php checked($is_custom, true); ?>>
                                Custom:
                                <input type="text" name="referrer_policy_custom_input"
                                       value="<?php echo esc_attr($custom_value); ?>"
                                       style="width:100%; margin-top:5px;">
                            </label>
                        </div>
                        <?php
                        $customInput = ob_get_clean();
                    }
                    ?>

                    <div style="border:1px solid #ccd0d4; padding:20px; border-radius:8px; background-color:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <label style="display:flex; align-items:center;">
                            <!-- The per-header checkbox -->
                            <input type="checkbox" name="<?php echo esc_attr($key); ?>"
                                <?php checked(1, $headers[$key] ?? 0); ?>
                                style="margin-right:10px;transform:scale(1.5);">
                            <span style="font-weight:600; font-size:16px;">
                                <?php echo esc_html($label); ?>
                            </span>
                        </label>

                        <p style="color:#646970; margin-top:10px;">
                            <?php echo esc_html($description); ?>
                        </p>

                        <!-- If this header has dynamic inputs (like referrer_policy), show it here -->
                        <?php echo $customInput; ?>

                        <span style="display:inline-block; background-color:#2271b1; color:#fff; padding:2px 8px; border-radius:4px; font-size:12px; margin-top:10px;">
                            Score: <?php echo (int) $score; ?>/10
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:30px;">
                <a href="<?php echo admin_url('admin.php?page=gseo-security-settings'); ?>" class="button">⬅️ Back to Security Settings</a>
                <a href="<?php echo admin_url('admin.php?page=gseo-csp-settings'); ?>" class="button button-secondary">🔒 Customize Content Security Policy</a>
                <input type="submit" name="gseo_save_headers_settings" class="button button-primary button-large" value="💾 Save Security Headers">
            </p>
        </form>
    </div>
    <?php
}
