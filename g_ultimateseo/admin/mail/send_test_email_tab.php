<form method="post">
    <?php wp_nonce_field('smtp_test_email_action', 'smtp_test_email_nonce'); ?>
    <table class="form-table">
        <tr><th>Recipient Email:</th><td><input type="email" name="test_email" required style="width: 100%; max-width: 500px;"></td></tr>
        <tr><th>Email Body:</th><td><textarea name="email_body" rows="6" required style="width: 100%; max-width: 500px;"></textarea></td></tr>
    </table>
    <?php submit_button('Send Test Email', 'secondary', 'send_test_email'); ?>
</form>

<?php

//admin/mail/send_test_email_tab.php
if (isset($_POST['send_test_email'])) {
    if (!isset($_POST['smtp_test_email_nonce']) || !wp_verify_nonce($_POST['smtp_test_email_nonce'], 'smtp_test_email_action')) {
        wp_die('Security check failed');
    }

    $test_email = sanitize_email($_POST['test_email']);
    $email_body = sanitize_textarea_field($_POST['email_body']);
    $sent = g_ultimateseo_send_email_smtp($test_email, 'Test Email from G Ultimate SEO Plugin', $email_body);

    if ($sent) {
        echo '<div class="notice notice-success"><p>Email successfully sent!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Email failed to send. Check SMTP settings.</p></div>';
    }
}
