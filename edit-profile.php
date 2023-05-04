<?php

session_start();

if (!isset($_SESSION["customer"])) {
  header("Location: login.php?status=login");
  exit();
}

$status = $_GET["status"] ?? "";

$statusMessages = [
  "success_picture" => "Your profile picture has been updated.",
  "success_information" => "Your profile information has been updated.",
  "success_password" => "Your password has been updated.",
  "picture_upload" => "Something went wrong while uploading your profile picture. Please try again.",
  "information_unknown" => "Something went wrong while updating your profile information. Please try again.",
  "password_unknown" => "Something went wrong while updating your password. Please try again.",
  "picture_empty" => "Please select a profile picture.",
  "picture_big" => "Your profile picture is too big.",
  "picture_type" => "Your profile picture must be a JPEG or PNG file.",
  "information_phone" => "Your phone number must be 11 digits long.",
  "password_current" => "Your current password is incorrect.",
  "password_new" => "Your new password must be different from your current password.",
  "password_match" => "Your new password does not match.",
  "password_length" => "Your password must be at least 8 characters long.",
];

$statusSeverity = strpos($status, "success") !== false ? "alert-success" : "alert-danger";
$statusMsg = array_key_exists($status, $statusMessages) ? $statusMessages[$status] : "";

require_once "includes/database.php";

$customer_id = $_SESSION["customer"];

$stmt = $conn->prepare(
  "SELECT first_name, middle_name, last_name, address, email_address, phone_number, customer_picture, password FROM customer WHERE customer_id = ?"
);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result(
  $first_name,
  $middle_name,
  $last_name,
  $address,
  $email_address,
  $phone_number,
  $customer_picture,
  $password
);
$stmt->fetch();

if (isset($_POST["update_picture"])) {
  $fileName = $_FILES["customer_picture_form"]["name"];
  $fileTmpName = $_FILES["customer_picture_form"]["tmp_name"];
  $fileSize = $_FILES["customer_picture_form"]["size"];
  $fileError = $_FILES["customer_picture_form"]["error"];
  $fileType = $_FILES["customer_picture_form"]["type"];

  $fileExt = explode(".", $fileName);
  $fileActualExt = strtolower(end($fileExt));

  $fileNameOnly = pathinfo($fileName, PATHINFO_FILENAME);

  if ($fileError === UPLOAD_ERR_NO_FILE || $fileSize === 0) {
    header("Location: edit-profile.php?status=picture_empty");
    exit();
  }

  if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE || $fileSize > 5000000) {
    header("Location: edit-profile.php?status=picture_big");
    exit();
  }

  if ($fileError !== UPLOAD_ERR_OK) {
    header("Location: edit-profile.php?status=picture_upload");
    exit();
  }

  if ($fileType === "image/jpeg" || $fileType === "image/jpg") {
    $image = imagecreatefromjpeg($fileTmpName);
  } elseif ($fileType === "image/png") {
    $image = imagecreatefrompng($fileTmpName);
  } else {
    header("Location: edit-profile.php?status=picture_type");
    exit();
  }

  imagejpeg($image, $fileTmpName, 80);

  $fileNameNew = uniqid() . "." . $fileActualExt;
  $fileDestination = "uploads/customer/" . $fileNameNew;

  if (!file_exists("uploads/customer/")) {
    mkdir("uploads/customer", 0755, true);
  }

  if ($customer_picture && $customer_picture !== $fileNameNew && file_exists("uploads/customer/$customer_picture")) {
    unlink("uploads/customer/$customer_picture");
  }

  move_uploaded_file($fileTmpName, $fileDestination);

  $stmt = $conn->prepare("UPDATE customer SET customer_picture = ? WHERE customer_id = ?");
  $stmt->bind_param("si", $fileNameNew, $customer_id);

  if (!$stmt->execute()) {
    header("Location: edit-profile.php?status=picture_upload");
    exit();
  }

  header("Location: edit-profile.php?status=success_picture");
  exit();
}

