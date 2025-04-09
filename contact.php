<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

// Variable to capture debug output
$debugOutput = '';

// Custom function to capture debug output
$mail->Debugoutput = function($str, $level) use (&$debugOutput) {
    $debugOutput .= "$str\n";
};

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate data
    if (!isset($data['name'], $data['email'], $data['phone'], $data['zipcode'], $data['address'], $data['house_type'])) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields except message are required.']);
        exit;
    }

    // Server settings
    $mail->SMTPDebug = 2; // Still enable debugging internally
    // $mail->SMTPDebug = 0; // Use 0 for production after debugging
    $mail->isSMTP();
    $mail->Host = 'send.one.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kontakt@cakisolering.dk';
    $mail->Password = 'Basse30/12'; // Consider using environment variables or a config file for sensitive data
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('kontakt@cakisolering.dk', 'CAK Isolering Website'); // More descriptive sender name
    $mail->addAddress('kontakt@cakisolering.dk', 'CAK Isolering Inbox'); // More descriptive recipient name

    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8'; // Ensure proper character encoding
    $mail->Subject = 'Ny Henvendelse fra Hjemmeside: ' . htmlspecialchars($data['name']); // More informative subject

    // Build HTML Body
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
    $houseTypeText = isset($houseTypeMap[$data['house_type']]) ? $houseTypeMap[$data['house_type']] : htmlspecialchars($data['house_type']); // Fallback to original value if not found

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
    // Optional: Add a plain text version for non-HTML mail clients
    $altBody = "Ny henvendelse:\nNavn: {$data['name']}\nEmail: {$data['email']}\nTelefon: {$data['phone']}\nPostnummer: {$data['zipcode']}\nAdresse: {$data['address']}\nBoligtype: {$houseTypeText}" . (!empty($message) ? "\nBesked: {$message}" : ''); // Use mapped text here too
    $mail->AltBody = $altBody;


    $mail->send();
    // Log debug info only if sending succeeded (or handle it based on needs)
    // error_log("PHPMailer Debug Info (Success):\n" . $debugOutput);

    // Send success response (ensure no output before this)
    header('Content-Type: application/json');
    echo json_encode(['success' => 'Message has been sent']);

} catch (Exception $e) {
    http_response_code(500);
    // Log the detailed error and the captured debug output server-side
    error_log("Mailer Error: {$mail->ErrorInfo}\nPHPMailer Debug Info (Error):\n{$debugOutput}");
    // Send generic error message to the client
    header('Content-Type: application/json');
    echo json_encode(['error' => "Message could not be sent. Please try again later."]);
}
?>