<?php

session_start();

if (isset($_SESSION["customer"])) {
  header("Location: shop.php");
  exit();
}

$status = $_GET["status"] ?? "";

$statusMessages = [
  "unknown" => "Something went wrong. Please try again.",
  "verify" => "Something went wrong verifying your account. Please try again.",
  "code" => "Invalid verification code. Please try again.",
];

$statusSeverity = strpos($status, "success") !== false ? "alert-success" : "alert-danger";
$statusMsg = array_key_exists($status, $statusMessages) ? $statusMessages[$status] : "";

require_once "includes/database.php";
require_once "includes/smtp.php";

if (isset($_GET["resend"])) {
  $id = $_GET["customer_id"];
  $new_email = $_GET["email_address"];
  $new_randomness = md5(uniqid(rand(), true));

  $stmt = $conn->prepare(
      "UPDATE customer SET email_address = ?, randomness = ? WHERE customer_id = ? AND status = 'PENDING' LIMIT 1"
  );
  $stmt->bind_param("ssi", $new_email, $new_randomness, $id);
  $stmt->execute();

  if ($stmt->affected_rows == 0) {
    header("Location: verify.php?tp=wait&id=$id&status=unknown");
    exit();
  }

  header("Location: verify.php?tp=verify&id=$id");
  exit();
}

if (isset($_GET["verify"])) {
  $id = $_GET["customer_id"];
  $code = $_GET["verification_code"];

  header("Location: verify.php?tp=check&id=$id&cd=$code");
  exit();
}

if (!isset($_GET["tp"]) || !isset($_GET["id"])) {
  header("Location: index.php");
  exit();
}

$type = $_GET["tp"];
$id = $_GET["id"];

$stmt = $conn->prepare("SELECT first_name, email_address, status, randomness FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  header("Location: register.php?status=unknown");
  exit();
}

$result = $result->fetch_assoc();

$first_name = $result["first_name"];
$email_address = $result["email_address"];
$status = $result["status"];
$randomness = $result["randomness"];

if ($status === "VERIFIED") {
  header("Location: login.php?status=verified");
  exit();
}

if ($type === "verify") {
  $message =
    "
    <h1>Hi $first_name!</h1>
    <p>Thank you for registering to Ukay-Ukay Shopping! Please click the link below to verify your email address:</p>
    <a href='http://" .
    $_SERVER["HTTP_HOST"] .
    "/verify.php?tp=check&id=$id&cd=$randomness" .
    "'>Verify Email Address</a>
    <br /><br />
    <p>If the link above does not work, please copy and paste the verification code below:</p>
    <p><strong>$randomness</strong></p>
  ";

  $mail->addAddress($email_address);
  $mail->isHTML(true);
  $mail->Subject = "Account Verification";
  $mail->Body = $message;

  try {
    $mail->send();
  } catch (Exception $e) {
    header("Location: verify.php?tp=wait&id=$id&status=unknown");
    exit();
  }

  header("Location: verify.php?tp=wait&id=$id");
  exit();
}

if ($type === "check") {
  if (!isset($_GET["cd"])) {
    header("Location: index.php");
    exit();
  }

  $code = $_GET["cd"];

  if ($code !== $randomness) {
    header("Location: verify.php?tp=wait&id=$id&status=code");
    exit();
  }

  $stmt = $conn->prepare(
    "UPDATE customer SET status = 'VERIFIED', randomness = NULL WHERE customer_id = ? AND status = 'PENDING' AND randomness = ? LIMIT 1"
  );
  $stmt->bind_param("is", $id, $code);
  $stmt->execute();

  if ($stmt->affected_rows == 0) {
    header("Location: verify.php?tp=wait&id=$id&status=verify");
    exit();
  }

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

  try {
    $mail->send();
  } catch (Exception $e) {
    header("Location: verify.php?tp=wait&id=$id&status=unknown");
    exit();
  }

  header("Location: login.php?status=success_verify");
  exit();
}
?>

