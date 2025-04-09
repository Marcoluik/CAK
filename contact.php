<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate data
    if (!isset($data['name'], $data['email'], $data['phone'], $data['zipcode'], $data['address'], $data['house_type'])) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'send.one.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kontakt@cakisolering.dk';
    $mail->Password = 'Basse30/12';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('kontakt@cakisolering.dk', 'Mailer');
    $mail->addAddress('kontakt@cakisolering.dk', 'Recipient Name');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Contact Form Submission';
    $mail->Body    = 'Name: ' . $data['name'] . '<br>Email: ' . $data['email'] . '<br>Phone: ' . $data['phone'] . '<br>Zipcode: ' . $data['zipcode'] . '<br>Address: ' . $data['address'] . '<br>House Type: ' . $data['house_type'];

    $mail->send();
    echo json_encode(['success' => 'Message has been sent']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>