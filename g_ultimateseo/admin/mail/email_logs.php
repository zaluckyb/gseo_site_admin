<?php
if (!defined('ABSPATH')) exit;

// admin/mail/email_logs.php

function gseo_display_email_logs() {
    global $wpdb;
    $table = $wpdb->prefix . 'gseo_email_log';
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC LIMIT 100");
    ?>
    <h2>Email Logs (Last 100 emails)</h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th width="20%">Date</th>
                <th width="20%">Recipient</th>
                <th width="20%">Subject</th>
                <th width="10%">Status</th>
                <th width="30%">Error Log</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($logs): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->sent_at); ?></td>
                    <td><?php echo esc_html($log->email_to); ?></td>
                    <td><?php echo esc_html($log->subject); ?></td>
                    <td><?php echo esc_html(ucfirst($log->status)); ?></td>
                    <td><?php echo esc_html($log->error_log); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No logs found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php
}