if (isset($_POST["update_information"])) {
  $first_name = $_POST["first_name"];
  $middle_name = $_POST["middle_name"];
  $last_name = $_POST["last_name"];
  $address = $_POST["address"];
  $phone_number = $_POST["phone_number"];

  if (!preg_match("/^[0-9]{11}$/", $phone_number)) {
    header("Location: edit-profile.php?status=information_phone");
    exit();
  }

  $stmt = $conn->prepare(
    "UPDATE customer SET first_name = ?, middle_name = ?, last_name = ?, address = ?, phone_number = ? WHERE customer_id = ?"
  );
  $stmt->bind_param("sssssi", $first_name, $middle_name, $last_name, $address, $phone_number, $customer_id);

  if (!$stmt->execute()) {
    header("Location: edit-profile.php?status=information_unknown");
    exit();
  }

  header("Location: edit-profile.php?status=success_information");
  exit();
}

if (isset($_POST["change_password"])) {
  $current_password = $_POST["current_password"];
  $new_password = $_POST["new_password"];
  $confirm_new_password = $_POST["confirm_new_password"];

  if (!password_verify($current_password, $password)) {
    header("Location: edit-profile.php?status=password_current");
    exit();
  }

  if (strlen($new_password) < 8) {
    header("Location: register.php?status=password_length");
    exit();
  }

  if (password_verify($new_password, $password)) {
    header("Location: edit-profile.php?status=password_new");
    exit();
  }

  if ($new_password !== $confirm_new_password) {
    header("Location: edit-profile.php?status=password_match");
    exit();
  }

  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
  $stmt->bind_param("si", $hashed_password, $customer_id);

  if (!$stmt->execute()) {
    header("Location: edit-profile.php?status=password_unknown");
    exit();
  }

  header("Location: edit-profile.php?status=success_password");
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
    <title>Edit Profile - Ukay-Ukay Shopping</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD"
      crossorigin="anonymous" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
    <style>
      img {
        object-fit: cover;
        object-position: top;
      }
    </style>
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
          class="bi bi-person-lines-fill mb-5"
          viewBox="0 0 16 16">
          <path
            d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z" />
        </svg>
        <h1 class="text-body-emphasis">Edit Profile</h1>
      </section>

      <div class="container mb-5 mt-sm-5 bg-body-tertiary rounded">
        <div class="row">
          <section class="col-lg-4 p-3 p-sm-5">
            <h2 class="text-body-emphasis mb-5">Profile Picture</h2>
            <?php if (isset($_GET["status"]) && strpos($_GET["status"], "picture") !== false): ?>
            <div
              class="alert <?= $statusSeverity ?> mb-5"
              role="alert">
              <?= $statusMsg ?>
            </div>
            <?php endif; ?>
            <div class="text-center mb-5">
              <img
                src="<?= !empty($customer_picture)
                  ? "uploads/customer/$customer_picture"
                  : "https://api.dicebear.com/6.x/initials/svg?seed=$first_name" ?>"
                alt="profile"
                class="rounded-circle"
                style="width: 80%; aspect-ratio: 1/1" />
            </div>
            <form action="<?php echo $_SERVER["PHP_SELF"]; ?>"
              method="post"
              enctype="multipart/form-data"
              class="needs-validation"
              novalidate>
              <div class="row row-cols-1 gy-3">
                <input
                  type="hidden"
                  name="MAX_FILE_SIZE"
                  value="5000000" />
                <div class="col">
                  <input
                    type="file"
                    class="form-control"
                    id="customer_picture_form"
                    name="customer_picture_form"
                    placeholder="profile"
                    accept=".jpg,.jpeg,.png"
                    required />
                  <div class="invalid-feedback">Please select a file to upload.</div>
                </div>
                <div class="col text-center">
                  <p>Accepted file types: <code>jpg</code>, <code>jpeg</code>, <code>png</code></p>
                  <p>Maximum accepted file size: <code>5MB</code></p>
                </div>
              </div>
              <div class="text-center mt-5">
                <button
                  type="submit"
                  class="btn btn-primary px-3 rounded-pill"
                  name="update_picture">
                  Update Profile Picture
                </button>
                <!-- <?php if (!empty($customer_picture)): ?>
                <button
                  type="submit"
                  class="btn btn-danger mt-2 px-3 rounded-pill"
                  name="delete_picture">
                  Delete Profile Picture
                </button>
                <?php endif; ?> -->
              </div>
            </form>
          </section>

          <section class="col-lg-4 p-3 p-sm-5">
            <h2 class="text-body-emphasis mb-5">Profile Information</h2>
            <?php if (isset($_GET["status"]) && strpos($_GET["status"], "info") !== false): ?>
            <div
              class="alert <?= $statusSeverity ?> mb-5"
              role="alert">
              <?= $statusMsg ?>
            </div>
            <?php endif; ?>
            <form
              action="<?php echo $_SERVER["PHP_SELF"]; ?>"
              method="post"
              class="needs-validation"
              novalidate>
              <div class="row row-cols-1 gy-3">
                <div class="col">
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
                    value="<?= $first_name ?>"
                    required />
                  <div class="invalid-feedback">Please provide a valid first name.</div>
                </div>
                <div class="col">
                  <label
                    for="middle_name"
                    class="form-label">
                    Middle name
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    id="middle_name"
                    name="middle_name"
                    value="<?= $middle_name ?>" />
                </div>
                <div class="col">
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
                    value="<?= $last_name ?>"
                    required />
                  <div class="invalid-feedback">Please provide a valid last name.</div>
                </div>
                <div class="col">
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
                    value="<?= $address ?>"
                    required />
                  <div class="invalid-feedback">Please provide a valid address.</div>
                </div>
                <div class="col">
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
                    value="<?= $email_address ?>"
                    readonly />
                  <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                <div class="col">
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
                    value="<?= $phone_number ?>"
                    required />
                  <div class="invalid-feedback">Please provide a valid phone number.</div>
                </div>
                <div class="text-center mt-5">
                  <button
                    class="btn btn-primary px-3 rounded-pill"
                    type="submit"
                    name="update_information">
                    Update Profile Information
                  </button>
                </div>
              </div>
            </form>
          </section>

          <section class="col-lg-4 p-3 p-sm-5">
            <h2 class="text-body-emphasis mb-5">Password</h2>
            <?php if (isset($_GET["status"]) && strpos($_GET["status"], "password") !== false): ?>
            <div
              class="alert <?= $statusSeverity ?> mb-5"
              role="alert">
              <?= $statusMsg ?>
            </div>
            <?php endif; ?>
            <form
              action="<?php echo $_SERVER["PHP_SELF"]; ?>"
              method="post"
              class="needs-validation mt-5"
              novalidate>
              <div class="row row-cols-1 gy-3">
                <div class="col">
                  <label
                    for="current_password"
                    class="form-label">
                    Current Password
                  </label>
                  <input
                    type="password"
                    class="form-control"
                    id="current_password"
                    name="current_password"
                    required />
                  <div class="invalid-feedback">Please enter your password</div>
                </div>
                <div class="col">
                  <label
                    for="new_password"
                    class="form-label">
                    New Password
                  </label>
                  <input
                    type="password"
                    class="form-control"
                    id="new_password"
                    name="new_password"
                    required />
                  <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                </div>
                <div class="col">
                  <label
                    for="confirm_new_password"
                    class="form-label">
                    Confirm New Password
                  </label>
                  <input
                    type="password"
                    class="form-control"
                    id="confirm_new_password"
                    name="confirm_new_password"
                    required />
                  <div class="invalid-feedback">Password does not match.</div>
                </div>
                <div class="text-center mt-5">
                  <button
                    class="btn btn-primary px-3 rounded-pill"
                    type="submit"
                    name="change_password">
                    Change Password
                  </button>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
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

      const newPassword = document.getElementById("new_password");
      const confirmNewPassword = document.getElementById("confirm_new_password");

      function validatePasswordStrength() {
        if (newPassword.value.length < 8) {
          newPassword.setCustomValidity("Password must be at least 8 characters long");
        } else {
          newPassword.setCustomValidity("");
        }
      }

      function validatePassword() {
        if (newPassword.value !== confirmNewPassword.value) {
          confirmNewPassword.setCustomValidity("Passwords Don't Match");
        } else {
          confirmNewPassword.setCustomValidity("");
        }
      }

      newPassword.addEventListener("keyup", validatePasswordStrength);
      newPassword.addEventListener("keyup", validatePassword);
      confirmNewPassword.addEventListener("keyup", validatePassword);

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
