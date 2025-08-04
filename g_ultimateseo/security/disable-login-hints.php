<?php
if (!defined('ABSPATH')) exit;

// security/disable-login-hints.php

$options = get_option('gseo_security_settings', []);
if (!empty($options['disable_login_hints'])) {
    add_filter('login_errors', function() {
        return 'Invalid login credentials.';
    });
}
