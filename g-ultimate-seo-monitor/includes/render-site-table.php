<?php
if (!defined('ABSPATH')) exit;

function gseo_render_sites_table($sites) {
    if (!function_exists('gseo_get_feature_labels')) {
        require_once plugin_dir_path(__FILE__) . 'helpers.php';
    }

    // Parameters
    $sort_by = $_GET['sort_by'] ?? 'url';
    $filter_status = $_GET['status'] ?? 'all';
    $current_page = max(1, intval($_GET['paged'] ?? 1));
    $per_page = intval($_GET['per_page'] ?? 10);
    if (!in_array($per_page, [5,10,25,50,100])) {
        $per_page = 10;
    }

    $feature_labels = gseo_get_feature_labels();
    $filtered_data = gseo_get_filtered_site_rows($sites, $feature_labels);
    $paged_rows = $filtered_data['paged'];
    $total_pages = $filtered_data['total_pages'];

    $base_url_args = [
        'sort_by' => $sort_by,
        'status' => $filter_status,
        'per_page' => $per_page
    ];
    ?>

<form method="get" id="gseo-filter-form">
    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? ''); ?>">
    <select name="status">
        <option value="all" <?php selected($filter_status, 'all'); ?>>All</option>
        <option value="connected" <?php selected($filter_status, 'connected'); ?>>Connected</option>
        <option value="unauthorized" <?php selected($filter_status, 'unauthorized'); ?>>Unauthorized</option>
        <option value="failed" <?php selected($filter_status, 'failed'); ?>>Failed</option>
    </select>
    <select name="sort_by">
        <option value="url" <?php selected($sort_by, 'url'); ?>>Sort by URL</option>
        <option value="status" <?php selected($sort_by, 'status'); ?>>Sort by Status</option>
    </select>
    <select name="per_page">
        <option value="5" <?php selected($per_page, 5); ?>>5 per page</option>
        <option value="10" <?php selected($per_page, 10); ?>>10 per page</option>
        <option value="25" <?php selected($per_page, 25); ?>>25 per page</option>
        <option value="50" <?php selected($per_page, 50); ?>>50 per page</option>
        <option value="100" <?php selected($per_page, 100); ?>>100 per page</option>
    </select>
    <button class="button">Apply</button>
</form>

<table class="widefat striped">
    <tbody id="gseo-site-table-body">
    <?php foreach ($paged_rows as $site):
        $site_id = $site['id'];
        $url = $site['url'];
        $status = ucfirst($site['status']);
    ?>

    <!-- URL, Status and Delete Button Row -->
    <tr class="gseo-url-row">
        <td colspan="2"><strong>Site URL:</strong> 
            <a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($url); ?></a>
        </td>
        <td style="text-align:center;"><strong>Status:</strong> <?php echo esc_html($status); ?></td>
        <td style="text-align:right;">
            <form method="post" style="display:inline;">
                <?php wp_nonce_field('gseo_delete_site_action', 'gseo_delete_site_nonce'); ?>
                <input type="hidden" name="delete_site_url" value="<?php echo esc_attr($url); ?>">
                <?php submit_button('Delete', 'delete', 'gseo_delete_site', false, [
                    'onclick' => 'return confirm("Delete this site?");'
                ]); ?>
            </form>
        </td>
    </tr>

    <!-- Headers Row -->
    <tr class="gseo-section-headers">
        <th>Main Security Settings</th>
        <th>Security Headers</th>
        <th colspan="2">Content Security Policy</th>
    </tr>

    <!-- Content Row -->
    <tr class="gseo-settings-row">
        <td>
            <ul class="gseo-settings-list">
            <?php foreach ($feature_labels as $key => $label):
                $enabled = gusm_get_security_option($site_id, $key); ?>
                <li>
                    <span class="gseo-label"><?php echo esc_html($label); ?></span>
                    <span class="gseo-status"><?php echo $enabled ? '✅' : '❌'; ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
        </td>

        <td>
            <ul class="gseo-settings-list">
            <?php
            $headers = [
                'x_frame_options' => 'X-Frame-Options',
                'x_content_type' => 'X-Content-Type',
                'x_xss_protection' => 'X-XSS-Protection',
                'referrer_policy' => 'Referrer-Policy',
                'strict_transport' => 'Strict-Transport',
                'permissions_policy' => 'Permissions-Policy',
                'content_security' => 'Content-Security-Policy'
            ];
            foreach ($headers as $header_key => $header_label):
                $enabled = gusm_get_security_option($site_id, 'header_' . $header_key); ?>
                <li>
                    <span class="gseo-label"><?php echo esc_html($header_label); ?></span>
                    <span class="gseo-status"><?php echo $enabled ? '✅' : '❌'; ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
        </td>

        <td colspan="2">
            <ul class="gseo-csp-list">
            <?php
            $csp_directives = [
                'csp_default_src' => 'default-src',
                'csp_script_src' => 'script-src',
                'csp_style_src' => 'style-src',
                'csp_img_src' => 'img-src',
                'csp_font_src' => 'font-src',
                'csp_connect_src' => 'connect-src',
                'csp_media_src' => 'media-src',
                'csp_object_src' => 'object-src',
                'csp_frame_src' => 'frame-src',
                'csp_form_action' => 'form-action',
                'csp_base_uri' => 'base-uri'
            ];
            foreach ($csp_directives as $directive_key => $directive_label):
                $apply = gusm_get_security_option($site_id, $directive_key . '_apply');
                $value = gusm_get_security_option($site_id, $directive_key . '_value');
                $is_applied = (int)$apply === 1;
                $icon = $is_applied ? '✅' : '❌';
                $display_value = $is_applied ? esc_html($value) : '(disabled)';
            ?>
                <li>
                    <span class="gseo-csp-directive"><?php echo esc_html($directive_label); ?></span>
                    <span class="gseo-csp-status"><?php echo $icon; ?></span>
                </li>
                <li class="gseo-csp-value"><?php echo $display_value; ?></li>
            <?php endforeach; ?>
            </ul>
        </td>
    </tr>

    <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="tablenav">
    <div class="tablenav-pages">
        <?php for ($i = 1; $i <= $total_pages; $i++):
            $url_args = array_merge($base_url_args, ['paged' => $i]);
            $url = add_query_arg($url_args); ?>
            <a class="button<?php echo $i == $current_page ? ' current' : ''; ?>" href="<?php echo esc_url($url); ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
<?php endif;
}
