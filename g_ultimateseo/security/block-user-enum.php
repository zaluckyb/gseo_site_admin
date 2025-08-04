<?php
if (!defined('ABSPATH')) exit;

// /security/block-user-enum.php

$options = get_option('gseo_security_settings', []);
if (!empty($options['block_user_enum'])) {
    if (!is_admin() && preg_match('/author=([0-9]*)/i', $_SERVER['QUERY_STRING'])) {
        wp_redirect(home_url()); 
        exit;
    }
}
