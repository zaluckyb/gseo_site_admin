<?php
if (!defined('ABSPATH')) exit;

// admin/mail/email_logger.php

function gseo_log_email($to, $subject, $message, $status, $error_log = '') {
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'gseo_email_log', [
        'email_to' => $to,
        'subject' => $subject,
        'message' => $message,
        'status' => $status,
        'error_log' => $error_log
    ]);
}

function gseo_test_smtp_connection() {
    $options = get_option('g_ultimateseo_smtp_options');
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = $options['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $options['username'];
    $mail->Password = $options['password'];
    $mail->SMTPSecure = ($options['encryption'] == 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $options['port'];

    if ($mail->smtpConnect()) {
        $mail->smtpClose();
        return true;
    } else {
        error_log('SMTP Connection Test Failed: ' . $mail->ErrorInfo);
        return $mail->ErrorInfo;
    }
}
