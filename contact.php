<?php
header('Content-Type: application/json');

// Get form data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['name', 'email', 'phone', 'zipcode', 'address', 'house_type'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => 'All required fields must be filled out']);
        exit;
    }
}

// Prepare email content
$to = 'kontakt@cakisolering.dk';
$subject = 'Ny kontaktformular forespørgsel';
$message = "
<h2>Ny forespørgsel fra kontaktformularen</h2>
<p><strong>Navn:</strong> {$data['name']}</p>
<p><strong>Email:</strong> {$data['email']}</p>
<p><strong>Telefon:</strong> {$data['phone']}</p>
<p><strong>Postnummer:</strong> {$data['zipcode']}</p>
<p><strong>Adresse:</strong> {$data['address']}</p>
<p><strong>Boligtype:</strong> {$data['house_type']}</p>
";

if (!empty($data['message'])) {
    $message .= "<p><strong>Besked:</strong> {$data['message']}</p>";
}

// Email headers
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=utf-8',
    'From: ' . $data['email'],
    'Reply-To: ' . $data['email'],
    'X-Mailer: PHP/' . phpversion()
];

// Send email
$mail_sent = mail($to, $subject, $message, implode("\r\n", $headers));

if ($mail_sent) {
    echo json_encode(['message' => 'Email sent successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send email']);
}
?> 