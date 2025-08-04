<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// admin/db.php

// Activation hook for database setup
register_activation_hook(__FILE__, 'g_ultimateseo_db_activate');

function g_ultimateseo_db_activate() {
    g_ultimateseo_create_tables();
}

function g_ultimateseo_create_tables() {
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $charset_collate = $wpdb->get_charset_collate();

    // Define all database tables in an associative array
    $tables = [
        // Email Log Table
        "{$wpdb->prefix}gseo_email_log" => "CREATE TABLE {$wpdb->prefix}gseo_email_log (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email_to varchar(255) NOT NULL,
            subject varchar(255),
            message longtext,
            status varchar(50),
            error_log text,
            sent_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;",

        // 404 Errors Log Table
        "{$wpdb->prefix}gseo_404_errors" => "CREATE TABLE {$wpdb->prefix}gseo_404_errors (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            requested_url text NOT NULL,
            referer text DEFAULT NULL,
            user_agent text DEFAULT NULL,
            date_logged datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;",

        // Email Report Table
        "{$wpdb->prefix}gfseo_email_report" => "CREATE TABLE {$wpdb->prefix}gfseo_email_report (
            id bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name varchar(100),
            surname varchar(100),
            email varchar(100),
            date_added datetime DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;",

        // Login Logs Table
        "{$wpdb->prefix}gfseo_login_logs" => "CREATE TABLE {$wpdb->prefix}gfseo_login_logs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_login varchar(60) NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            login_time datetime NOT NULL,
            login_status varchar(20) NOT NULL,
            ip_address varchar(100) NOT NULL,
            PRIMARY KEY (id),
            KEY user_login (user_login),
            KEY login_time (login_time)
        ) $charset_collate;",

        // Analytics Detailed Table
        "{$wpdb->prefix}g_seo_analytics" => "CREATE TABLE {$wpdb->prefix}g_seo_analytics (
            id bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            visit_time datetime NOT NULL,
            ip_address varchar(100),
            visited_url text,
            referrer_url text,
            user_agent text,
            http_status smallint DEFAULT 200,
            visitor_type varchar(20) DEFAULT 'visitor'
        ) $charset_collate;",

        // Analytics Summary Table
        "{$wpdb->prefix}g_seo_analytics_summary" => "CREATE TABLE {$wpdb->prefix}g_seo_analytics_summary (
            id bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            visit_date date NOT NULL,
            day tinyint NOT NULL,
            month tinyint NOT NULL,
            year smallint NOT NULL,
            visit_count int NOT NULL DEFAULT 0
        ) $charset_collate;",
    ];

    // Loop through the array and create each table using dbDelta
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
}
