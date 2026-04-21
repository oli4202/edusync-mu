<?php

namespace App\Core;

/**
 * Mailer — email sending helper.
 * Placeholder for future email functionality.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        // Basic PHP mail() wrapper — replace with PHPMailer if needed
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . (getenv('MAIL_FROM') ?: 'noreply@edusync.local') . "\r\n";

        return mail($to, $subject, $body, $headers);
    }
}
