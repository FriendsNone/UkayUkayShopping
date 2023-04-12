<?php

session_start();
require_once "includes/database.php";

if (isset($_SESSION["customer"])) {
  $customer_id = $_SESSION["customer"];

  $stmt = $conn->prepare(
    "SELECT first_name, middle_name, last_name, address, email_address, phone_number, customer_picture FROM customer WHERE customer_id = ?"
  );
  $stmt->bind_param("i", $customer_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($first_name, $middle_name, $last_name, $address, $email_address, $phone_number, $customer_picture);
  $stmt->fetch();
} else {
  header("Location: login.php?error=login");
  exit();
}

if (isset($_POST["update_picture"])) {
  $fileName = $_FILES["customer_picture_form"]["name"];
  $fileTmpName = $_FILES["customer_picture_form"]["tmp_name"];
  $fileSize = $_FILES["customer_picture_form"]["size"];
  $fileError = $_FILES["customer_picture_form"]["error"];
  $fileType = $_FILES["customer_picture_form"]["type"];

  $fileExt = explode(".", $fileName);
  $fileActualExt = strtolower(end($fileExt));

  $allowed = ["jpg", "jpeg", "png"];

  $fileNameOnly = pathinfo($fileName, PATHINFO_FILENAME);

  if ($fileError === UPLOAD_ERR_NO_FILE || $fileSize === 0) {
    header("Location: edit-profile.php?error=picture_empty");
    exit();
  }

  if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE || $fileSize > 5000000) {
    header("Location: edit-profile.php?error=picture_big");
    exit();
  }

  if (!in_array($fileActualExt, $allowed)) {
    header("Location: edit-profile.php?error=picture_type");
    exit();
  }

  if ($fileError !== UPLOAD_ERR_OK) {
    header("Location: edit-profile.php?error=picture_upload");
    exit();
  }

  $fileNameNew = uniqid() . "." . $fileActualExt;
  $fileDestination = "uploads/customer/" . $fileNameNew;

  if (!file_exists("uploads/customer/")) {
    mkdir("uploads/customer", 0777, true);
  }

  if ($customer_picture && file_exists("uploads/customer/$customer_picture")) {
    unlink("uploads/customer/$customer_picture");
  }

  move_uploaded_file($fileTmpName, $fileDestination);

  $stmt = $conn->prepare("UPDATE customer SET customer_picture = ? WHERE customer_id = ?");
  $stmt->bind_param("si", $fileNameNew, $customer_id);

  if ($stmt->execute()) {
    header("Location: edit-profile.php?success=picture");
    exit();
  } else {
    $stmt->close();
    $conn->close();

    header("Location: edit-profile.php?error=picture_upload");
    exit();
  }
}

if (isset($_POST["update_information"])) {
  $first_name = $_POST["first_name"];
  $middle_name = $_POST["middle_name"];
  $last_name = $_POST["last_name"];
  $address = $_POST["address"];
  $phone_number = $_POST["phone_number"];

  $stmt = $conn->prepare(
    "UPDATE customer SET first_name = ?, middle_name = ?, last_name = ?, address = ?, phone_number = ? WHERE customer_id = ?"
  );
  $stmt->bind_param("sssssi", $first_name, $middle_name, $last_name, $address, $phone_number, $customer_id);

  if ($stmt->execute()) {
    header("Location: edit-profile.php?success=information");
    exit();
  } else {
    $stmt->close();
    $conn->close();

    header("Location: edit-profile.php?error=information_unknown");
    exit();
  }
}

