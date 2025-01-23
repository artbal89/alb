<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data with XSS protection
    $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $tel = htmlspecialchars($_POST['tel'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8');

    // Check for empty fields
    if (empty($name) || empty($tel) || empty($email) || empty($subject) || empty($message)) {
        header("Location: https://costapalawanresort.com/form?error=empty");
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: https://costapalawanresort.com/form?error=invalid_email");
        exit;
    }

    // Define recipient and prepare email content
    $toAdmin = "balingitartchistian@gmail.com";
    $messageAdmin = "
<html>
<head>
  <title>Contact Form Submission: $name</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #434753; padding: 0; margin: 0;'>
  <div style='max-width: 600px; margin: 0 auto; background-color: #222; color: #ffffff;'>

    <!-- Header -->
    <div style='text-align: center; padding: 20px; background-color: #1b1b1b;'>
      <img src='https://costapalawanresort.com/images/logo.png' alt='Costa Palawan Logo' style='width: 150px;'>
    </div>

    <!-- Email Content -->
    <div style='padding: 20px; background-color: #333333; color: #ffffff;'>
      <h2 style='margin: 20px 0; font-size: 20px; color: #00aaff;'>New Contact Form Submission</h2>
      <table style='width: 100%; color: #ffffff; font-size: 16px;'>
        <tr>
          <td style='padding: 10px 0; width: 30%;'><strong>Name:</strong></td>
          <td style='padding: 10px 10px;'>$name</td>
        </tr>
        <tr>
          <td style='padding: 10px 0;'><strong>Phone:</strong></td>
          <td style='padding: 10px 10px;'>$tel</td>
        </tr>
        <tr>
          <td style='padding: 10px 0;'><strong>Email:</strong></td>
          <td style='padding: 10px 10px;'>$email</td>
        </tr>
        <tr>
          <td style='padding: 10px 0;'><strong>Subject:</strong></td>
          <td style='padding: 10px 10px;'>$subject</td>
        </tr>
      </table>
      <div style='margin: 30px 0; padding: 15px; background-color: #444444; border-radius: 5px; border-left: 5px solid #0078d4;'>
        <p style='margin: 0; font-size: 16px; color: #bbbbbb;'>$message</p>
      </div>
    </div>

    <!-- Footer -->
    <div style='padding: 15px; text-align: center; font-size: 12px; background-color: #1b1b1b; color: #888888;'>
      <p style='margin: 10px 0 0;'>&copy; 2025 Costa Palawan Resort. All rights reserved.</p>
    </div>
  </div>
</body>
</html>";

    $headersAdmin = "From: $name <$email>\r\n";
    $headersAdmin .= "Reply-To: $email\r\n";
    $headersAdmin .= "MIME-Version: 1.0\r\n";
    $headersAdmin .= "Content-Type: text/html; charset=UTF-8\r\n";

    $adminEmailSent = mail($toAdmin, "Contact Form: $subject", $messageAdmin, $headersAdmin);

    // Confirmation email for the user
    $toUser = $email;
    $subjectUser = "Thank you for contacting us!";
    $messageUser = "
<html>
<head></head>
<body style='font-family: Arial, sans-serif; background-color: #434753; padding: 0; margin: 0;'>
  <div style='max-width: 600px; margin: 0 auto; background-color: #222; color: #ffffff;'>

    <!-- Header -->
    <div style='text-align: center; padding: 20px; background-color: #1b1b1b;'>
      <img src='https://costapalawanresort.com/images/logo.png' alt='Costa Palawan Logo' style='width: 150px;'>
    </div>

    <!-- Email Content -->
    <div style='padding: 20px; background-color: #333333; color: #ffffff;'>
      <h2 style='color: #00aaff; text-align: center; font-size: 24px;'>Thank You!</h2>
      <p style='font-size: 16px;'>Hi <strong>$name</strong>,</p>
      <p style='font-size: 16px; color: #bbbbbb;'>We have received your message and will get back to you shortly. Thank you for reaching out to Costa Palawan Resort!</p>
      <p style='font-size: 16px;'>Best regards,</p>
      <p style='font-size: 16px;'><strong>Costa Palawan Resort Team</strong></p>
    </div>

    <!-- Footer -->
    <div style='padding: 15px; text-align: center; font-size: 12px; background-color: #1b1b1b; color: #888888;'>
      <p style='margin: 10px 0 0;'>&copy; 2025 Costa Palawan Resort. All rights reserved.</p>
    </div>
  </div>
</body>
</html>";

    $headersUser = "From: Costa Palawan Resort <no-reply@costapalawanresort.com>\r\n";
    $headersUser .= "MIME-Version: 1.0\r\n";
    $headersUser .= "Content-Type: text/html; charset=UTF-8\r\n";

    $userEmailSent = mail($toUser, $subjectUser, $messageUser, $headersUser);

    // Redirect after submission
    if ($adminEmailSent && $userEmailSent) {
        header("Location: https://costapalawanresort.com/sent");
    } else {
        header("Location: https://costapalawanresort.com/form?error=mail");
    }
    exit;
}
?>
