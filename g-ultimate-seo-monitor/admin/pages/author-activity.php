<?php
if (!defined('ABSPATH')) exit;

function gseo_render_author_activity_page() {
    global $wpdb;

    $results = $wpdb->get_results("
        SELECT ac.*, a.name as author_name, s.url as site_url
        FROM {$wpdb->prefix}gusm_author_content ac
        JOIN {$wpdb->prefix}gusm_authors a ON ac.author_id = a.id
        JOIN {$wpdb->prefix}gusm_sites s ON ac.site_id = s.id
        ORDER BY ac.post_date DESC
    ");

    echo '<div class="wrap"><h1>📝 Author Activity Overview</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Author</th><th>Site</th><th>Title</th><th>Date</th></tr></thead><tbody>';

    if (!empty($results)) {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->author_name) . '</td>';
            echo '<td>' . esc_html($row->site_url) . '</td>';
            echo '<td><a href="' . esc_url($row->post_url) . '" target="_blank">' . esc_html($row->post_title) . '</a></td>';
            echo '<td>' . esc_html(date('Y-m-d', strtotime($row->post_date))) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4">No author activity found.</td></tr>';
    }

    echo '</tbody></table></div>';
}