<?php

namespace App\Support;

use App\Models\Setting;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    /**
     * @throws Exception
     */
    public static function send(string $to, string $toName, string $subject, string $htmlBody, ?string $replyTo = null): void
    {
        $settings = Setting::current();

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = (string) ($settings->mailer_host ?: env('PHPMAILER_HOST'));
        $mail->Port = (int) ($settings->mailer_port ?: env('PHPMAILER_PORT', 587));
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($settings->mailer_username ?: env('PHPMAILER_USERNAME'));
        $mail->Password = (string) ($settings->mailer_password ?: env('PHPMAILER_PASSWORD'));

        $encryption = strtolower((string) ($settings->mailer_encryption ?: env('PHPMAILER_ENCRYPTION', 'tls')));
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $fromAddress = (string) ($settings->mailer_from_address ?: env('PHPMAILER_FROM_ADDRESS'));
        $fromName = (string) ($settings->mailer_from_name ?: env('PHPMAILER_FROM_NAME', config('app.name')));

        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($to, $toName);

        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        /* Logo artık şablonda public URL ile gider; CID yedek (eski istemciler) olarak kalır. */
        if ($settings->logo) {
            $logoPath = storage_path('app/public/' . ltrim((string) $settings->logo, '/'));
            if (is_file($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'bkd-logo');
            }
        }

        $mail->send();
    }
}

