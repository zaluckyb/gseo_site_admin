<?php
// admin/siteinfo/activity.php
if (!defined('ABSPATH')) exit;

function gfseo_activity_page_callback() {
    echo '<div class="wrap">';
    echo '<h1>Activity Summary</h1>';

    // Manual report trigger button
    if (isset($_POST['send_activity_report_now']) && check_admin_referer('gfseo_send_activity_report_action', 'gfseo_send_activity_report_nonce')) {
        gfseo_send_daily_activity_email();
        echo '<div class="notice notice-success is-dismissible"><p>Activity report sent successfully.</p></div>';
    }

    echo '<form method="post" style="margin-bottom:20px;">';
    wp_nonce_field('gfseo_send_activity_report_action', 'gfseo_send_activity_report_nonce');
    echo '<input type="submit" name="send_activity_report_now" class="button button-primary" value="Send Activity Report Now">';
    echo '</form>';

    // Admin Activity Summary first
    gfseo_admin_activity_summary();

    // Content Activity next
    gfseo_content_activity();

    echo '</div>';
}

function gfseo_admin_activity_summary() {
    echo '<h2>Admin Activity Summary</h2>';

    $admins = get_users(['role' => 'Administrator']);

    echo '<table class="widefat striped"><thead><tr>';
    echo '<th>Administrator</th><th>Today</th><th>This Week</th><th>This Month</th>';
    echo '</tr></thead><tbody>';

    foreach ($admins as $admin) {
        $daily   = gfseo_count_admin_posts($admin->ID, 'today');
        $weekly  = gfseo_count_admin_posts($admin->ID, 'week');
        $monthly = gfseo_count_admin_posts($admin->ID, 'month');

        echo '<tr>';
        echo '<td>'.esc_html($admin->display_name).'</td>';
        echo '<td>'.esc_html($daily).'</td>';
        echo '<td>'.esc_html($weekly).'</td>';
        echo '<td>'.esc_html($monthly).'</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

function gfseo_content_activity() {
    echo '<h2 style="margin-top:30px;">Recent Content Activity (Latest 100)</h2>';

    $recent_posts = get_posts([
        'post_type'      => ['post', 'page'],
        'posts_per_page' => 100,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);

    echo '<table class="widefat striped">';
    echo '<thead><tr>';
    echo '<th>Date Published</th><th>Title</th><th>Type</th><th>Author</th>';
    echo '</tr></thead><tbody>';

    foreach ($recent_posts as $post) {
        $author = get_the_author_meta('display_name', $post->post_author);
        echo '<tr>';
        echo '<td>'.esc_html(get_the_date('Y-m-d H:i', $post)).'</td>';
        echo '<td><a href="'.esc_url(get_permalink($post)).'" target="_blank">'.esc_html($post->post_title).'</a></td>';
        echo '<td>'.esc_html(ucfirst($post->post_type)).'</td>';
        echo '<td>'.esc_html($author).'</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

function gfseo_count_admin_posts($admin_id, $period) {
    $date_query = ['after' => 'today midnight'];

    if ($period === 'week') {
        $date_query = ['after' => 'monday this week midnight'];
    } elseif ($period === 'month') {
        $date_query = ['after' => 'first day of this month midnight'];
    }

    $posts = get_posts([
        'post_type'   => ['post', 'page'],
        'post_status' => 'publish',
        'author'      => $admin_id,
        'date_query'  => [$date_query],
        'fields'      => 'ids'
    ]);

    return count($posts);
}

// Schedule daily email if not already scheduled
if (!wp_next_scheduled('gfseo_daily_activity_email')) {
    wp_schedule_event(strtotime('tomorrow 08:00:00'), 'daily', 'gfseo_daily_activity_email');
}

add_action('gfseo_daily_activity_email', 'gfseo_send_daily_activity_email');

// Remove scheduled event upon plugin deactivation
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('gfseo_daily_activity_email');
});

function gfseo_send_daily_activity_email() {
    global $wpdb;

    $recipients = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gfseo_email_report");
    if (empty($recipients)) return;

    $site_url = get_site_url();
    ob_start();

    echo '<h2>Administrator Activity Yesterday</h2><ul>';
    foreach (get_users(['role' => 'Administrator']) as $admin) {
        $count = gfseo_count_admin_posts($admin->ID, 'today');
        echo '<li>'.esc_html($admin->display_name).': '.esc_html($count).' posts/pages</li>';
    }
    echo '</ul>';

    echo '<h2>Content Published Yesterday</h2>';
    $recent_posts = get_posts([
        'post_type'      => ['post', 'page'],
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'date_query'     => [['after' => 'yesterday', 'before' => 'today']]
    ]);

    if (empty($recent_posts)) {
        echo '<p>No new content published yesterday.</p>';
    } else {
        echo '<ul>';
        foreach ($recent_posts as $post) {
            $author = get_the_author_meta('display_name', $post->post_author);
            echo '<li><strong>'.esc_html($post->post_title).'</strong> ('.ucfirst($post->post_type).') by '.esc_html($author).' - Published: '.get_the_date('Y-m-d H:i', $post).'</li>';
        }
        echo '</ul>';
    }

    $email_content = ob_get_clean();

    $template = '
    <html>
    <body style="font-family: Arial; background-color: #f8f9fa; padding: 20px;">
        <div style="background-color:#ffffff; padding: 30px; border-radius: 8px; max-width: 800px; margin: auto;">
            <h1 style="color:#007cba;">Daily Activity Report - '.esc_html($site_url).'</h1>
            '.$email_content.'
            <footer style="text-align:center; margin-top:20px; color:#888; font-size:12px;">
                &copy; '.date('Y').' '.get_bloginfo('name').'
            </footer>
        </div>
    </body>
    </html>';

    foreach ($recipients as $recipient) {
        g_ultimateseo_send_email_smtp(
            $recipient->email,
            'Daily Activity Report - '.$site_url,
            $template,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }
}
