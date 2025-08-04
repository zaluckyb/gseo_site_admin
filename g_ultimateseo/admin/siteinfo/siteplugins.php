<?php
// admin/siteinfo/siteplugins.php

if (!defined('ABSPATH')) {
    exit;
}
function gfseo_check_plugin_update_status($plugin_file, $current_version) {
    $update_plugins = get_site_transient('update_plugins');
    if (isset($update_plugins->response[$plugin_file])) {
        return 'Update Available';
    }
    return 'Up to Date';
}

function gfseo_display_plugins_info() {
    $status = get_option('gseo_latest_site_status');

    if (empty($status['installed_plugins'])) {
        echo '<p>No plugins data available. Run a status check first.</p>';
        return;
    }

    // Calculate plugin stats
    $total_plugins = count($status['installed_plugins']);
    $active_plugins = 0;
    $inactive_plugins = 0;
    $plugins_up_to_date = 0;
    $plugins_need_updates = 0;

    foreach ($status['installed_plugins'] as $plugin) {
        if ($plugin['status'] === 'active') {
            $active_plugins++;
        } else {
            $inactive_plugins++;
        }

        if ($plugin['update_status'] === 'Up to Date') {
            $plugins_up_to_date++;
        } else {
            $plugins_need_updates++;
        }
    }

    ?>
    <div class="plugin-score-overview" style="padding:20px;background:#f8f8f8;border-radius:5px;margin-bottom:20px;">
        <h2>🔌 Plugin Status Overview</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Plugin Statistic</th>
                    <th>Count</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Installed Plugins</td>
                    <td><?php echo esc_html($total_plugins); ?></td>
                    <td>Total number of plugins installed on your site.</td>
                </tr>
                <tr>
                    <td>Active Plugins</td>
                    <td><span style="color:green;"><?php echo esc_html($active_plugins); ?></span></td>
                    <td>Plugins currently active and running.</td>
                </tr>
                <tr>
                    <td>Inactive Plugins</td>
                    <td><span style="color:orange;"><?php echo esc_html($inactive_plugins); ?></span></td>
                    <td>Plugins installed but currently inactive.</td>
                </tr>
                <tr>
                    <td>Plugins Up to Date</td>
                    <td><span style="color:green;"><?php echo esc_html($plugins_up_to_date); ?></span></td>
                    <td>Plugins that are updated to their latest versions.</td>
                </tr>
                <tr>
                    <td>Plugins Needing Updates</td>
                    <td><span style="color:red;"><?php echo esc_html($plugins_need_updates); ?></span></td>
                    <td>Plugins that have new updates available.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2 style="margin-top:30px;">🔌 Installed Plugins Details</h2>
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Plugin Name</th>
                <th>Version</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($status['installed_plugins'] as $plugin): ?>
            <tr>
                <td><?php echo esc_html($plugin['name']); ?></td>
                <td><?php echo esc_html($plugin['version']); ?></td>
                <td>
                    <?php echo $plugin['status'] === 'active' 
                        ? '<span style="color:green;">Active</span>' 
                        : '<span style="color:gray;">Inactive</span>'; ?>
                </td>
                <td>
                    <?php echo $plugin['update_status'] === 'Update Available' 
                        ? '<span style="color:red;">Update Available ⚠️</span>' 
                        : '<span style="color:green;">Up to Date ✅</span>'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
