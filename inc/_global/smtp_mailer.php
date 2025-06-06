<?php
// inc/_global/smtp_mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Attempt to send via SMTP.
 * Returns true on success, or an error message (string) on failure.
 */
function send_smtp_mail(string $to, string $toName, string $subject, string $body, array $site)
{
    $mail = new PHPMailer(true);

    try {
        // Tell PHPMailer to show errors directly (for debugging)
        // You can comment out these two lines in production
        $mail->SMTPDebug  = 2;                      // 0 = off (for production), 2 = client and server messages
        $mail->Debugoutput= function($str, $level) {
            // Collect debug output in a global so we can return it
            global $phpmailer_debug_output;
            $phpmailer_debug_output .= trim($str) . "\n";
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host       = $site['smtp_host'];         // e.g. server201.web-hosting.com
        $mail->SMTPAuth   = true;
        $mail->Username   = $site['smtp_user'];         // e.g. support@demonscriptz.com
        $mail->Password   = $site['smtp_pass'];         // e.g. VyHWy^HZPZZq
        $mail->SMTPSecure = $site['smtp_encryption'];   // 'ssl' or 'tls'
        $mail->Port       = (int)$site['smtp_port'];    // e.g. 465

        // Recipients
        $mail->setFrom($site['mail_from_address'], $site['mail_from_name']);
        $mail->addAddress($to, $toName);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->CharSet = 'UTF-8';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // If PHPMailer exception, return its message + any debug output
        global $phpmailer_debug_output;
        $errorMsg = 'PHPMailer Error: ' . $e->getMessage();
        if (!empty($phpmailer_debug_output)) {
            $errorMsg .= "\nDebug Output:\n" . $phpmailer_debug_output;
        }
        return $errorMsg;
    }
}
