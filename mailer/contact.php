<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Enable debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Sanitize inputs
    $name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'));
    $tel = trim(htmlspecialchars($_POST['tel'] ?? '', ENT_QUOTES, 'UTF-8'));
    $subject = trim(htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'));
    $message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

    // Validate inputs
    if (empty($name) || empty($tel) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    if (!preg_match('/^\+?[0-9]{10,15}$/', $tel)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number.']);
        exit;
    }

    // Send email to admin
    $toAdmin = "balingitartchristian@gmail.com";
    $subjectAdmin = "Transfers Request: $name";
    $messageAdmin = "
    <html>
    <head>
      <title>alb Inquiry</title>
    </head>
    <body>
      <h2>New Inquiry from $name</h2>
      <p><strong>Phone:</strong> $tel</p>
      <p><strong>Email:</strong> $email</p>
      <p><strong>Message:</strong></p>
      <p>$message</p>
    </body>
    </html>";
    $headersAdmin = "From: $name <$email>\r\n";
    $headersAdmin .= "Reply-To: $email\r\n";
    $headersAdmin .= "MIME-Version: 1.0\r\n";
    $headersAdmin .= "Content-Type: text/html; charset=UTF-8\r\n";

    $adminEmailSent = mail($toAdmin, $subjectAdmin, $messageAdmin, $headersAdmin);

    // Confirmation email to user
    $subjectUser = "Confirmation";
    $messageUser = "
    <html>
    <body>
      <h2>Thank You, $name!</h2>
      <p>We have received your request. Our team will contact you shortly.</p>
    </body>
    </html>";
    $headersUser = "From: Costa Palawan Resort <no-reply@costapalawanresort.com>\r\n";
    $headersUser .= "MIME-Version: 1.0\r\n";
    $headersUser .= "Content-Type: text/html; charset=UTF-8\r\n";

    $userEmailSent = mail($email, $subjectUser, $messageUser, $headersUser);

    // Respond to the AJAX request
    if ($adminEmailSent && $userEmailSent) {
        echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send the email.']);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
