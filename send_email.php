<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if the email parameter is provided in the POST data
  if (isset($_POST["email"])) {
    $to = $_POST["email"];
    $subject = "Test Email";
    $message = "This is a test email sent from the PHP script.";
    $headers = "From: shouttolearn@gmail.com"; // Replace with your actual email address

    // Send the email using PHP's mail function
    if (mail($to, $subject, $message, $headers)) {
      // Email sent successfully
      echo json_encode(["status" => "success", "message" => "Email sent successfully"]);
    } else {
      // Failed to send email
      echo json_encode(["status" => "error", "message" => "Failed to send email"]);
    }
  } else {
    // Email parameter is missing
    echo json_encode(["status" => "error", "message" => "Email address not provided"]);
  }
} else {
  // Invalid request method
  echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
