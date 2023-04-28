<?php

session_start();

if (isset($_SESSION["customer"])) {
  header("Location: shop.php");
  exit();
}

$status = $_GET["status"] ?? "";

$statusMessages = [
  "unknown" => "Something went wrong while registering your account. Please try again.",
  "email" => "This email address is already registered. Do you mean to <a href='login.php'>login</a>?",
  "phone_number" => "Your phone number must be 11 digits long.",
  "password_match" => "Your password does not match.",
  "password_length" => "Your password must be at least 8 characters long.",
];

$statusSeverity = strpos($status, "success") !== false ? "alert-success" : "alert-danger";
$statusMsg = array_key_exists($status, $statusMessages) ? $statusMessages[$status] : "";

require_once "includes/database.php";

if (isset($_POST["register"])) {
  $first_name = $_POST["first_name"];
  $middle_name = $_POST["middle_name"];
  $last_name = $_POST["last_name"];
  $address = $_POST["address"];
  $email_address = $_POST["email_address"];
  $phone_number = $_POST["phone_number"];
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];

  $stmt = $conn->prepare("SELECT customer_id FROM customer WHERE email_address = ?");
  $stmt->bind_param("s", $email_address);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    header("Location: register.php?status=email");
    exit();
  }

  if (!preg_match("/^[0-9]{11}$/", $phone_number)) {
    header("Location: register.php?status=phone_number");
    exit();
  }

  if (strlen($password) < 8) {
    header("Location: register.php?status=password_length");
    exit();
  }

  if ($password !== $confirm_password) {
    header("Location: register.php?status=password_match");
    exit();
  }

  $password = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare(
    "INSERT INTO customer (first_name, middle_name, last_name, address, email_address, phone_number, password) VALUES (?, ?, ?, ?, ?, ?, ?)"
  );
  $stmt->bind_param(
    "sssssss",
    $first_name,
    $middle_name,
    $last_name,
    $address,
    $email_address,
    $phone_number,
    $password
  );

  if (!$stmt->execute()) {
    header("Location: register.php?status=unknown");
    exit();
  }

  require_once "includes/smtp.php";

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
    header("Location: register.php?status=unknown");
    exit();
  }

  header("Location: login.php?status=success_register");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0" />
    <title>Register - Ukay-Ukay Shopping</title>
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
        <div class="container my-3">
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
          class="bi bi-person-circle mb-5"
          viewBox="0 0 16 16">
          <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
          <path
            fill-rule="evenodd"
            d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
        </svg>
        <h1 class="text-body-emphasis">Register to Ukay-Ukay Shopping</h1>
      </section>

      <section class="container mb-5 mt-sm-5 p-3 p-sm-5 bg-body-tertiary rounded">
        <form
          action="<?php echo $_SERVER["PHP_SELF"]; ?>"
          method="post"
          class="needs-validation"
          novalidate>
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
          <div class="row g-3 justify-content-center mb-3">
            <div class="col-sm-4 col-lg-2">
              <label
                for="first_name"
                class="form-label">
                First name
              </label>
              <input
                type="text"
                class="form-control"
                id="first_name"
                name="first_name"
                required />
              <div class="invalid-feedback">Please provide a valid first name.</div>
            </div>
            <div class="col-sm-4 col-lg-2">
              <label
                for="middle_name"
                class="form-label">
                Middle name
              </label>
              <input
                type="text"
                class="form-control"
                id="middle_name"
                name="middle_name" />
            </div>
            <div class="col-sm-4 col-lg-2">
              <label
                for="last_name"
                class="form-label">
                Last name
              </label>
              <input
                type="text"
                class="form-control"
                id="last_name"
                name="last_name"
                required />
              <div class="invalid-feedback">Please provide a valid last name.</div>
            </div>
          </div>
          <div class="row justify-content-center mb-3">
            <div class="col-lg-6">
              <label
                for="address"
                class="form-label">
                Address
              </label>
              <input
                type="text"
                class="form-control"
                id="address"
                name="address"
                required />
              <div class="invalid-feedback">Please provide a valid address.</div>
            </div>
          </div>
          <div class="row g-3 justify-content-center mb-3">
            <div class="col-sm-6 col-lg-3">
              <label
                for="email_address"
                class="form-label">
                Email Address
              </label>
              <input
                type="email"
                class="form-control"
                id="email_address"
                name="email_address"
                required />
              <div class="invalid-feedback">Please provide a valid email.</div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <label
                for="phone_number"
                class="form-label">
                Phone Number
              </label>
              <input
                type="tel"
                class="form-control"
                id="phone_number"
                name="phone_number"
                required />
              <div class="invalid-feedback">Phone number must be 11 digits.</div>
            </div>
          </div>
          <div class="row g-3 justify-content-center">
            <div class="col-sm-6 col-lg-3">
              <label
                for="password"
                class="form-label">
                Password
              </label>
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                required />
              <div class="invalid-feedback">Password must be at least 8 characters long.</div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <label
                for="confirm_password"
                class="form-label">
                Confirm Password
              </label>
              <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                required />
              <div class="invalid-feedback">Password does not match.</div>
            </div>
          </div>
          <div class="text-center mt-5">
            <button
              class="btn btn-primary px-3 rounded-pill"
              type="submit"
              name="register">
              Register
            </button>
          </div>
        </form>
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

      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirm_password");

      function validatePasswordStrength() {
        if (password.value.length < 8) {
          password.setCustomValidity("Password must be at least 8 characters long");
        } else {
          password.setCustomValidity("");
        }
      }

      function validatePassword() {
        if (password.value !== confirmPassword.value) {
          confirmPassword.setCustomValidity("Passwords Don't Match");
        } else {
          confirmPassword.setCustomValidity("");
        }
      }

      password.addEventListener("keyup", validatePasswordStrength);
      password.addEventListener("keyup", validatePassword);
      confirmPassword.addEventListener("keyup", validatePassword);

      const phoneNumber = document.getElementById("phone_number");

      function validatePhoneNumber() {
        const regex = /^\d{11}$/;

        if (regex.test(phoneNumber.value)) {
          phoneNumber.setCustomValidity("");
        } else {
          phoneNumber.setCustomValidity("Phone number must be 11 digits");
        }
      }
      
      phoneNumber.addEventListener("keyup", validatePhoneNumber);

      const theme = localStorage.getItem("theme");
      if (theme) document.body.dataset.bsTheme = theme;
    </script>
  </body>
</html>
