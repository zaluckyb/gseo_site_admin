<?php
if (!defined('ABSPATH')) exit;

//security/remove-wp-version.php

$options = get_option('gseo_security_settings', []);
if (!empty($options['remove_wp_version'])) {
    remove_action('wp_head', 'wp_generator');
    add_filter('the_generator', '__return_empty_string');
}
