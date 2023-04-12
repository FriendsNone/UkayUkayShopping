<?php

session_start();

if (isset($_SESSION["customer"])) {
  header("Location: shop.php");
  exit();
}

require_once "includes/database.php";

if (isset($_POST["login"])) {
  $email_address = $_POST["email_address"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("SELECT customer_id FROM customer WHERE email_address = ?");
  $stmt->bind_param("s", $email_address);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows == 0) {
    $stmt->close();
    $conn->close();
    unset($_POST);

    header("Location: login.php?error=email");
    exit();
  }

  $stmt->bind_result($customer_id);
  $stmt->fetch();

  $stmt = $conn->prepare("SELECT password FROM customer WHERE customer_id = ?");
  $stmt->bind_param("i", $customer_id);
  $stmt->execute();
  $stmt->bind_result($password_hash);
  $stmt->fetch();

  if (password_verify($password, $password_hash)) {
    $stmt->close();
    $conn->close();
    unset($_POST);

    $_SESSION["customer"] = $customer_id;

    header("Location: shop.php");
    exit();
  } else {
    $stmt->close();
    $conn->close();
    unset($_POST);

    header("Location: login.php?error=password");
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
    <title>Login - Ukay-Ukay Shopping</title>
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
            class="bi bi-person-circle mb-5"
            viewBox="0 0 16 16">
            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
            <path
              fill-rule="evenodd"
              d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
          </svg>
          <h1 class="text-body-emphasis">Login to Ukay-Ukay Shopping</h1>
        </div>
      </section>

      <section class="container my-5">
        <div class="p-5 bg-body-tertiary rounded-3">
          <form
            action="<?php echo $_SERVER["PHP_SELF"]; ?>"
            method="post"
            class="needs-validation"
            novalidate>
            <?php if (isset($_GET["error"])): ?>
              <div class="row justify-content-center mb-3">
                <div class="col-lg-6">
                  <div class="alert alert-danger" role="alert">
                    <?php if ($_GET["error"] == "unknown") {
                      echo "Something went wrong. Please try again.";
                    } elseif ($_GET["error"] == "email") {
                      echo "Account does not exist.";
                    } elseif ($_GET["error"] == "password") {
                      echo "Password does not match.";
                    } elseif ($_GET["error"] == "login") {
                      echo "Please login to continue.";
                    } ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <div class="row justify-content-center mb-3">
              <div class="col-lg-6">
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
            </div>
            <div class="row justify-content-center mb-3">
              <div class="col-lg-6">
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
                <div class="invalid-feedback">Please provide a valid password.</div>
              </div>
            </div>
            <div class="text-center">
              <button
                class="btn btn-primary px-4 rounded-pill"
                type="submit"
                name="login">
                Login
              </button>
            </div>
          </form>
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
      })();

      const theme = localStorage.getItem("theme");

      if (theme) {
      document.body.dataset.bsTheme = theme;
      }
    </script>
  </body>
</html>