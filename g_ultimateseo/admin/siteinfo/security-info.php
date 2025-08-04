<?php
// Exit if accessed directly.

// admin/siteinfo/security-info.php
if (!defined('ABSPATH')) {
    exit;
}

function gfseo_display_security_info() {
    global $wpdb;

    $checks = [
        [
            'setting' => 'WordPress Debug Mode (WP_DEBUG)',
            'status' => defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled',
            'description' => 'WP_DEBUG should be disabled on production sites to prevent sensitive information leakage.',
            'level' => defined('WP_DEBUG') && WP_DEBUG ? 'Critical' : 'Good'
        ],
        [
            'setting' => 'File Editing Disabled (DISALLOW_FILE_EDIT)',
            'status' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? 'Yes' : 'No',
            'description' => 'Disabling file editing prevents attackers from altering PHP files directly from WordPress admin.',
            'level' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? 'Good' : 'Critical'
        ],
        [
            'setting' => 'Automatic Updates Disabled',
            'status' => defined('AUTOMATIC_UPDATER_DISABLED') && AUTOMATIC_UPDATER_DISABLED ? 'Yes' : 'No',
            'description' => 'Automatic updates keep your site secure by applying important security patches.',
            'level' => defined('AUTOMATIC_UPDATER_DISABLED') && AUTOMATIC_UPDATER_DISABLED ? 'Warning' : 'Good'
        ],
        [
            'setting' => 'Force SSL Admin',
            'status' => defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? 'Yes' : 'No',
            'description' => 'Forcing SSL on admin secures your backend login and sessions.',
            'level' => defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? 'Good' : 'Critical'
        ],
        [
            'setting' => 'HTTPS Enabled',
            'status' => is_ssl() ? 'Yes' : 'No',
            'description' => 'SSL/HTTPS encrypts data between visitors and your website.',
            'level' => is_ssl() ? 'Good' : 'Critical'
        ],
        [
            'setting' => 'Default admin User Exists',
            'status' => get_user_by('login', 'admin') ? 'Yes' : 'No',
            'description' => 'The default admin username should not be used to prevent brute-force attacks.',
            'level' => get_user_by('login', 'admin') ? 'Critical' : 'Good'
        ],
        [
            'setting' => 'XML-RPC Enabled',
            'status' => file_exists(ABSPATH . 'xmlrpc.php') ? 'Yes' : 'No',
            'description' => 'XML-RPC can be exploited for DDoS attacks or brute-force login attempts.',
            'level' => file_exists(ABSPATH . 'xmlrpc.php') ? 'Warning' : 'Good'
        ],
        [
            'setting' => 'REST API Access',
            'status' => (!is_wp_error(wp_remote_get(rest_url())) && wp_remote_retrieve_response_code(wp_remote_get(rest_url())) === 200) ? 'Accessible' : 'Restricted',
            'description' => 'Publicly accessible REST API can expose sensitive data.',
            'level' => (!is_wp_error(wp_remote_get(rest_url())) && wp_remote_retrieve_response_code(wp_remote_get(rest_url())) === 200) ? 'Warning' : 'Good'
        ],
        [
            'setting' => 'wp-config.php File Permissions',
            'status' => substr(sprintf('%o', fileperms(ABSPATH . 'wp-config.php')), -3),
            'description' => 'wp-config.php file should have permissions 400 or 440 to prevent unauthorized reading.',
            'level' => (substr(sprintf('%o', fileperms(ABSPATH . 'wp-config.php')), -3) <= 440) ? 'Good' : 'Critical'
        ],
        [
            'setting' => 'robots.txt Blocks Admin Area',
            'status' => (file_exists(ABSPATH . 'robots.txt') && strpos(file_get_contents(ABSPATH . 'robots.txt'), 'Disallow: /wp-admin/') !== false) ? 'Yes' : 'No',
            'description' => 'Blocking admin area via robots.txt helps prevent indexing of sensitive pages.',
            'level' => (file_exists(ABSPATH . 'robots.txt') && strpos(file_get_contents(ABSPATH . 'robots.txt'), 'Disallow: /wp-admin/') !== false) ? 'Good' : 'Warning'
        ],
        [
            'setting' => 'Directory Browsing Disabled',
            'status' => (file_exists(ABSPATH . '.htaccess') && strpos(file_get_contents(ABSPATH . '.htaccess'), 'Options -Indexes') !== false) ? 'Yes' : 'No',
            'description' => 'Disabling directory browsing prevents attackers from viewing directory contents.',
            'level' => (file_exists(ABSPATH . '.htaccess') && strpos(file_get_contents(ABSPATH . '.htaccess'), 'Options -Indexes') !== false) ? 'Good' : 'Critical'
        ],
    ];

    $score = ['Good' => 0, 'Warning' => 0, 'Critical' => 0];
    foreach ($checks as $check) {
        $score[$check['level']]++;
    }
    ?>
    <div class="wrap">
        <h1>WordPress Security Information</h1>

        <h2>Security Score</h2>
        <ul style="margin-bottom:30px;">
            <li>✅ Good: <strong><?php echo $score['Good']; ?></strong></li>
            <li>⚠️ Warning: <strong><?php echo $score['Warning']; ?></strong></li>
            <li>❌ Critical: <strong><?php echo $score['Critical']; ?></strong></li>
        </ul>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Security Setting</th>
                    <th>Status</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['setting']); ?></td>
                        <td>
                            <?php
                            $color = $item['level'] === 'Good' ? 'green' : ($item['level'] === 'Warning' ? 'orange' : 'red');
                            echo "<span style='color:{$color};'>{$item['status']}</span>";
                            ?>
                        </td>
                        <td><?php echo esc_html($item['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}