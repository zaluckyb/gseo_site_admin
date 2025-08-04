<?php
if (!defined('ABSPATH')) exit;

// security/csp-settings.php

function gseo_render_csp_settings_page() {
    if (isset($_POST['gseo_save_csp_settings'])) {
        check_admin_referer('gseo_csp_settings_nonce');

        $raw_post = wp_unslash($_POST);

        $directive_options = [
            'self'      => "'self'",
            'none'      => "'none'",
            'self_data' => "'self' data:",
            'all'       => "*",
            'custom'    => "Custom",
        ];

        $directives = [
            'csp_default_src', 'csp_script_src', 'csp_style_src', 'csp_img_src', 'csp_font_src',
            'csp_connect_src', 'csp_media_src', 'csp_object_src', 'csp_frame_src', 'csp_form_action', 'csp_base_uri'
        ];

        $csp_settings = [];
        foreach ($directives as $directive) {
            $apply_directive = !empty($raw_post[$directive . '_apply']) ? 1 : 0;
            $val = isset($raw_post[$directive]) ? $raw_post[$directive] : 'self';

            if ($val === 'custom') {
                $custom_val = isset($raw_post[$directive . '_custom']) ? $raw_post[$directive . '_custom'] : "'self'";
                $val = sanitize_text_field($custom_val);
            } else {
                $val = isset($directive_options[$val]) ? $directive_options[$val] : "'self'";
                $val = sanitize_text_field($val);
            }

            if ($directive === 'csp_form_action' && $val === "'none'") {
                $val = "'self'";
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-warning is-dismissible"><p>⚠️ CSP "form-action" cannot be set to <strong>\'none\'</strong>. Reset to <strong>\'self\'</strong>.</p></div>';
                });
            }

            $csp_settings[$directive] = [
                'apply' => $apply_directive,
                'value' => $val,
            ];
        }

        update_option('gseo_csp_settings', $csp_settings);
        echo '<div class="notice notice-success is-dismissible"><p>✅ CSP settings updated successfully.</p></div>';
    }

    $csp_settings = get_option('gseo_csp_settings', []);

    $directive_options = [
        'self'      => "'self'",
        'none'      => "'none'",
        'self_data' => "'self' data:",
        'all'       => "*",
        'custom'    => "Custom",
    ];

    $csp_fields = [
        'csp_default_src' => ['default-src', 'Defines the default source for content if other directives aren\'t specified.'],
        'csp_script_src'  => ['script-src', 'Specifies allowed sources for JavaScript files.'],
        'csp_style_src'   => ['style-src', 'Defines permitted sources for CSS stylesheets.'],
        'csp_img_src'     => ['img-src', 'Sets sources from which images can be loaded.'],
        'csp_font_src'    => ['font-src', 'Specifies valid sources for fonts.'],
        'csp_connect_src' => ['connect-src', 'Controls origins for XMLHttpRequest, Fetch, WebSockets.'],
        'csp_media_src'   => ['media-src', 'Specifies allowed sources for audio and video files.'],
        'csp_object_src'  => ['object-src', 'Defines sources for plugins like Flash.'],
        'csp_frame_src'   => ['frame-src', 'Sets allowed sources for iframe content.'],
        'csp_form_action' => ['form-action', 'Restricts URLs for form submissions.'],
        'csp_base_uri'    => ['base-uri', 'Limits URLs usable in <base> elements.'],
    ];
?>
<div class="wrap">
    <h1>🔐 CSP Settings</h1>

    <div class="metabox-holder gseo-mb-holder">
        <div class="postbox gseo-postbox">
            <div class="postbox-header">
                <h2 class="hndle"><span>Legend of CSP Options</span></h2>
            </div>
            <div class="inside">
                <dl style="margin:0;">
                    <dt>'self'</dt>
                    <dd>Allows resources only from your own domain.</dd>
                    <dt>'none'</dt>
                    <dd>Disallows all resources from loading.</dd>
                    <dt>'self' data:</dt>
                    <dd>Allows resources from your domain and inline data (base64 images).</dd>
                    <dt>* (all)</dt>
                    <dd>Allows resources from any domain (less secure).</dd>
                    <dt>Custom</dt>
                    <dd>Enter specific domains to allow (e.g., Google Fonts, APIs, Gravatar).</dd>
                </dl>
            </div>
        </div>
    </div>

    <form method="post">
        <?php wp_nonce_field('gseo_csp_settings_nonce'); ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:20px;margin-top:30px;">
            <?php foreach ($csp_fields as $key => [$label, $description]) :
                $current_setting = $csp_settings[$key]['value'] ?? "'self'";
                $apply_directive = $csp_settings[$key]['apply'] ?? 0;
                $matched_key = array_search($current_setting, $directive_options, true);

                $selected_option = $matched_key === false ? 'custom' : $matched_key;
                $custom_value = $matched_key === false ? $current_setting : '';
            ?>
            <div style="border:1px solid #ccd0d4;padding:20px;border-radius:8px;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($key); ?>_apply" <?php checked(1, $apply_directive); ?> style="transform:scale(1.5);margin-right:8px;">
                    <strong style="font-size:16px;"><?php echo esc_html($label); ?> (Apply?)</strong>
                </label>
                <p style="color:#646970;margin-top:8px;font-size:13px;"><?php echo esc_html($description); ?></p>
                <?php foreach ($directive_options as $val => $text) : ?>
                    <label style="display:block;margin:6px 0;">
                        <input type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>" <?php checked($selected_option, $val); ?> onclick="toggleCustom('<?php echo esc_js($key); ?>')">
                        <?php echo esc_html($text); ?>
                    </label>
                <?php endforeach; ?>
                <input type="text" id="<?php echo esc_attr($key); ?>_custom" name="<?php echo esc_attr($key); ?>_custom" style="width:100%;margin-top:8px;padding:6px;display:<?php echo $selected_option === 'custom' ? 'block' : 'none'; ?>;" placeholder="Enter custom directive..." value="<?php echo esc_attr($custom_value); ?>">
            </div>
            <?php endforeach; ?>
        </div>

        <p style="margin-top:30px;">
            <input type="submit" name="gseo_save_csp_settings" class="button button-primary" value="💾 Save CSP Settings">
            <a href="<?php echo admin_url('admin.php?page=gseo-security-headers'); ?>" class="button">⬅️ Back to Security Headers</a>
        </p>
    </form>
</div>

<script>
function toggleCustom(key) {
    const customInput = document.getElementById(key + '_custom');
    const selected = document.querySelector(`input[name="${key}"]:checked`).value;
    customInput.style.display = selected === 'custom' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($csp_fields as $key => $_) : ?>
        toggleCustom('<?php echo esc_js($key); ?>');
    <?php endforeach; ?>
});
</script>

<?php
}
