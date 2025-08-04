<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Renders the site rows (table body).
 */

 // // includes/render-site-table.php
function gseo_render_site_table_rows($rows, $feature_labels) {
    ob_start();

    foreach ($rows as $site) {
        $url = $site['url'];
        // $token = isset($site['token']) ? trim($site['token']) : 'token-placeholder'; // Replace if no token column yet
        $token = trim($site['token']); // ✅ now safely using real token from DB
        $result = gseo_fetch_site_status(untrailingslashit($url), $token);

        echo '<tr>';
        echo '<td>' . esc_html($url) . '</td>';

        // Status column
        echo '<td>';
        if ($result['error']) {
            echo '<span style="color:red;">Connection Failed: ' . esc_html($result['error']) . '</span>';
        } elseif ($result['status'] === 401) {
            echo '<span style="color:red;">Unauthorized (Invalid Token)</span>';
        } elseif ($result['status'] === 200) {
            echo '<span style="color:green;">Connected</span>';
        } else {
            echo '<span style="color:red;">HTTP ' . esc_html($result['status']) . '</span>';
        }
        echo '</td>';

        // Features column
        echo '<td>';
        $body = $result['body'];
        if ($result['status'] === 200 && isset($body['features']) && is_array($body['features'])) {
            echo '<ul>';
            $counter = 1;
            foreach ($feature_labels as $key => $label) {
                $enabled = $body['features'][$key] ?? false;
                $icon = $enabled ? '✅' : '❌';
                echo '<li><strong>' . esc_html("$counter. $label") . ':</strong> ' . $icon . '</li>';
                $counter++;
            }
            echo '</ul>';
        } elseif (isset($body['message'])) {
            echo esc_html($body['message']);
        } else {
            echo 'N/A';
        }
        echo '</td>';

        // Actions column
        echo '<td>';
        ?>
        <form method="post" style="display:inline;">
            <?php wp_nonce_field('gseo_delete_site_action', 'gseo_delete_site_nonce'); ?>
            <input type="hidden" name="delete_site_url" value="<?php echo esc_attr($url); ?>">
            <?php submit_button('Delete', 'delete', 'gseo_delete_site', false, [
                'onclick' => 'return confirm("Delete this site?");'
            ]); ?>
        </form>
        <?php
        echo '</td>';
        echo '</tr>';
    }

    return ob_get_clean();
}

/**
 * Renders the full site table with controls and pagination.
 */
function gseo_render_sites_table($sites) {
    if (!function_exists('gseo_get_feature_labels')) {
        require_once plugin_dir_path(__FILE__) . 'helpers.php';
    }

    $feature_labels = gseo_get_feature_labels();
    $filtered_data = gseo_get_filtered_site_rows($sites, $feature_labels);
    $paged_rows = $filtered_data['paged'];
    $total_pages = $filtered_data['total_pages'];

    $sort_by = $_GET['sort_by'] ?? 'url';
    $filter_status = $_GET['status'] ?? 'all';
    $current_page = max(1, intval($_GET['paged'] ?? 1));

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
        <button class="button">Apply</button>
    </form>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>Site URL</th>
                <th>Status</th>
                <th>Security Settings</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="gseo-site-table-body">
            <?php echo gseo_render_site_table_rows($paged_rows, $feature_labels); ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="tablenav"><div class="tablenav-pages">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php
                $url = add_query_arg([
                    'paged' => $i,
                    'sort_by' => $sort_by,
                    'status' => $filter_status,
                ]);
                ?>
                <a class="button<?php echo $i == $current_page ? ' current' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div></div>
    <?php endif;
}
