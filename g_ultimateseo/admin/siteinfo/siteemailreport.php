<?php
// admin/siteinfo/siteemailreport.php

if (!defined('ABSPATH')) {
    exit;
}

// Display general information
function gfseo_display_general_info() {
    $site_status = get_option('gseo_latest_site_status');

    if (empty($site_status['general_info'])) {
        echo '<p>No general information available.</p>';
        return;
    }

    echo '<h3>General Information:</h3>';
    echo '<ul>';
    foreach ($site_status['general_info'] as $key => $value) {
        echo '<li><strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong> ' . esc_html($value) . '</li>';
    }
    echo '</ul>';
}

// Display admin page for managing email report recipients
function gfseo_display_email_report_admin() {
    global $wpdb;
    $table = $wpdb->prefix . 'gfseo_email_report';

    // Add new email recipient
    if (isset($_POST['add_email']) && check_admin_referer('gfseo_add_email_action', 'gfseo_add_email_nonce')) {
        $wpdb->insert($table, [
            'name'       => sanitize_text_field($_POST['name']),
            'surname'    => sanitize_text_field($_POST['surname']),
            'email'      => sanitize_email($_POST['email']),
            'date_added' => current_time('mysql')
        ]);
        echo '<div class="notice notice-success">Recipient added!</div>';
    }

    // Delete recipient
    if (isset($_GET['delete_email'])) {
        $wpdb->delete($table, ['id' => intval($_GET['delete_email'])]);
        echo '<div class="notice notice-success">Recipient removed.</div>';
    }

    $recipients = $wpdb->get_results("SELECT * FROM $table");
    ?>

    <form method="post">
        <?php wp_nonce_field('gfseo_add_email_action', 'gfseo_add_email_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label>Name</label></th>
                <td><input type="text" name="name" required></td>
            </tr>
            <tr>
                <th><label>Surname</label></th>
                <td><input type="text" name="surname" required></td>
            </tr>
            <tr>
                <th><label>Email</label></th>
                <td><input type="email" name="email" required></td>
            </tr>
        </table>
        <p><input type="submit" name="add_email" class="button button-primary" value="Add Recipient"></p>
    </form>

    <h3>Email Recipients:</h3>
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Date Added</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recipients as $r): ?>
            <tr>
                <td><?php echo esc_html($r->name); ?></td>
                <td><?php echo esc_html($r->surname); ?></td>
                <td><?php echo esc_html($r->email); ?></td>
                <td><?php echo esc_html($r->date_added); ?></td>
                <td>
                    <a href="<?php echo esc_url(add_query_arg('delete_email', $r->id)); ?>" onclick="return confirm('Delete recipient?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form method="post">
        <?php wp_nonce_field('gfseo_manual_report_action', 'gfseo_manual_report_nonce'); ?>
        <p><input type="submit" name="send_manual_report" class="button button-primary" value="Send Report to All Now"></p>
    </form>

    <?php
    if (isset($_POST['send_manual_report']) && check_admin_referer('gfseo_manual_report_action', 'gfseo_manual_report_nonce')) {
        gfseo_send_status_report_to_recipients();
        echo '<div class="notice notice-success">Report sent to all recipients!</div>';
    }
}

function gfseo_send_status_report_to_recipients() {
    global $wpdb;
    $recipients = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gfseo_email_report");

    ob_start();

    $site_url = get_site_url();
    $site_status = get_option('gseo_latest_site_status');

    // General Information
    echo '<h2 style="color:#007cba;">General Information</h2>';
    echo '<table>';
    foreach ($site_status['general_info'] as $key => $value) {
        echo '<tr><td><strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . '</strong></td><td>' . esc_html($value) . '</td></tr>';
    }
    echo '</table>';

    // Security Checks
    echo '<h2 style="color:#007cba; margin-top:30px;">Security Checks</h2>';
    gfseo_display_security_info();

    // Email Configuration
    echo '<h2 style="color:#007cba; margin-top:30px;">Email Configuration</h2>';
    gfseo_display_email_info();

    // Plugins Status
    echo '<h2 style="color:#007cba; margin-top:30px;">Plugins Status</h2>';
    gfseo_display_plugins_info();

    // Broken Links
    echo '<h2 style="color:#007cba; margin-top:30px;">Broken Links</h2>';
    gfseo_display_broken_links_checker();

    // 404 Errors
    echo '<h2 style="color:#007cba; margin-top:30px;">404 Errors</h2>';
    gfseo_display_404_errors();

    $email_body = ob_get_clean();

    $email_template = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Weekly Website Status Report - '.esc_html($site_url).'</title>
        <style>
            body {
                font-family: "Segoe UI", Arial, sans-serif;
                background-color: #f2f4f6;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 900px;
                margin: 20px auto;
                background: #ffffff;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            }
            h1 {
                text-align: center;
                color: #007cba;
                border-bottom: 2px solid #e6e6e6;
                padding-bottom: 10px;
            }
            h2 {
                color: #007cba;
                border-bottom: 1px solid #e6e6e6;
                padding-bottom: 5px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            th, td {
                padding: 10px;
                border-bottom: 1px solid #ddd;
                text-align: left;
            }
            th {
                background-color: #f9f9f9;
            }
            .footer {
                font-size: 12px;
                color: #999;
                text-align: center;
                margin-top: 30px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
            }
            a {
                color: #007cba;
                text-decoration: none;
            }
            ul {
                padding-left: 20px;
                margin: 0;
            }
            li {
                padding-bottom: 6px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <h1>Weekly Website Status Report - '.esc_html($site_url).'</h1>
            '.$email_body.'
            <div class="footer">
                &copy; '.date("Y").' '.esc_html(get_bloginfo('name')).' | <a href="'.esc_url($site_url).'">'.esc_html($site_url).'</a>
            </div>
        </div>
    </body>
    </html>';

    // Get SMTP "From Email" from your plugin options
    $smtp_options = get_option('g_ultimateseo_smtp_options');
    $from_email = !empty($smtp_options['from_email']) ? sanitize_email($smtp_options['from_email']) : get_option('admin_email');

    // Set correct headers
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: '.get_bloginfo('name').' <'.$from_email.'>'
    ];

    foreach ($recipients as $recipient) {
        wp_mail($recipient->email, 'Weekly Website Status Report - '.$site_url, $email_template, $headers);
    }
}



// Schedule weekly report
if (!wp_next_scheduled('gfseo_weekly_email_report')) {
    wp_schedule_event(strtotime('next Monday 08:00:00'), 'weekly', 'gfseo_weekly_email_report');
}
add_action('gfseo_weekly_email_report', 'gfseo_send_status_report_to_recipients');

// Clear scheduled event upon plugin deactivation
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('gfseo_weekly_email_report');
});
