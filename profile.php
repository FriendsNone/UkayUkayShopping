<?php

session_start();

if (!isset($_SESSION["customer"])) {
  header("Location: login.php?status=login");
  exit();
}

require_once "includes/database.php";

$customer_id = $_SESSION["customer"];

$stmt = $conn->prepare(
  "SELECT first_name, middle_name, last_name, address, email_address, phone_number, customer_picture FROM customer WHERE customer_id = ?"
);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($first_name, $middle_name, $last_name, $address, $email_address, $phone_number, $customer_picture);
$stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0" />
    <title>Profile - Ukay-Ukay Shopping</title>
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
        <img
          src="<?= !empty($customer_picture)
            ? "uploads/customer/$customer_picture"
            : "https://api.dicebear.com/6.x/initials/svg?seed=$first_name" ?>"
          alt="profile"
          class="rounded-circle mb-5"
          width="200"
          height="200" />
        <h1 class="text-body-emphasis">
          Welcome back, <?= $first_name ?>
        </h1>
      </section>

      <div class="container mb-5 mt-sm-5 bg-body-tertiary rounded">
        <div class="row">
          <section class="col-lg-6 p-3 p-sm-5">
            <h2 class="mb-5 text-body-emphasis">Recent Purchases</h2>
            <h1>TODO: Add Recent Purchases list</h1>
            <div
              class="accordion mb-5"
              id="orderHistory">
              <div class="accordion-item">
                <h5 class="accordion-header">
                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseOne">
                    Order #94058229
                  </button>
                </h5>
                <div
                  id="collapseOne"
                  class="accordion-collapse collapse"
                  data-bs-parent="#orderHistory">
                  <div class="accordion-body">
                    <div class="row row-cols-1 row-cols-sm-2 mb-3">
                      <p class="m-0">Product</p>
                      <p class="m-0">Product</p>
                      <p class="m-0">Product</p>
                      <p class="m-0">Product</p>
                      <p class="m-0">Product</p>
                      <p class="m-0">Product</p>
                    </div>
                    <div class="row row-cols-1 row-cols-sm-2">
                      <small class="m-0 text-body-secondary"> Items: 6 </small>
                      <small class="m-0 text-body-secondary"> Date: 2021-10-10 </small>
                      <small class="m-0 text-body-secondary"> Total: P60 </small>
                      <small class="m-0 text-body-secondary"> Status: Delivered </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-center">
              <a
                href="purchase-history.php"
                class="btn btn-outline-primary px-3 rounded-pill">
                Show Previous Purchases
              </a>
            </div>
          </section>
          <section class="col-lg-6 p-3 p-sm-5">
            <h2 class="mb-5 text-body-emphasis">Profile Information</h2>
            <div class="d-flex flex-column gap-1 mb-5">
              <div>
                <h5 class="text-body-secondary small">Name</h5>
                <p class="text-body-emphasis h5">
                  <?= $first_name . " " . $middle_name . " " . $last_name ?>
                </p>
              </div>
              <div>
                <h5 class="text-body-secondary small">Address</h5>
                <p class="text-body-emphasis h5">
                  <?= $address ?>
                </p>
              </div>
              <div>
                <h5 class="text-body-secondary small">Email Address</h5>
                <p class="text-body-emphasis h5 text-break">
                  <?= $email_address ?>
                </p>
              </div>
              <div>
                <h5 class="text-body-secondary small">Phone Number</h5>
                <p class="text-body-emphasis h5">
                  <?= $phone_number ?>
                </p>
              </div>
            </div>
            <div class="text-center">
              <a
                href="edit-profile.php"
                class="btn btn-outline-primary px-3 rounded-pill">
                Edit Profile
              </a>
            </div>
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
      const theme = localStorage.getItem("theme");
      if (theme) document.body.dataset.bsTheme = theme;
    </script>
  </body>
</html>
