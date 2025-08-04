<?php
if (!defined('ABSPATH')) exit;

// security/disable-xmlrpc.php

$options = get_option('gseo_security_settings', []);
if (!empty($options['login_protection'])) {

    function gseo_check_failed_logins($username) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient = 'gseo_failed_login_' . md5($ip);
        $attempts = (int) get_transient($transient);

        if ($attempts >= 3) {
            wp_die('Your IP has been temporarily blocked due to multiple failed login attempts.');
        }
    }

    function gseo_increment_failed_logins($username) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient = 'gseo_failed_login_' . md5($ip);
        $attempts = (int) get_transient($transient);
        $attempts++;

        set_transient($transient, $attempts, HOUR_IN_SECONDS);
    }

    function gseo_reset_failed_logins($user_login, $user) {
        $ip = $_SERVER['REMOTE_ADDR'];
        delete_transient('gseo_failed_login_' . md5($ip));
    }

    add_action('wp_login_failed', 'gseo_increment_failed_logins');
    add_action('wp_authenticate', 'gseo_check_failed_logins', 30);
    add_action('wp_login', 'gseo_reset_failed_logins', 10, 2);
}
