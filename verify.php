<?php

require_once "includes/database.php";
require_once "includes/smtp.php";

if (isset($_GET["email_address"])) {
  $email_address = $_GET["email_address"];

  $stmt = $conn->prepare("SELECT customer_id, first_name FROM customer WHERE email_address = ?");
  $stmt->bind_param("s", $email_address);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 0) {
    header("Location: login.php?status=email");
    exit();
  }

  $result = $result->fetch_assoc();

  $customer_id = $result["customer_id"];
  $first_name = $result["first_name"];
  $stmt->fetch();

  if (isset($_GET["resend"]) && $_GET["resend"] == "true") {
    $message =
      "
      <h1>Hi, $first_name!</h1>
      <p>Thank you for registering to Ukay-Ukay Shopping. Please click the link below to verify your account.</p>
      <a href='http://" .
      $_SERVER["HTTP_HOST"] .
      "/verify.php?email_address=$email_address'>Verify Account</a>
    ";

    $mail->addAddress($email_address);
    $mail->isHTML(true);
    $mail->Subject = "Account Verification";
    $mail->Body = $message;

    if (!$mail->send()) {
      header("Location: login .php?status=unknown");
      exit();
    }

    header("Location: login.php?status=verify");
    exit();
  }

  $stmt = $conn->prepare("UPDATE customer SET status = 'VERIFIED' WHERE customer_id = ?");
  $stmt->bind_param("i", $customer_id);
  $stmt->execute();

  $message =
    "
    <h1>Welcome to Ukay-Ukay Shopping!</h1>
    <p>Your email address has been verified successfully. You may now login to your account.</p>
    <a href='http://" .
    $_SERVER["HTTP_HOST"] .
    "/shop.php'>Shop Now!</a>
  ";

  $mail->addAddress($email_address);
  $mail->isHTML(true);
  $mail->Subject = "Welcome to Ukay-Ukay Shopping!";
  $mail->Body = $message;

  if (!$mail->send()) {
    header("Location: login.php?status=unknown");
    exit();
  }

  header("Location: login.php?status=success_verify");
  exit();
} else {
  header("Location: index.php");
  exit();
}
