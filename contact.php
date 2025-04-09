<?php
// --- START: Added Debug Logging ---
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 0); // Don't display errors to the browser
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', __DIR__ . '/cak_debug.log'); // Log errors to /www/cak_debug.log
// --- END: Added Debug Logging ---

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

// Variable to capture debug output
$debugOutput = '';

// Custom function to capture debug output
$mail->Debugoutput = function($str, $level) use (&$debugOutput) {
    // Also log debug output immediately as it happens
    error_log("PHPMailer Debug ($level): $str");
    $debugOutput .= "$str\n";
};

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Basic validation check
    if (empty($data)) {
        http_response_code(400);
        error_log("Contact form error: Received empty JSON data.");
        echo json_encode(['error' => 'Invalid request data.']);
        exit;
    }

    // More robust validation
    $required_fields = ['name', 'email', 'phone', 'zipcode', 'address', 'house_type'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        http_response_code(400);
        $error_message = 'Missing required fields: ' . implode(', ', $missing_fields);
        error_log("Contact form error: $error_message");
        echo json_encode(['error' => 'Venligst udfyld alle påkrævede felter.']); // User-friendly message
        exit;
    }

    // Server settings
    $mail->SMTPDebug = 2; // Still enable internal debugging level
    // $mail->SMTPDebug = 0; // Use 0 for production after debugging
    $mail->isSMTP();
    $mail->Host = 'send.one.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kontakt@cakisolering.dk';
    $mail->Password = 'Basse30/12'; // IMPORTANT: Use environment variables or config file
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('kontakt@cakisolering.dk', 'CAK Isolering Website');
    $mail->addAddress('kontakt@cakisolering.dk', 'CAK Isolering Inbox');

    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Ny Henvendelse fra Hjemmeside: ' . htmlspecialchars($data['name']);

    // Build HTML Body (keep existing styles)
    $body = '<!DOCTYPE html>
    <html lang="da">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ny Henvendelse</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333333;
                margin: 0;
                padding: 20px;
                background-color: #f4f4f4;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            h2 {
                color: #003366; /* Darker blue heading */
                border-bottom: 2px solid #eeeeee;
                padding-bottom: 10px;
                margin-top: 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            td {
                text-align: left;
                padding: 12px;
                border: 1px solid #dddddd;
            }
            .label {
                font-weight: bold;
                width: 150px; /* Adjust width if needed */
                background-color: #f9f9f9; /* Light background for labels */
                vertical-align: top; /* Align labels top */
                color: #555555;
            }
            .value {
                 vertical-align: top; /* Align values top */
            }
            .message-label {
                vertical-align: top;
            }
            .message-content {
                white-space: pre-wrap; /* Preserve line breaks in message */
                word-wrap: break-word;
            }
            .footer {
                margin-top: 20px;
                text-align: center;
                font-size: 0.9em;
                color: #777777;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Ny henvendelse modtaget</h2>
            <table>
                <tr><td class="label">Navn:</td><td class="value">' . htmlspecialchars($data['name']) . '</td></tr>
                <tr><td class="label">Email:</td><td class="value">' . htmlspecialchars($data['email']) . '</td></tr>
                <tr><td class="label">Telefon:</td><td class="value">' . htmlspecialchars($data['phone']) . '</td></tr>
                <tr><td class="label">Postnummer:</td><td class="value">' . htmlspecialchars($data['zipcode']) . '</td></tr>
                <tr><td class="label">Adresse:</td><td class="value">' . htmlspecialchars($data['address']) . '</td></tr>';

    // Map house type value to Danish text
    $houseTypeMap = [
        'villa' => 'Villa/Parcelhus',
        'townhouse' => 'Rækkehus',
        'apartment' => 'Etageejendom',
        'cottage' => 'Sommerhus',
        'other' => 'Andet'
    ];
    $houseTypeText = isset($houseTypeMap[$data['house_type']]) ? $houseTypeMap[$data['house_type']] : htmlspecialchars($data['house_type']);

    $body .= '<tr><td class="label">Boligtype:</td><td class="value">' . $houseTypeText . '</td></tr>';

    // Include message if provided
    $message = isset($data['message']) ? trim($data['message']) : '';
    if (!empty($message)) {
        $body .= '<tr><td class="label message-label">Besked:</td><td class="value message-content">' . nl2br(htmlspecialchars($message)) . '</td></tr>';
    }

    $body .= '</table>
            <div class="footer">
                <p>Denne email blev sendt fra kontaktformularen på cakisolering.dk</p>
            </div>
        </div>
    </body>
    </html>';

    $mail->Body = $body;
    // Plain text version
    $altBody = "Ny henvendelse:\nNavn: {$data['name']}\nEmail: {$data['email']}\nTelefon: {$data['phone']}\nPostnummer: {$data['zipcode']}\nAdresse: {$data['address']}\nBoligtype: {$houseTypeText}" . (!empty($message) ? "\nBesked: {$message}" : '');
    $mail->AltBody = $altBody;


    $mail->send();
    // Log success and debug info
    error_log("Mail successfully sent to {$mail->getToAddresses()[0][0]}. PHPMailer Debug Info:\n{$debugOutput}");

    // Send success response
    header('Content-Type: application/json');
    echo json_encode(['success' => 'Message has been sent']);

} catch (Exception $e) {
    http_response_code(500);
    // Log the detailed error and the captured debug output server-side
    error_log("Mailer Error: {$mail->ErrorInfo}\nPHPMailer Debug Info (Caught Exception):\n{$debugOutput}");
    // Send generic error message to the client
    header('Content-Type: application/json');
    echo json_encode(['error' => "Message could not be sent. Please try again later."]);
}
?>