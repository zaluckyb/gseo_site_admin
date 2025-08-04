<?php
if (!defined('ABSPATH')) exit;

// admin/mail/functions.php

add_action('gseo_daily_smtp_check', 'gseo_perform_scheduled_smtp_check');

register_activation_hook(G_ULTIMATESEO_PATH . 'g_ultimateseo.php', 'gseo_schedule_daily_smtp_check');
register_deactivation_hook(G_ULTIMATESEO_PATH . 'g_ultimateseo.php', 'gseo_unschedule_daily_smtp_check');

function gseo_schedule_daily_smtp_check() {
    if (!wp_next_scheduled('gseo_daily_smtp_check')) {
        wp_schedule_event(time(), 'daily', 'gseo_daily_smtp_check');
    }
}

function gseo_unschedule_daily_smtp_check() {
    wp_clear_scheduled_hook('gseo_daily_smtp_check');
}

function gseo_perform_scheduled_smtp_check() {
    require_once G_ULTIMATESEO_PATH . 'admin/mail/email_logger.php';
    $result = gseo_test_smtp_connection();
    if ($result !== true) {
        wp_mail(get_option('admin_email'), 'SMTP Connection Failed', 'Scheduled SMTP check failed: ' . $result);
    }
}