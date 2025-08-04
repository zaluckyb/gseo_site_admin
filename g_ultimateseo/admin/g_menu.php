<?php
// Exit if accessed directly

//admin/g_menu.php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'g_ultimate_seo_menu');

function g_ultimate_seo_menu() {
    // G Ultimate SEO Main Menu
    add_menu_page(
        __('SEO Settings', 'g-ultimate-seo'),
        __('G Ultimate SEO', 'g-ultimate-seo'),
        'manage_options',
        'g-ultimate-seo-settings',
        'g_ultimate_seo_settings_page',
        null,
        6
    );

    $options = get_option('g_ultimateseo_options');

    if (!empty($options['enable_organization_schema'])) {
        add_submenu_page(
            'g-ultimate-seo-settings',
            'Organization Schema',
            'Organization Schema',
            'manage_options',
            'g-ultimateseo_organization_schema',
            'g_ultimateseo_organization_schema_page'
        );
    }

    if (!empty($options['enable_schema'])) {
        add_submenu_page(
            'g-ultimate-seo-settings',
            'Post Schema Settings',
            'Post Schema',
            'manage_options',
            'g-ultimateseo-post-schema',
            'g_ultimateseo_post_schema_page'
        );
    }

    if (!empty($options['enable_images'])) {
        add_submenu_page(
            'g-ultimate-seo-settings',
            'Images Settings',
            'Images Settings',
            'manage_options',
            'g-ultimateseo-images',
            'g_ultimateseo_images_page'
        );
    }

    if (!empty($options['enable_page_visits'])) {
        add_submenu_page(
            'g-ultimate-seo-settings',
            'Page Visits',
            'Page Visits',
            'manage_options',
            'g-ultimateseo-page-visits',
            'g_ultimateseo_page_visits_page'
        );
    }

    add_submenu_page(
        'g-ultimate-seo-settings',
        __('Settings', 'g-ultimate-seo'),
        __('Settings', 'g-ultimate-seo'),
        'manage_options',
        'g-ultimate-seo-sub-settings',
        'g_settings_page'
    );

    // G Security Main Menu (Updated clearly with overview callback)
    add_menu_page(
        __('G Security', 'g-ultimate-seo'),
        __('G Security', 'g-ultimate-seo'),
        'manage_options',
        'gseo-security-settings-main',
        'gseo_render_security_overview_page',
        'dashicons-shield-alt',
        7
    );

    // Security Settings Submenu (clearly listed under G Security)
    add_submenu_page(
        'gseo-security-settings-main',
        'Security Settings',
        'Security Settings',
        'manage_options',
        'gseo-security-settings',
        'gseo_render_security_settings'
    );

    add_submenu_page(
        'gseo-security-settings-main',
        'Security Headers',
        'Security Headers',
        'manage_options',
        'gseo-security-headers',
        'gseo_render_security_headers_settings'
    );

    add_submenu_page(
        'gseo-security-settings-main',
        'Content Security Policy',
        'Content Security Policy',
        'manage_options',
        'gseo-csp-settings',
        'gseo_render_csp_settings_page'
    );

    add_submenu_page(
        'gseo-security-settings-main',
        '.htaccess Generator',
        '.htaccess Generator',
        'manage_options',
        'gseo-htaccess-generator',
        'gseo_render_htaccess_snippet_page'
    );

    add_submenu_page(
        'gseo-security-settings-main',
        'Security API Status',
        'Security API',
        'manage_options',
        'gseo-security-api-status',
        'gsecurity_api_status_page'
    );

    // NEW: G Email Main Menu clearly separated
    add_menu_page(
        __('SMTP Email Settings', 'g-ultimate-seo'),
        __('G Email', 'g-ultimate-seo'),
        'manage_options',
        'g-email-smtp-settings',
        'g_ultimateseo_smtp_settings_page',
        'dashicons-email-alt',
        8
    );
}
