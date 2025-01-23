<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data with XSS protection
    $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $contact = htmlspecialchars($_POST['contact'] ?? '', ENT_QUOTES, 'UTF-8');
    $subjectAdmin = htmlspecialchars($_POST['subject'] ?? 'Inquiry', ENT_QUOTES, 'UTF-8');
    $comments = htmlspecialchars($_POST['comments'] ?? '', ENT_QUOTES, 'UTF-8');
    $arrdate = htmlspecialchars($_POST['arrdate'] ?? '', ENT_QUOTES, 'UTF-8');
    $arrtime = htmlspecialchars($_POST['arrtime'] ?? '', ENT_QUOTES, 'UTF-8');
    $depdate = htmlspecialchars($_POST['depdate'] ?? '', ENT_QUOTES, 'UTF-8');
    $deptime = htmlspecialchars($_POST['deptime'] ?? '', ENT_QUOTES, 'UTF-8');
	$pax = htmlspecialchars($_POST['pax'] ?? '', ENT_QUOTES, 'UTF-8');

    // Check for empty fields
    if (empty($name) || empty($contact) || empty($email) || empty($arrdate) || empty($arrtime) || empty($depdate) || empty($deptime) || empty($pax)) {
        header("Location: https://costapalawanresort.com/form?error=empty");
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: https://costapalawanresort.com/form?error=invalid_email");
        exit;
    }

    // Load previous submissions from file or database
    $submissions = loadSubmissions(); // Implement this function to load previous submissions

    // Time frame for checking repeated submissions (in seconds, e.g., 3600 for 1 hour)
    $timeFrame = 3600; // 1 hour

    // Check for repeated submissions from the same email address
    $blacklisted = false;
    $currentTime = time();
    foreach ($submissions as $submission) {
        if ($submission['email'] === $email && ($currentTime - $submission['timestamp']) < $timeFrame) {
            $blacklisted = true;
            break;
        }
    }

    // If blacklisted, add the email address to the blacklist
    if ($blacklisted) {
        addToBlacklist($email); // Implement this function to add email to the blacklist
        header("Location: https://costapalawanresort.com/form?error=spam");
        exit;
    }

    // If not blacklisted, process the form submission as usual
    $toAdmin = "albalingit@costapalawanresort.com, frontoffice@costapalawanresort.com";
    $messageAdmin = "
<html>
<head>
  <title>Transfer Request: $name</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #434753; padding: 0; margin: 0;'>
  <div style='max-width: 80%; margin: 0 auto; background-color: #222; overflow: hidden; color: #ffffff; height: 100vh; display: flex; flex-direction: column;'>

    <!-- Header Section -->
    <div style='text-align: center; padding: 20px; background-color: #1b1b1b;'>
      <img src='https://costapalawanresort.com/images/logo.png' alt='Costa Palawan Logo' style='width: 150px;'>
    </div>

    <!-- Email Content Section -->
    <div style='flex: 1; padding: 20px; background-color: #333333; color: #ffffff;'>
      <h2 style='margin: 20px 0; font-size: 20px; color: #00aaff;'>Guest Transfers Request</h2>
      
      <table style='width: 100%; color: #ffffff; font-size: 16px;'>
        <tr>
          <td style='padding: 10px 0; width: 25%;'><strong>Name:</strong></td>
          <td style='padding: 10px 10px; width: 50%;'>$name</td>
        </tr>
		
		<tr>
          <td style='padding: 10px 0; width: 25%;'><strong>Number of Pax:</strong></td>
		  <td style='padding: 10px 10px; width: 50%;'>$pax</td>
        </tr>
		
		<tr>
          <td style='padding: 10px 0; width: 25%;'><strong>Contact:</strong></td>
          <td style='padding: 10px 10px; width: 50%;'>$contact</td>
        </tr>
		
        <tr>
          <td style='padding: 10px 0; width: 25%;'><strong>Arrival:</strong></td>
          <td style='padding: 10px 10px; width: 50%;'>$arrdate | $arrtime</td>
        </tr>
        <tr>
          <td style='padding: 10px 0; width: 25%;'><strong>Departure:</strong></td>
          <td style='padding: 10px 10px; width: 50%;'>$depdate | $deptime</td>
        </tr>
      </table>

      <!-- Box containing the message -->
      <div style='margin: 30px 0; padding: 15px; background-color: #444444; border-radius: 5px; border-left: 5px solid #0078d4;'>
        <p style='margin: 0; font-size: 16px; color: #bbbbbb;'>$comments</p>
      </div>
    </div>

    <!-- Footer Section -->
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

    $adminEmailSent = mail($toAdmin, "Transfers Request: $name", $messageAdmin, $headersAdmin);

    // Send confirmation email to the user
    $toUser = $email;
    $subjectUser = "Confirmation";
    $messageUser = "
<html>
<head></head>
<body style='font-family: Arial, sans-serif; background-color: #434753; padding: 0; margin: 0;'>
  <div style='max-width: 600px; margin: 0 auto; background-color: #222; overflow: hidden; color: #ffffff;'>

    <!-- Header Section -->
    <div style='text-align: center; padding: 20px; background-color: #1b1b1b;'>
      <img src='https://costapalawanresort.com/images/logo.png' alt='Costa Palawan Logo' style='width: 150px;'>
    </div>

    <!-- Background Image Section -->
    <div>
      <img src='https://costapalawanresort.com/images/background/reply.jpg' alt='Background Image' style='width: 100%; display: block;'>
    </div>

    <!-- Email Content -->
    <div style='padding: 20px; background-color: #333333; color: #ffffff;'>
      <h2 style='color: #00aaff; text-align: center; font-size: 24px; margin-bottom: 20px;'>Mabuhay!</h2>
      <p style='font-size: 16px;'>Dear <strong>$name</strong>,</p>
      <p style='font-size: 16px; color: #bbbbbb;'>Thank you for sharing your arrival and departure details with us—it helps us perfectly schedule your transfers for a seamless journey. With our reliable service, you can relax and enjoy the ride, knowing your vacation begins the moment you land. <br><br> We’re excited to welcome you and make your experience unforgettable!</p>
      <p style='font-size: 16px; padding: 10px 0 0'>Best regards,</p>
      <p style='font-size: 16px;'><strong>Costa Palawan Resort</strong></p>
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

// Function to load previous submissions
function loadSubmissions() {
    // Example implementation: Load from file (replace with your database code)
    if (file_exists("submissions.json")) {
        return json_decode(file_get_contents("submissions.json"), true);
    }
    return [];
}

// Function to add email to the blacklist
function addToBlacklist($email) {
    $blacklistFile = "blacklist.json";
    $blacklist = file_exists($blacklistFile) ? json_decode(file_get_contents($blacklistFile), true) : [];
    $blacklist[] = $email;
    file_put_contents($blacklistFile, json_encode($blacklist));
}
?>
