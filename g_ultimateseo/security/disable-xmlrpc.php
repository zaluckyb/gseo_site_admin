<?php
if (!defined('ABSPATH')) exit;

// security/disable-xmlrpc.php

// Correctly hook into WordPress initialization action
add_action('init', 'gseo_disable_xmlrpc_based_on_setting');

function gseo_disable_xmlrpc_based_on_setting() {
    $options = get_option('gseo_security_settings', []);
    
    if (!empty($options['disable_xmlrpc'])) {
        // Completely disable XML-RPC functionality
        add_filter('xmlrpc_enabled', '__return_false');

        // Block direct access explicitly
        if (strpos($_SERVER['REQUEST_URI'], 'xmlrpc.php') !== false) {
            wp_die(
                'XML-RPC services are disabled on this site for security reasons.', 
                'XML-RPC Disabled', 
                ['response' => 403]
            );
        }
    }
}
