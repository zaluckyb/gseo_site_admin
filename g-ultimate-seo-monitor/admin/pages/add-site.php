<?php
// admin/pages/add-site.php
function gseo_render_add_site_form() {
    ?>
    <form method="post">
        <?php wp_nonce_field('gseo_add_site_action', 'gseo_add_site_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Site Name:', 'gusm'); ?></th>
                <td><input type="text" name="new_site_name" required class="regular-text" placeholder="<?php _e('Example Site', 'gusm'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Site URL:', 'gusm'); ?></th>
                <td><input type="url" name="new_site_url" required class="regular-text" placeholder="https://example.com"></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('API Token:', 'gusm'); ?></th>
                <td><input type="text" name="new_site_token" required class="regular-text" placeholder="<?php _e('Token from remote site', 'gusm'); ?>"></td>
            </tr>
        </table>
        <?php submit_button(__('Add Site', 'gusm'), 'primary', 'gseo_add_site'); ?>
    </form>
    <?php
}

function gseo_add_site_page() {
    gseo_handle_add_site();

    echo '<div class="wrap"><h1>' . esc_html__('Add New Site', 'gusm') . '</h1>';
    gseo_render_add_site_form();

    // Display existing sites
    global $wpdb;
    $sites = gusm_get_all_sites();

    if (!empty($sites)) {
        echo '<h2>' . esc_html__('Existing Sites', 'gusm') . '</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Name', 'gusm') . '</th>';
        echo '<th>' . esc_html__('URL', 'gusm') . '</th>';
        echo '<th>' . esc_html__('Status', 'gusm') . '</th>';
        echo '<th>' . esc_html__('Last Checked', 'gusm') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($sites as $site) {
            echo '<tr>';
            echo '<td>' . esc_html($site['name']) . '</td>';
            echo '<td>' . esc_html($site['url']) . '</td>';
            echo '<td>' . esc_html($site['status']) . '</td>';
            echo '<td>' . esc_html($site['last_checked'] ?? 'Never') . '</td>'; // Handle missing 'last_checked'
            echo '</tr>';
        }
        

        echo '</tbody></table>';
    }

    echo '</div>';
}