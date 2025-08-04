<?php
// Successful logins

// admin/siteinfo/activitylog.php
add_action('wp_login', 'gfseo_log_successful_login', 10, 2);
function gfseo_log_successful_login($user_login, $user) {
    gfseo_insert_login_log($user_login, $user->ID, 'Success');
}

// Failed logins
add_action('wp_login_failed', 'gfseo_log_failed_login');
function gfseo_log_failed_login($user_login) {
    error_log('Login failed for username: ' . $user_login);
    gfseo_insert_login_log($user_login, null, 'Failed');
    gfseo_alert_failed_login($user_login);
}



// Insert login details into the database
function gfseo_insert_login_log($user_login, $user_id, $status) {
    global $wpdb;

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'gfseo_login_logs',
        [
            'user_login'   => sanitize_text_field($user_login),
            'user_id'      => $user_id,
            'login_time'   => current_time('mysql'),
            'login_status' => $status,
            'ip_address'   => sanitize_text_field($_SERVER['REMOTE_ADDR']),
        ]
    );

    if ($inserted === false) {
        error_log('Insert failed: ' . $wpdb->last_error);
    }
}


// Email notification for suspicious failed login attempts
function gfseo_alert_failed_login($user_login) {
    global $wpdb;
    $recipients = $wpdb->get_results("SELECT email FROM {$wpdb->prefix}gfseo_email_report");
    if (empty($recipients)) return;

    $emails = wp_list_pluck($recipients, 'email');
    $subject = '⚠️ Failed Login Attempt Alert - '.get_bloginfo('name');
    $message = '<p>A failed login attempt was detected:</p>
                <ul>
                    <li><strong>Username:</strong> '.esc_html($user_login).'</li>
                    <li><strong>IP Address:</strong> '.esc_html($_SERVER['REMOTE_ADDR']).'</li>
                    <li><strong>Date/Time:</strong> '.current_time('mysql').'</li>
                </ul>
                <p>If this wasn’t you, please verify your site’s security immediately.</p>';

    foreach ($emails as $email) {
        g_ultimateseo_send_email_smtp($email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
    }
}
function gfseo_render_activity_log_tab() {
    global $wpdb;

    $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gfseo_login_logs ORDER BY login_time DESC LIMIT 100");

    if (empty($logs)) {
        echo '<p>No login activity recorded yet.</p>';
        error_log('No logs found in gfseo_login_logs table.');
        return;
    }

    echo '<table class="widefat striped">';
    echo '<thead><tr>';
    echo '<th>Date/Time</th><th>Username</th><th>Status</th><th>IP Address</th>';
    echo '</tr></thead><tbody>';

    foreach ($logs as $log) {
        $status_color = ($log->login_status === 'Success') ? 'green' : 'red';
        echo '<tr>';
        echo '<td>'.esc_html($log->login_time).'</td>';
        echo '<td>'.esc_html($log->user_login).'</td>';
        echo '<td style="color:'.$status_color.';">'.esc_html($log->login_status).'</td>';
        echo '<td>'.esc_html($log->ip_address).'</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}



if (!wp_next_scheduled('gfseo_daily_login_activity_email')) {
    wp_schedule_event(strtotime('tomorrow 09:00:00'), 'daily', 'gfseo_daily_login_activity_email');
}

add_action('gfseo_daily_login_activity_email', 'gfseo_send_login_activity_report');

register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('gfseo_daily_login_activity_email');
});
function gfseo_send_login_activity_report() {
    global $wpdb;
    $recipients = $wpdb->get_results("SELECT email FROM {$wpdb->prefix}gfseo_email_report");
    if (empty($recipients)) return;

    $emails = wp_list_pluck($recipients, 'email');
    $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gfseo_login_logs WHERE login_time >= NOW() - INTERVAL 1 DAY ORDER BY login_time DESC");

    ob_start();
    echo '<h2>🔐 Login Activity - Last 24 hours</h2>';
    echo '<ul>';
    foreach ($logs as $log) {
        echo '<li>'.esc_html($log->login_time).' - '.esc_html($log->user_login).' ('.esc_html($log->login_status).') from IP: '.esc_html($log->ip_address).'</li>';
    }
    echo '</ul>';

    $message = ob_get_clean();

    foreach ($emails as $email) {
        g_ultimateseo_send_email_smtp(
            $email,
            'Daily Login Activity Report - '.get_bloginfo('name'),
            $message,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }
}
