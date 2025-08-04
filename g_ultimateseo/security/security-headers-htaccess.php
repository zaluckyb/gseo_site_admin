<?php
if (!defined('ABSPATH')) exit;

// security/disable-xmlrpc.php

function gseo_render_htaccess_snippet_page() {
    $headers = get_option('gseo_headers_settings', []);
    $security_settings = get_option('gseo_security_settings', []);
    $csp = get_option('gseo_csp_settings', []);

    $directives = [
        'csp_default_src' => 'default-src',
        'csp_script_src'  => 'script-src',
        'csp_style_src'   => 'style-src',
        'csp_img_src'     => 'img-src',
        'csp_font_src'    => 'font-src',
        'csp_connect_src' => 'connect-src',
        'csp_media_src'   => 'media-src',
        'csp_object_src'  => 'object-src',
        'csp_frame_src'   => 'frame-src',
        'csp_form_action' => 'form-action',
        'csp_base_uri'    => 'base-uri',
    ];

    // Build .htaccess string
    $htaccess = "<IfModule mod_headers.c>\n";

    if (!empty($headers['x_frame_options'])) {
        $htaccess .= "    Header always set X-Frame-Options \"SAMEORIGIN\"\n";
    }
    if (!empty($headers['x_content_type'])) {
        $htaccess .= "    Header always set X-Content-Type-Options \"nosniff\"\n";
    }
    if (!empty($headers['x_xss_protection'])) {
        $htaccess .= "    Header always set X-XSS-Protection \"1; mode=block\"\n";
    }
    if (!empty($headers['referrer_policy'])) {
        $ref = $headers['referrer_policy_custom'] ?? 'strict-origin-when-cross-origin';
        $htaccess .= "    Header always set Referrer-Policy \"$ref\"\n";
    }
    if (!empty($headers['strict_transport'])) {
        $htaccess .= "    Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\"\n";
    }
    if (!empty($headers['permissions_policy'])) {
        $htaccess .= "    Header always set Permissions-Policy \"geolocation=(), microphone=(), camera=()\"\n";
    }

    if (!empty($headers['content_security'])) {
        $csp_parts = [];
        foreach ($directives as $key => $directive) {
            if (!empty($csp[$key]['apply'])) {
                $val = trim($csp[$key]['value']);
                $csp_parts[] = "{$directive} {$val}";
            }
        }
        if (!empty($csp_parts)) {
            $csp_combined = implode('; ', $csp_parts);
            $htaccess .= "    Header always set Content-Security-Policy \"$csp_combined\"\n";
        }
    }

    // Add Remove Server Header
    if (!empty($security_settings['remove_server_header'])) {
        $htaccess .= "    Header unset Server\n";
    }

    $htaccess .= "</IfModule>";

    // Handle POST to write .htaccess
    if (isset($_POST['gseo_write_htaccess']) && check_admin_referer('gseo_write_htaccess_action')) {
        $result = gseo_write_security_headers_to_htaccess($htaccess);
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error"><p>❌ ' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>✅ .htaccess updated successfully.</p></div>';
        }
    }
    ?>

    <div class="wrap">
        <h1>🛠️ .htaccess Generator for Security Headers</h1>
        <p style="font-size:15px;color:#333;">
            Below is a dynamically generated snippet you can copy and paste into your site’s <code>.htaccess</code> file (for Apache),
            or use the button below to write it directly.
        </p>
        <?php
        $htaccess_path = ABSPATH . '.htaccess';
        $htaccess_current = '';

        if (file_exists($htaccess_path)) {
            if (is_readable($htaccess_path)) {
                $htaccess_current = file_get_contents($htaccess_path);
            } else {
                $htaccess_current = '❌ .htaccess file exists but is not readable.';
            }
        } else {
            $htaccess_current = '⚠️ .htaccess file does not exist.';
        }
        ?>

        <div style="margin-top: 30px;">
            <h2>📄 Current .htaccess File</h2>
            <textarea readonly style="width:100%; height:300px; font-family:monospace; padding:15px; background:#fff5e5; border:1px solid #ccc; border-radius:6px;"><?php echo esc_textarea($htaccess_current); ?></textarea>
        </div>

        <textarea style="width:100%; height:400px; font-family:monospace; padding:15px; background:#f7f7f7; border:1px solid #ccc; border-radius:6px;" readonly><?php echo esc_textarea($htaccess); ?></textarea>

        <form method="post" style="margin-top:20px;">
            <?php wp_nonce_field('gseo_write_htaccess_action'); ?>
            <input type="submit" name="gseo_write_htaccess" class="button button-primary" value="✍️ Write to .htaccess">
        </form>

        <p style="margin-top:20px;">
            <a href="<?php echo admin_url('admin.php?page=gseo-security-headers'); ?>" class="button">⬅️ Back to Security Headers</a>
        </p>
    </div>

<?php
}