<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0" />
    <title>Verify - Ukay-Ukay Shopping</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD"
      crossorigin="anonymous" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
  </head>
  <body data-bs-theme="light">
    <header>
      <nav class="navbar navbar-expand-md bg-body-secondary">
        <div class="container my-1 my-md-3">
          <a
            href="index.php"
            class="navbar-brand my-auto h1">
            Ukay-Ukay Shopping
          </a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div
            class="collapse navbar-collapse d-md-flex flex-column gap-2"
            id="navbarNav">
            <?php if (isset($_SESSION["customer"])): ?>
            <div class="navbar-nav ms-auto">
              <a
                class="nav-link py-md-0"
                href="shop.php">
                Shop
              </a>
              <a
                class="nav-link py-md-0"
                href="cart.php">
                Cart (<?= isset($_SESSION["cart"]) ? array_sum($_SESSION["cart"]) : "0" ?>)
              </a>
              <a
                class="nav-link py-md-0"
                href="profile.php">
                Profile
              </a>
              <a
                class="nav-link py-md-0"
                href="logout.php">
                Logout
              </a>
            </div>
            <?php else: ?>
            <div class="navbar-nav ms-auto">
              <a
                class="nav-link py-md-0"
                href="shop.php">
                Shop
              </a>
              <a
                class="nav-link py-md-0"
                href="register.php">
                Register
              </a>
              <a
                class="nav-link py-md-0"
                href="login.php">
                Login
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <section class="container mb-5 mt-sm-5 px-3 py-5 px-sm-5 text-center bg-body-tertiary rounded">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="100"
          height="100"
          fill="currentColor"
          class="bi bi-envelope-check mb-5"
          viewBox="0 0 16 16">
          <path
            d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2H2Zm3.708 6.208L1 11.105V5.383l4.708 2.825ZM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2-7-4.2Z" />
          <path
            d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686Z" />
        </svg>
        <h1 class="text-body-emphasis">Verify your account</h1>
      </section>

      <section class="container mb-5 mt-sm-5 p-3 p-sm-5 bg-body-tertiary rounded">
        <div class="row gap-3">
          <?php if (isset($_GET["status"])): ?>
            <div class="row justify-content-center mb-3">
              <div class="col-lg-6">
                <div
                  class="alert <?= $statusSeverity ?>"
                  role="alert">
                  <?= $statusMsg ?>
                </div>
              </div>
            </div>
            <?php endif; ?>

          <div class="col-lg-8 mx-auto text-center">
            <h2 class="text-body-emphasis mb-3">You're almost there!</h2>
          </div>

          <p class="col-lg-8 mx-auto fs-5 text-muted mb-3">
            We've sent you an email (<em><?= $email_address ?></em>) with a verification link. Please click on the link to verify your account. If you don't see the email, please check your spam folder
          </p>

          <p class="col-lg-8 mx-auto fs-5 text-muted mb-3">
            If you still can't find the email, or if you entered the wrong email address, you can resend the email by entering your email address below.
          </p>

          <form
            action="<?= $_SERVER["PHP_SELF"] ?>"
            method="get"
            class="col-lg-6 mx-auto needs-validation"
            novalidate>
            <input
              type="hidden"
              name="customer_id"
              value="<?= $id ?>" />
            <label
              for="email_address"
              class="form-label">
              Email Address
            </label>
            <div class="input-group">
              <input
                type="email"
                class="form-control"
                id="email_address"
                name="email_address"
                value="<?= $email_address ?>"
                required />
              <button
                class="btn btn-primary px-3 rounded-end"
                type="submit"
                name="resend">
                Resend
              </button>
              <div class="invalid-feedback">Please provide a valid email.</div>
            </div>
          </form>

          <p class="col-lg-8 mx-auto fs-5 text-muted mb-3">
            If the verification link does not work, you can enter the verification code below instead.
          </p>

          <form
            action="<?= $_SERVER["PHP_SELF"] ?>"
            method="get"
            class="col-lg-6 mx-auto needs-validation"
            novalidate>
            <input
              type="hidden"
              name="customer_id"
              value="<?= $id ?>" />
            <label
              for="verification_code"
              class="form-label">
              Verification Code
            </label>
            <div class="input-group">
              <input
                type="text"
                class="form-control"
                id="verification_code"
                name="verification_code"
                required />
                <button
                class="btn btn-primary px-3 rounded-end"
                type="submit"
                name="verify">
                Verify
              </button>
              <div class="invalid-feedback">Please provide a valid verification code.</div>
            </div>
          </form>
        </div>
      </section>
    </main>

    <footer class="container py-3 mt-5 border-top text-center text-body-secondary">
      <p>© 2023 Ukay-Ukay Shopping</p>
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
      crossorigin="anonymous"></script>

    <script>
      (() => {
        "use strict";

        const forms = document.querySelectorAll(".needs-validation");

        Array.from(forms).forEach((form) => {
          form.addEventListener(
            "submit",
            (event) => {
              if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
              }

              form.classList.add("was-validated");
            },
            false
          );
        });
      })();

      const theme = localStorage.getItem("theme");
      if (theme) document.body.dataset.bsTheme = theme;
    </script>
  </body>
</html>