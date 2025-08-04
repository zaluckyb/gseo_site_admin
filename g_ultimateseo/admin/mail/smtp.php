<?php
if (!defined('ABSPATH')) exit;

// admin/mail/smtp.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
}

require_once G_ULTIMATESEO_PATH . 'admin/mail/smtp_settings.php';
require_once G_ULTIMATESEO_PATH . 'admin/mail/email_logger.php';
require_once G_ULTIMATESEO_PATH . 'admin/mail/email_logs.php';
require_once G_ULTIMATESEO_PATH . 'admin/mail/functions.php';
// require_once G_ULTIMATESEO_PATH . 'admin/mail/send_test_email_tab.php';

// SMTP Email sending function
function g_ultimateseo_send_email_smtp($to, $subject, $message, $headers = '') {
    $options = get_option('g_ultimateseo_smtp_options');

    $mail = new PHPMailer();

    // SMTP configuration from settings
    $mail->isSMTP();
    $mail->Host       = $options['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $options['username'];
    $mail->Password   = $options['password'];
    $mail->SMTPSecure = ($options['encryption'] == 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $options['port'];

    // Email content
    $mail->setFrom($options['from_email'], $options['from_name']);
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->isHTML(true);

    // Optional headers
    if (!empty($headers)) {
        if (is_array($headers)) {
            foreach ($headers as $header) {
                $mail->addCustomHeader($header);
            }
        } elseif (is_string($headers)) {
            // If headers is already a string, split by line breaks
            $headers_array = explode("\r\n", $headers);
            foreach ($headers_array as $header_line) {
                if (!empty(trim($header_line))) {
                    $mail->addCustomHeader($header_line);
                }
            }
        }
        
    }

    if (!$mail->send()) {
        gseo_log_email($to, $subject, $message, 'failed', $mail->ErrorInfo);
        return false;
    }

    gseo_log_email($to, $subject, $message, 'success', '');
    return true;
}
