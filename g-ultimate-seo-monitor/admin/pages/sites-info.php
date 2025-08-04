<?php
// admin/pages/sites-info.php
if (!defined('ABSPATH')) exit;

function gseo_render_site_overview_page() {
    global $wpdb;

    wp_enqueue_script('admin-sites', GSEOM_URL . '/assets/js/admin-sites.js', ['jquery'], '1.0.0', true);
    wp_localize_script('admin-sites', 'gseo_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('gseo_sync_nonce'),
    ]);

    $sites = $wpdb->get_results("
        SELECT sites.*, info.*
        FROM {$wpdb->prefix}gusm_sites AS sites
        LEFT JOIN {$wpdb->prefix}gusm_site_info AS info ON sites.id = info.site_id
        ORDER BY sites.url ASC
    ");

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">🛡️ Site Overview</h1><hr class="wp-header-end">';

    echo '<div style="margin-bottom:20px;">';
    echo '<button class="button gseo-toggle-section" data-section="all">🌐 Show All</button>';
    echo '<button class="button gseo-toggle-section" data-section="server-info">🖥️ Server Info</button>';
    echo '<button class="button gseo-toggle-section" data-section="user-roles">👥 User Roles</button>';
    echo '<button class="button gseo-toggle-section" data-section="post-types">📑 Post Types</button>';
    echo '<button class="button gseo-toggle-section" data-section="security-checks">🔒 Security Checks</button>';
    echo '<button class="button gseo-toggle-section" data-section="email-settings">📧 Email Settings</button>';
    echo '<button class="button gseo-toggle-section" data-section="plugin-status">🧩 Plugin Status</button>';
    echo '<button class="button gseo-toggle-section" data-section="links-errors">🔗 Links & Errors</button>';
    echo '<button class="button gseo-toggle-section" data-section="recent-activity">📰 Recent Content Activity</button>';
    echo '</div>';

    foreach ($sites as $site) {
        $sections = [
            'server-info' => ['🖥️ Server Info', maybe_unserialize($site->server_info) ?: []],
            'user-roles' => ['👥 User Roles', maybe_unserialize($site->user_roles) ?: []],
            'post-types' => ['📑 Post Types', maybe_unserialize($site->post_types) ?: []],
            'security-checks' => ['🔒 Security Checks', maybe_unserialize($site->security_checks) ?: []],
            'email-settings' => ['📧 Email Settings', maybe_unserialize($site->email_settings) ?: []],
            'plugin-status' => ['🧩 Plugin Status', maybe_unserialize($site->plugin_status) ?: []],
            'links-errors' => ['🔗 Links & Errors', [
                'Broken Links' => intval($site->broken_links ?? 0),
                '404 Errors' => intval($site->errors_404 ?? 0),
            ]],
            'recent-activity' => ['📰 Recent Content Activity', maybe_unserialize($site->content_activity) ?: []],
        ];

        echo '<div class="postbox" style="margin-top:20px;">';
        echo '<div class="postbox-header">';
        echo '<h2 class="hndle" style="padding:10px; font-size:18px;">🌐 <a href="' . esc_url($site->url) . '" target="_blank" style="font-size:18px; text-decoration: none; color: black; text-align: left; font-weight: bold;">' . esc_html($site->url) . '</a> ';
        echo '<button class="button gseo-sync-site" data-site-id="' . esc_attr($site->id) . '">🔄 Sync Site Info</button>';
        echo '</h2>';
        echo '</div>';

        echo '<div class="inside">';
        foreach ($sections as $section_class => [$title, $data]) {
            echo '<div class="site-section ' . esc_attr($section_class) . '" style="margin-bottom:20px;">';
            echo '<h3>' . esc_html($title) . '</h3>';
            echo '<table class="widefat striped">';
        
            if ($section_class === 'post-types') {
                echo '<tr><th>Type</th><th>Published</th><th>Draft</th><th>Scheduled</th></tr>';
                foreach ($data as $type => $counts) {
                    echo '<tr><td>' . esc_html($type) . '</td>';
                    echo '<td>' . intval($counts['published'] ?? 0) . '</td>';
                    echo '<td>' . intval($counts['draft'] ?? 0) . '</td>';
                    echo '<td>' . intval($counts['scheduled'] ?? 0) . '</td></tr>';
                }
            } 
            elseif ($section_class === 'links-errors') {
                echo '<tr><th>Type</th><th>Count</th></tr>';
                foreach ($data as $type => $count) {
                    echo '<tr><td>' . esc_html($type) . '</td><td>' . intval($count) . '</td></tr>';
                }
            } 
            elseif ($section_class === 'recent-activity') {
                echo '<tr><th>Date</th><th>Title</th><th>Type</th><th>Author</th></tr>';
                foreach ($data as $item) {
                    echo '<tr>';
                    echo '<td>' . esc_html($item['date']) . '</td>';
                    echo '<td><a href="' . esc_url($item['url']) . '" target="_blank">' . esc_html($item['title']) . '</a></td>';
                    echo '<td>' . esc_html($item['type']) . '</td>';
                    echo '<td>' . esc_html($item['author']) . '</td>';
                    echo '</tr>';
                }
            } 
            elseif ($section_class === 'security-checks') {
                echo '<tr><th>Check</th><th>Status</th></tr>';
                foreach ($data as $key => $value) {
                    echo '<tr><td>' . esc_html($key) . '</td><td>' . ($value ? '✅' : '❌') . '</td></tr>';
                }
            }
            elseif ($section_class === 'email-settings') {
                echo '<tr><th>Setting</th><th>Status</th></tr>';
                foreach ($data as $key => $value) {
                    echo '<tr><td>' . esc_html($key) . '</td><td>' . ($value ? '✅' : '❌') . '</td></tr>';
                }
            }
            elseif ($section_class === 'plugin-status') {
                echo '<tr><th>Plugin</th><th>Version</th><th>Status</th><th>Update Status</th></tr>';
                if (is_array($data)) {
                    foreach ($data as $plugin_info) {
                        if (is_array($plugin_info)) {
                            $plugin_name = esc_html($plugin_info['name'] ?? 'N/A');
                            $version = esc_html($plugin_info['version'] ?? 'N/A');
                            $status = ($plugin_info['status'] === 'active') ? '✅ Active' : '❌ Inactive';
                            $update_status = ($plugin_info['update_status'] === 'Up to Date') ? '✅ Up to Date' : '⚠️ Update Available';
            
                            echo '<tr>';
                            echo '<td>' . $plugin_name . '</td>';
                            echo '<td>' . $version . '</td>';
                            echo '<td>' . $status . '</td>';
                            echo '<td>' . $update_status . '</td>';
                            echo '</tr>';
                        }
                    }
                } else {
                    echo '<tr><td colspan="4">No plugin data available.</td></tr>';
                }
            }
            
            
            elseif ($section_class === 'server-info') {
                echo '<tr><th>Item</th><th>Status/Value</th></tr>';
                foreach ($data as $key => $value) {
                    $style = '';
        
                    if ($key === 'Debug Mode' && strtolower($value) === 'on') {
                        $style = 'color:red;font-weight:bold;';
                    } elseif ($key === 'SSL Status' && strtolower($value) !== 'enabled') {
                        $style = 'color:red;font-weight:bold;';
                    } elseif ($key === 'PHP Memory Limit') {
                        $memory_limit = intval(rtrim(strtoupper($value), 'M'));
                        if ($memory_limit > 256) {
                            $style = 'color:red;font-weight:bold;';
                        }
                    }
        
                    echo '<tr>';
                    echo '<td>' . esc_html($key) . '</td>';
                    echo '<td style="' . esc_attr($style) . '">' . esc_html($value) . '</td>';
                    echo '</tr>';
                }
            }
            else {
                echo '<tr><th>Item</th><th>Status/Value</th></tr>';
                foreach ($data as $key => $value) {
                    $display_value = is_bool($value) ? ($value ? '✅' : '❌') : esc_html($value);
                    echo '<tr><td>' . esc_html($key) . '</td><td>' . $display_value . '</td></tr>';
                }
            }
        
            echo '</table>';
            echo '</div>';
        }
        

        echo '</div>';
        echo '<p style="padding:10px;margin-top:10px;"><strong>Last Synced:</strong> ' . esc_html($site->last_synced ?: 'Never') . '</p>';
        echo '</div>';
    }

    if (empty($sites)) {
        echo '<p><em>No sites found. Please add a site first.</em></p>';
    }

    echo '</div>';
}
