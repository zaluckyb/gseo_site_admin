<?php
if (!defined('ABSPATH')) exit;

// security/disable-login-hints.php

$options = get_option('gseo_security_settings', []);
if (!empty($options['disable_rest_api'])) {
    add_filter('rest_authentication_errors', function($result) {
        if (!empty($result)) return $result;

        if (!is_user_logged_in()) {
            return new WP_Error('rest_forbidden', 'REST API disabled for anonymous users.', ['status' => 401]);
        }
        return $result;
    });
}
