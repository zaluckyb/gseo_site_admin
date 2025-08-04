<?php
if (!defined('ABSPATH')) {
    exit; // Security measure
}

// Admin and core includes
require_once G_ULTIMATESEO_PATH . 'admin/db.php';
require_once G_ULTIMATESEO_PATH . 'admin/g_menu.php';
require_once G_ULTIMATESEO_PATH . 'admin/menu_items/g_seo_settings.php';
require_once G_ULTIMATESEO_PATH . 'admin/menu_items/g_settings.php';
require_once G_ULTIMATESEO_PATH . 'admin/functions/analytics.php';
require_once G_ULTIMATESEO_PATH . 'admin/mail/smtp.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/siteinformation.php';
require_once G_ULTIMATESEO_PATH . 'admin/siteinfo/site-info-api.php';
require_once G_ULTIMATESEO_PATH . 'newsletter/newsletter-subscribe.php';
require_once G_ULTIMATESEO_PATH . 'admin/analytics/g_seo_analytics.php';
require_once G_ULTIMATESEO_PATH . 'security/securitysettings.php';
require_once G_ULTIMATESEO_PATH . 'security/security-headers-settings.php';
require_once G_ULTIMATESEO_PATH . 'security/security-headers.php';
require_once G_ULTIMATESEO_PATH . 'security/csp-settings.php';
require_once G_ULTIMATESEO_PATH . 'security/security-headers-htaccess.php';
require_once G_ULTIMATESEO_PATH . 'security/securitysettingsapi.php';
require_once G_ULTIMATESEO_PATH . 'security/security-overview.php';

// Conditional includes based on admin options
$options = get_option('g_ultimateseo_options');

if (!empty($options['enable_images'])) {
    require_once G_ULTIMATESEO_PATH . 'admin/functions/images.php';
}

if (!empty($options['enable_comments'])) {
    require_once G_ULTIMATESEO_PATH . 'admin/functions/comments.php';
}

if (!empty($options['enable_schema'])) {
    require_once G_ULTIMATESEO_PATH . 'admin/functions/posts_schema.php';
}

if (!empty($options['enable_organization_schema'])) {
    require_once G_ULTIMATESEO_PATH . 'admin/functions/organization_schema.php';
}

if (!empty($options['enable_emoji'])) {
    require_once G_ULTIMATESEO_PATH . 'admin/functions/emoji.php';
}

if (!empty($options['enable_page_visits'])) {
    require_once G_ULTIMATESEO_PATH . 'admin/functions/page_visits_function.php';
    require_once G_ULTIMATESEO_PATH . 'admin/functions/page_visits.php';
}

// Load security feature files conditionally based on security settings
$security_options = get_option('gseo_security_settings', []);

if (!empty($security_options['login_protection'])) {
    require_once G_ULTIMATESEO_PATH . 'security/loginsecurity.php';
}

if (!empty($security_options['disable_xmlrpc'])) {
    require_once G_ULTIMATESEO_PATH . 'security/disable-xmlrpc.php';
}

if (!empty($security_options['limit_admin_ip'])) {
    require_once G_ULTIMATESEO_PATH . 'security/restrict-admin-ip.php';
}

if (!empty($security_options['remove_wp_version'])) {
    require_once G_ULTIMATESEO_PATH . 'security/remove-wp-version.php';
}

if (!empty($security_options['block_user_enum'])) {
    require_once G_ULTIMATESEO_PATH . 'security/block-user-enum.php';
}

if (!empty($security_options['strong_passwords'])) {
    require_once G_ULTIMATESEO_PATH . 'security/strong-password.php';
}

if (!empty($security_options['disable_login_hints'])) {
    require_once G_ULTIMATESEO_PATH . 'security/disable-login-hints.php';
}

if (!empty($security_options['disable_rest_api'])) {
    require_once G_ULTIMATESEO_PATH . 'security/disable-rest-api.php';
}

if (!empty($security_options['security_headers'])) {
    require_once G_ULTIMATESEO_PATH . 'security/security-headers.php';
    

}