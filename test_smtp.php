<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'send.one.com'; // Updated SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'kontakt@cakisolering.dk'; // Your email
    $mail->Password = 'Basse30/12'; // Your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465; // Updated SMTP port

    // Recipients
    $mail->setFrom('kontakt@cakisolering.dk', 'Mailer');
    $mail->addAddress('kontakt@cakisolering.dk', 'Recipient Name');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email sent using PHPMailer.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>