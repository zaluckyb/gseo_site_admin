<?php

// admin/menu.php

add_action('admin_menu', 'gseo_register_menus');

function gseo_register_menus() {
    add_menu_page(
        'G Ultimate SEO Monitor',
        'SEO Monitor',
        'manage_options',
        'gseo-monitor',
        'gseo_monitor_main_page',
        'dashicons-shield-alt',
        80
    );

    add_submenu_page(
        'gseo-monitor',
        'Add Site',
        'Add Site',
        'manage_options',
        'gseo-add-site',
        'gseo_add_site_page'
    );

    add_submenu_page(
        'gseo-monitor',
        'Security Settings',
        'Security Settings',
        'manage_options',
        'gseo-security-settings',
        'gseo_security_settings_page'
    );

    // NEW: Add Sites Info submenu
    add_submenu_page(
        'gseo-monitor',
        'Sites Info',
        'Sites Info',
        'manage_options',
        'gseo-sites-info',
        'gseo_render_site_overview_page' // directly using the existing callback
    );
    add_submenu_page(
        'gseo-monitor',
        'Manage Authors',
        'Manage Authors',
        'manage_options',
        'gseo-authors',
        'gseo_render_authors_page' // Ensure this matches exactly your authors.php function name
    );
    add_submenu_page(
        'gseo-monitor',
        'Author Activity',
        'Author Activity',
        'manage_options',
        'gseo_author-activity',
        'gseo_render_author_activity_page'
    );
}

// Include siteauthors.php
add_action('admin_init', 'gseo_include_admin_files');

function gseo_include_admin_files() {
    require_once plugin_dir_path(__FILE__) . '/pages/siteauthors.php';
}

