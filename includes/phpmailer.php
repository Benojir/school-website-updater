<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$configPath = __DIR__ . '/../includes/config.php';

if (!file_exists($autoloadPath)) {
    die('Autoload file not found: ' . $autoloadPath);
}

if (!file_exists($configPath)) {
    die('Config file not found: ' . $configPath);
}

include_once $configPath;
include_once $autoloadPath;


define('SMTP', $websiteConfig['smtp_server']);
define('USERNAME', $websiteConfig['smtp_username']);
define('PASSWORD', $websiteConfig['smtp_password']);

function sendMailToMultipleRecipients($senderName, $subject, $htmlBody, $altBody, $recipients = [], $cc = [], $bcc = [])
{
    $mail = new PHPMailer(true); // Create a new instance every time

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = SMTP;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME;
        $mail->Password   = PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Sender
        $mail->setFrom(USERNAME, $senderName);

        // To Recipients
        foreach ($recipients as $email => $name) {
            $mail->addAddress($email, $name);
        }

        // CC (optional)
        foreach ($cc as $email => $name) {
            $mail->addCC($email, $name);
        }

        // BCC (optional)
        foreach ($bcc as $email => $name) {
            $mail->addBCC($email, $name);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        if ($mail->send()) {
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Mailer Error: ' . $mail->ErrorInfo
        ];
    }
}


function sendMail($senderName, $recipientAddress, $recipientName, $subject, $htmlBody, $altBody)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = SMTP;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME; // Use your Gmail
        $mail->Password   = PASSWORD;    // Use Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & recipient
        $mail->setFrom(USERNAME, $senderName);
        $mail->addAddress($recipientAddress, $recipientName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        if ($mail->send()) {
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Mailer Error: ' . $mail->ErrorInfo
        ];
    }
}
