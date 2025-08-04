<?php
if (!defined('ABSPATH')) exit;

// security/strong-password.php

$options = get_option('gseo_security_settings', []);
if (!empty($options['strong_passwords'])) {
    add_action('user_profile_update_errors', 'gseo_validate_password', 10, 3);
    function gseo_validate_password($errors, $update, $user) {
        if (!empty($_POST['pass1'])) {
            if (strlen($_POST['pass1']) < 12 || !preg_match('/[A-Z]/', $_POST['pass1']) || !preg_match('/[0-9]/', $_POST['pass1'])) {
                $errors->add('weak_password', '<strong>ERROR</strong>: Password must be at least 12 chars, including numbers and uppercase letters.');
            }
        }
    }
}