if (isset($_POST["change_password"])) {
  $current_password = $_POST["current_password"];
  $new_password = $_POST["new_password"];
  $confirm_new_password = $_POST["confirm_new_password"];

  $stmt = $conn->prepare("SELECT password FROM customer WHERE customer_id = ?");
  $stmt->bind_param("i", $customer_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($password);
  $stmt->fetch();

  if (!password_verify($current_password, $password)) {
    header("Location: edit-profile.php?error=password_current");
    exit();
  }

  if (strlen($password) < 8) {
    header("Location: register.php?error=password_length");
    exit();
  }

  if ($new_password !== $confirm_new_password) {
    header("Location: edit-profile.php?error=password_match");
    exit();
  }

  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
  $stmt->bind_param("si", $hashed_password, $customer_id);

  if ($stmt->execute()) {
    header("Location: edit-profile.php?success=password");
    exit();
  } else {
    $stmt->close();
    $conn->close();

    header("Location: edit-profile.php?error=password_unknown");
    exit();
  }
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
        <div class="container">
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
              <div class="navbar-nav ms-auto small">
                <a
                  class="nav-link py-md-0"
                  href="shop.php">
                  Shop
                </a>
                <a
                  class="nav-link py-md-0"
                  href="cart.php">
                  Cart <?php if (isset($_SESSION["cart"])): ?>
                      (<?php echo array_sum($_SESSION["cart"]); ?>)
                  <?php endif; ?>
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
              <div class="navbar-nav ms-auto small">
                <a
                  class="nav-link py-md-0"
                  href="login.php">
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
            <form class="ms-auto">
              <div class="input-group input-group-sm">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search" />
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  id="search">
                  <i class="bi bi-search"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <section class="container my-5">
        <div class="p-5 text-center bg-body-tertiary rounded-3">
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
        </div>
      </section>

      <section class="container my-5">
        <div class="row gy-5 gx-lg-5">
          <div class="col-lg-4">
            <div class="p-5 bg-body-tertiary rounded-3">
              <h2 id="update_profile_picture" class="text-body-emphasis m-0">Profile Picture</h2>
              <?php if (isset($_GET["success"]) && $_GET["success"] == "picture"): ?>
                <div class="alert alert-success mt-3" role="alert">
                  Profile picture updated successfully!
                </div>
              <?php elseif (isset($_GET["error"]) && $_GET["error"] == "picture_upload"): ?>
                <div class="alert alert-danger mt-3" role="alert">
                  Error uploading profile picture.
                </div>
              <?php elseif (isset($_GET["error"]) && $_GET["error"] == "picture_type"): ?>
                <div class="alert alert-danger mt-3" role="alert">
                  Invalid file type. Only JPG, JPEG, and PNG files are allowed.
                </div>
              <?php elseif (isset($_GET["error"]) && $_GET["error"] == "picture_empty"): ?>
                <div class="alert alert-danger mt-3" role="alert">
                  Please select a file to upload.
                </div>
              <?php elseif (isset($_GET["error"]) && $_GET["error"] == "picture_big"): ?>
                <div class="alert alert-danger mt-3" role="alert">
                  File is too large. Maximum file size is 5MB.
                </div>
              <?php endif; ?>
              <form
                action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                method="post"
                enctype="multipart/form-data"
                class="needs-validation"
                novalidate>
                <div class="text-center mt-5">
                  <!-- <label class="form-label" for="customer_picture_form"> -->
                    <?php if ($customer_picture): ?>
                      <img
                        src="uploads/customer/<?php echo $customer_picture; ?>"
                        alt="profile"
                        class="rounded-circle"
                        style="width: 80%; aspect-ratio: 1/1" /> 
                    <?php else: ?>
                      <img
                        src="https://api.dicebear.com/6.x/initials/svg?seed=<?php echo str_replace(
                          " ",
                          "%20",
                          $first_name
                        ); ?>"
                        alt="profile"
                        class="rounded-circle"
                        style="width: 80%; aspect-ratio: 1/1" />
                    <?php endif; ?>
                  <!-- </label> -->
                </div>
                <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
                <input
                  type="file"
                  class="form-control mt-5"
                  id="customer_picture_form"
                  name="customer_picture_form"
                  placeholder="profile"
                  accept=".jpg,.jpeg,.png"
                  required/>
                <div class="invalid-feedback">Please select a file to upload.</div>
                <div class="text-center mt-3">
                  <p>Accepted file types: <code>jpg</code>, <code>jpeg</code>, <code>png</code></p>
                  <p>Maximum accepted file size: <code>5MB</code></p>
                  <button
                    type="submit"
                    class="btn btn-primary px-4 rounded-pill"
                    name="update_picture">
                    Update Profile Picture
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="p-5 bg-body-tertiary rounded-3">
              <h2 class="text-body-emphasis m-0">Profile Information</h2>
              <?php if (isset($_GET["success"]) && $_GET["success"] == "information"): ?>
                <div class="alert alert-success mt-3" role="alert">
                  Profile information updated successfully!
                </div>
              <?php endif; ?>
              <?php if (isset($_GET["error"]) && $_GET["error"] == "information_unknown"): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    Something went wrong. Please try again.
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
                      for="first_name"
                      class="form-label">
                      First name
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      id="first_name"
                      name="first_name"
                      value="<?php echo $first_name; ?>"
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
                      value="<?php echo $middle_name; ?>" />
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
                      value="<?php echo $last_name; ?>"
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
                      value="<?php echo $address; ?>"
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
                      value="<?php echo $email_address; ?>"
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
                      value="<?php echo $phone_number; ?>"
                      required />
                    <div class="invalid-feedback">Please provide a valid phone number.</div>
                  </div>
                  <div class="text-center">
                    <button
                      class="btn btn-primary px-4 rounded-pill"
                      type="submit"
                      name="update_information">
                      Update Profile Information
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="p-5 bg-body-tertiary rounded-3">
              <h2 class="text-body-emphasis m-0">Password</h2>
              <form
                action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                method="post"
                class="needs-validation mt-5"
                novalidate>
                <?php if (isset($_GET["success"]) && $_GET["success"] == "password"): ?>
                  <div class="alert alert-success mt-3" role="alert">
                    Password updated successfully!
                  </div>
                <?php endif; ?>
                <?php if (isset($_GET["error"]) && $_GET["error"] == "password_current"): ?>
                  <div class="alert alert-danger mt-3" role="alert">
                    Current password is incorrect.
                  </div>
                <?php elseif (isset($_GET["error"]) && $_GET["error"] == "password_match"): ?>
                  <div class="alert alert-danger mt-3" role="alert">
                    Passwords do not match.
                  </div>
                <?php elseif (isset($_GET["error"]) && $_GET["error"] == "password_length"): ?>
                  <div class="alert alert-danger mt-3" role="alert">
                    Password must be at least 8 characters long.
                  </div>
                <?php endif; ?>
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
                  <div class="text-center">
                    <button
                      class="btn btn-primary px-4 rounded-pill"
                      type="submit"
                      name="change_password">
                      Change Password
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="container py-3 mt-5 border-top">
      <p class="text-center text-body-secondary">© 2023 Ukay-Ukay Shopping</p>
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
        })();

        const theme = localStorage.getItem("theme");

        if (theme) {
        document.body.dataset.bsTheme = theme;
        }
      </script>
  </body>
</html>
