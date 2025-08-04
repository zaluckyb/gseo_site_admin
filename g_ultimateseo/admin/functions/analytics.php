<?php

// admin/functions/analytics.php

// Create Database for Analytics Tracking.
function g_ultimateseo_create_table() {
    // Check if the current user has the 'activate_plugins' capability
    if (!current_user_can('activate_plugins')) {
        wp_die('You do not have sufficient permissions to access this function.');
    }

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table 1: g_ultimateseo_analytics
    $table_name_1 = $wpdb->prefix . 'g_ultimateseo_analytics';
    $sql1 = "CREATE TABLE $table_name_1 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        ip_address varchar(55) DEFAULT '' NOT NULL,
        referrer varchar(255) DEFAULT '' NOT NULL,
        counted tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Table 2: gseo_analysis
    $table_name_2 = $wpdb->prefix . 'gseo_analysis';
    $sql2 = "CREATE TABLE $table_name_2 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        post_url text NOT NULL,
        post_title text,
        h1_count tinyint,
        h1_text LONGTEXT,
        h2_count tinyint,
        h2_text  LONGTEXT,
        h3_count tinyint,
        h3_text LONGTEXT,
        h4_count tinyint,
        h4_text LONGTEXT,
        h5_count tinyint,
        h5_text LONGTEXT,
        h6_count tinyint,
        h6_text LONGTEXT,
        page_excerpt TEXT,
        page_meta_tags LONGTEXT,
        post_content LONGTEXT,
        single_density LONGTEXT,
        double_density LONGTEXT,
        triple_density LONGTEXT,
        quadruple_density LONGTEXT,
        g_today INT,
        g_previous_day INT,
        g_past_30_days INT,
        g_past_60_days INT,
        g_past_90_days INT,
        g_past_120_days INT,
        g_past_150_days INT,
        g_past_180_days INT,
        g_total_visits INT,
        g_basic_seo_score INT,
        gseo_title_score INT,
        gseo_check_title_tag LONGTEXT,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_name_3 = $wpdb->prefix . 'g_ultimateseo_analytics_data';

    $sql3 = "CREATE TABLE IF NOT EXISTS $table_name_3 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        date DATE NOT NULL,
        visit_count INT NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_post_date (post_id, date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Execute table creation
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}