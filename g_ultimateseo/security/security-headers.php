<?php
add_action('send_headers', 'gseo_apply_security_headers', 100);

// security/security-headers.php

function gseo_apply_security_headers() {
    $options = get_option('gseo_security_settings', []);

    if (!empty($options['enable_security_headers'])) {
        $headers = get_option('gseo_headers_settings', []);

        // X-Frame-Options
        if (!empty($headers['x_frame_options'])) {
            header_remove('X-Frame-Options');
            header('X-Frame-Options: SAMEORIGIN');
        } else {
            header_remove('X-Frame-Options');
        }

        // X-Content-Type-Options
        if (!empty($headers['x_content_type'])) {
            header_remove('X-Content-Type-Options');
            header('X-Content-Type-Options: nosniff');
        } else {
            header_remove('X-Content-Type-Options');
        }

        // X-XSS-Protection
        if (!empty($headers['x_xss_protection'])) {
            header_remove('X-XSS-Protection');
            header('X-XSS-Protection: 1; mode=block');
        } else {
            header_remove('X-XSS-Protection');
        }

        // Referrer-Policy
        if (!empty($headers['referrer_policy'])) {
            header_remove('Referrer-Policy');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        } else {
            header_remove('Referrer-Policy');
        }

        // Strict-Transport-Security
        if (!empty($headers['strict_transport'])) {
            header_remove('Strict-Transport-Security');
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        } else {
            header_remove('Strict-Transport-Security');
        }

        // Permissions-Policy
        if (!empty($headers['permissions_policy'])) {
            header_remove('Permissions-Policy');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        } else {
            header_remove('Permissions-Policy');
        }

        // Content-Security-Policy
        if (!empty($headers['content_security'])) {
            $csp_settings = get_option('gseo_csp_settings', []);
            $csp_fields = [
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

            $csp_directives = [];
            foreach ($csp_fields as $key => $directive) {
                if (!empty($csp_settings[$key]['apply'])) {
                    $csp_directives[] = "{$directive} " . $csp_settings[$key]['value'];
                }
            }

            if (!empty($csp_directives)) {
                header_remove('Content-Security-Policy');
                header('Content-Security-Policy: ' . implode('; ', $csp_directives));
            } else {
                header_remove('Content-Security-Policy');
            }
        } else {
            header_remove('Content-Security-Policy');
        }

        // NEW: Remove Server Header
        if (!empty($options['remove_server_header'])) {
            header_remove('Server');
        }

    } else {
        // Explicitly remove all headers if global setting is off
        $all_headers = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Referrer-Policy',
            'Strict-Transport-Security',
            'Permissions-Policy',
            'Content-Security-Policy',
            'Server'  // also remove Server header explicitly
        ];
        foreach ($all_headers as $header) {
            header_remove($header);
        }
    }
}

function gseo_write_security_headers_to_htaccess($block_content) {
    $htaccess_path = ABSPATH . '.htaccess';

    $marker_start = "# BEGIN GSEO Security Headers";
    $marker_end   = "# END GSEO Security Headers";

    $block = $marker_start . "\n" . $block_content . "\n" . $marker_end;

    if (!file_exists($htaccess_path)) {
        return new WP_Error('file_missing', '.htaccess file does not exist.');
    }

    if (!is_writable($htaccess_path)) {
        return new WP_Error('not_writable', '.htaccess file is not writable.');
    }

    $existing = file_get_contents($htaccess_path);

    if (strpos($existing, $marker_start) !== false && strpos($existing, $marker_end) !== false) {
        // Replace old block
        $updated = preg_replace("/$marker_start.*?$marker_end/s", $block, $existing);
    } else {
        // Append new block
        $updated = $existing . "\n\n" . $block . "\n";
    }

    file_put_contents($htaccess_path, $updated);
    return true;
}