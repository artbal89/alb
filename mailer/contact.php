<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'));
    $tel = trim(htmlspecialchars($_POST['tel'] ?? '', ENT_QUOTES, 'UTF-8'));
    $subject = trim(htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'));
    $message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

    // Check for empty fields
    if (empty($name) || empty($tel) || empty($email) || empty($subject) || empty($message)) {
        header("Location: https://artbal89.github.io/alb?error=empty");
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: https://artbal89.github.io/alb?error=invalid_email");
        exit;
    }

    // Validate phone number (example regex for numeric values, customize as needed)
    if (!preg_match('/^\+?[0-9]{10,15}$/', $tel)) {
        header("Location: https://artbal89.github.io/alb?error=invalid_tel");
        exit;
    }

    // Load previous submissions
    $submissions = loadSubmissions();

    // Time frame for checking repeated submissions (in seconds)
    $timeFrame = 3600; // 1 hour
    $currentTime = time();

    // Check for repeated submissions
    $blacklisted = false;
    foreach ($submissions as $submission) {
        if (
            $submission['email'] === $email &&
            ($currentTime - $submission['timestamp']) < $timeFrame
        ) {
            $blacklisted = true;
            break;
        }
    }

    // If blacklisted, add email to blacklist and stop processing
    if ($blacklisted) {
        addToBlacklist($email);
        header("Location: https://artbal89.github.io/alb?error=spam");
        exit;
    }

    // Admin email
    $toAdmin = "balingitartchristian@gmail.com";
    $messageAdmin = generateAdminMessage($name, $tel, $email, $message);
    $headersAdmin = "From: $name <$email>\r\n";
    $headersAdmin .= "Reply-To: $email\r\n";
    $headersAdmin .= "MIME-Version: 1.0\r\n";
    $headersAdmin .= "Content-Type: text/html; charset=UTF-8\r\n";

    $adminEmailSent = mail($toAdmin, "Transfers Request: $name", $messageAdmin, $headersAdmin);

    // User confirmation email
    $toUser = $email;
    $subjectUser = "Confirmation";
    $messageUser = generateUserConfirmation($name);
    $headersUser = "From: Costa Palawan Resort <no-reply@costapalawanresort.com>\r\n";
    $headersUser .= "MIME-Version: 1.0\r\n";
    $headersUser .= "Content-Type: text/html; charset=UTF-8\r\n";

    $userEmailSent = mail($toUser, $subjectUser, $messageUser, $headersUser);

    // Redirect after submission
    if ($adminEmailSent && $userEmailSent) {
        header("Location: https://artbal89.github.io/alb");
    } else {
        error_log("Email sending failed for $email"); // Log error for admin review
        header("Location: https://artbal89.github.io/alb?error=mail");
    }
    exit;
}

// Generate admin email message
function generateAdminMessage($name, $tel, $email, $message) {
    return "
<html>
<head>
  <title>alb Inquiry: $name</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #434753;'>
  <div style='max-width: 80%; margin: 0 auto; background-color: #222; color: #ffffff; padding: 20px;'>
    <h2>alb Inquiry</h2>
    <table>
      <tr><td><strong>Name:</strong></td><td>$name</td></tr>
      <tr><td><strong>Telephone:</strong></td><td>$tel</td></tr>
      <tr><td><strong>Email:</strong></td><td>$email</td></tr>
    </table>
    <p style='margin: 20px 0;'>$message</p>
  </div>
</body>
</html>";
}

// Generate user confirmation email
function generateUserConfirmation($name) {
    return "
<html>
<head></head>
<body style='font-family: Arial, sans-serif; background-color: #434753;'>
  <div style='max-width: 600px; margin: 0 auto; background-color: #222; color: #ffffff; padding: 20px;'>
    <h2>Thank You, $name!</h2>
    <p>We have received your transfer request. Our team will contact you shortly to confirm the details.</p>
  </div>
</body>
</html>";
}

// Load previous submissions
function loadSubmissions() {
    $file = "submissions.json";
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return $data ? json_decode($data, true) : [];
    }
    return [];
}

// Add email to the blacklist
function addToBlacklist($email) {
    $file = "blacklist.json";
    $blacklist = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $blacklist[] = $email;
    file_put_contents($file, json_encode($blacklist));
}
?>
