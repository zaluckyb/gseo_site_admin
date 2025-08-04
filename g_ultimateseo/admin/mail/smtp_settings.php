<?php
if (!defined('ABSPATH')) {
    exit;
}
// admin/mail/smtp_settings.php
add_action('admin_init', 'g_ultimateseo_register_smtp_settings');

function g_ultimateseo_register_smtp_settings() {
    register_setting('g_ultimateseo_smtp_group', 'g_ultimateseo_smtp_options');
}

function g_ultimateseo_smtp_settings_page() {

    // SECURITY CHECK - Only Administrators Allowed
    if (!current_user_can('administrator')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'smtp_settings';
    ?>
    <div class="wrap">
        <h1>SMTP, DMARC & DKIM Settings</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=g-email-smtp-settings&tab=smtp_settings" class="nav-tab <?php echo $active_tab == 'smtp_settings' ? 'nav-tab-active' : ''; ?>">SMTP Settings</a>
            <a href="?page=g-email-smtp-settings&tab=send_test_email" class="nav-tab <?php echo $active_tab == 'send_test_email' ? 'nav-tab-active' : ''; ?>">Send Test Email</a>
            <a href="?page=g-email-smtp-settings&tab=dmarc_dkim_check" class="nav-tab <?php echo $active_tab == 'dmarc_dkim_check' ? 'nav-tab-active' : ''; ?>">DMARC & DKIM Check</a>
            <a href="?page=g-email-smtp-settings&tab=email_logs" class="nav-tab <?php echo $active_tab == 'email_logs' ? 'nav-tab-active' : ''; ?>">Email Logs</a>
        </h2>

        <?php if ($active_tab == 'smtp_settings') : ?>

            <!-- SMTP Settings Tab Content -->
            <form method="post" action="options.php">
                <?php
                settings_fields('g_ultimateseo_smtp_group');
                $options = get_option('g_ultimateseo_smtp_options');
                ?>
                <table class="form-table">
                    <tr>
                        <th>SMTP Host:</th>
                        <td><input type="text" name="g_ultimateseo_smtp_options[host]" value="<?php echo esc_attr($options['host'] ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th>SMTP Username:</th>
                        <td><input type="text" name="g_ultimateseo_smtp_options[username]" value="<?php echo esc_attr($options['username'] ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th>SMTP Password:</th>
                        <td><input type="password" name="g_ultimateseo_smtp_options[password]" value="<?php echo esc_attr($options['password'] ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Encryption:</th>
                        <td>
                            <select name="g_ultimateseo_smtp_options[encryption]">
                                <option value="tls" <?php selected($options['encryption'] ?? '', 'tls'); ?>>TLS</option>
                                <option value="ssl" <?php selected($options['encryption'] ?? '', 'ssl'); ?>>SSL</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Port:</th>
                        <td><input type="number" name="g_ultimateseo_smtp_options[port]" value="<?php echo esc_attr($options['port'] ?? '587'); ?>" required></td>
                    </tr>
                    <tr>
                        <th>From Email:</th>
                        <td><input type="email" name="g_ultimateseo_smtp_options[from_email]" value="<?php echo esc_attr($options['from_email'] ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th>From Name:</th>
                        <td><input type="text" name="g_ultimateseo_smtp_options[from_name]" value="<?php echo esc_attr($options['from_name'] ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th>SMTP Authentication:</th>
                        <td>
                            <input type="checkbox" name="g_ultimateseo_smtp_options[smtp_auth]" value="1" <?php checked($options['smtp_auth'] ?? false, true); ?>>
                            <label>Enable SMTP Authentication (Recommended)</label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

        <?php elseif ($active_tab == 'send_test_email') : ?>

            <!-- Send Test Email Tab Content -->
            <?php include 'send_test_email_tab.php'; ?>

        <?php elseif ($active_tab == 'dmarc_dkim_check') : ?>

            <!-- DMARC & DKIM Check Tab Content -->
            <?php include 'dmarc_dkim_check_tab.php'; ?>

        <?php elseif ($active_tab == 'email_logs') : ?>

            <!-- Email Logs Tab Content -->
            <?php gseo_display_email_logs(); ?>

        <?php endif; ?>
    </div>
<?php
}
