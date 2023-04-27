<?php

session_start();

if (!isset($_SESSION["customer"])) {
  header("Location: login.php?status=login");
  exit();
}

require_once "includes/database.php";

if (isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0) {
  $cart = $_SESSION["cart"];
  $cart_items = array_keys($cart);

  $in = str_repeat("?,", count($cart) - 1) . "?";
  $types = str_repeat("i", count($cart));

  $stmt = $conn->prepare("SELECT product_id, name, price FROM product WHERE product_id IN ($in)");
  $stmt->bind_param($types, ...$cart_items);
  $stmt->execute();

  $result = $stmt->get_result();
  $products = [];

  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }

  $cart_total = 0;

  foreach ($products as $product) {
    $cart_total += $product["price"] * $cart[$product["product_id"]];
  }
}

if (isset($_POST["cart_item_add"])) {
  $cart_item_id = $_POST["cart_item_id"];

  if (isset($_SESSION["cart"][$cart_item_id])) {
    $_SESSION["cart"][$cart_item_id]++;
  }

  header("Location: cart.php");
  exit();
}

if (isset($_POST["cart_item_remove"])) {
  $cart_item_id = $_POST["cart_item_id"];

  if (isset($_SESSION["cart"][$cart_item_id])) {
    $_SESSION["cart"][$cart_item_id]--;
  }

  if ($_SESSION["cart"][$cart_item_id] == 0) {
    unset($_SESSION["cart"][$cart_item_id]);
  }

  header("Location: cart.php");
  exit();
}

if (isset($_POST["cart_item_delete"])) {
  $cart_item_id = $_POST["cart_item_id"];

  if (isset($_SESSION["cart"][$cart_item_id])) {
    unset($_SESSION["cart"][$cart_item_id]);
  }

  header("Location: cart.php");
  exit();
}

if (isset($_POST["cart_item_empty"])) {
  unset($_SESSION["cart"]);

  header("Location: cart.php");
  exit();
}

echo "<h1>TODO: Remove quantity before checkout</h1>";
echo "<h1>TODO: Add feedback</h1>";
if (isset($_POST["cart_checkout"])) {
  $stmt = $conn->prepare("INSERT INTO sales_order (customer_id, items, total) VALUES (?, ?, ?)");
  $stmt->bind_param("iis", $_SESSION["customer"], array_sum($cart), $cart_total);
  $stmt->execute();

  $sales_id = $conn->insert_id;

  foreach ($products as $product) {
    $item_total = $product["price"] * $cart[$product["product_id"]];

    $stmt = $conn->prepare("INSERT INTO sales_order_item (sales_id, product_id, quantity, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $sales_id, $product["product_id"], $cart[$product["product_id"]], $item_total);
    $stmt->execute();
  }

  unset($_SESSION["cart"]);
  header("Location: cart.php?success=checkout");
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
    <title>Shopping Cart - Ukay-Ukay Shopping</title>
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
          class="bi bi-cart mb-5"
          viewBox="0 0 16 16">
          <path
            d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </svg>
        <h1 class="text-body-emphasis">Shopping Cart</h1>
      </section>

      <?php if (!isset($products)): ?>
      <section class="container mb-5 mt-sm-5 p-5 text-center bg-body-tertiary rounded">
        <h2 class="text-body-emphasis">There's nothing but emptiness in your cart.</h2>
        <p class="col-lg-8 mx-auto fs-5 text-muted">You can start shopping by clicking the button below.</p>
        <a
          class="btn btn-primary btn-lg mt-5 px-4 rounded-pill"
          href="shop.php"
          role="button">
          Shop now!
        </a>
      </section>
      <?php else: ?>
      <div class="container mb-5 mt-sm-5 bg-body-tertiary rounded">
        <div class="row">
          <section class="col-lg-8 p-3 p-sm-5">
            <h2 class="mb-5 text-body-emphasis">Item List</h2>
            <div class="table-responsive">
              <table class="table table-hover text-center">
                <thead>
                  <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Price</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product): ?>
                  <tr>
                    <th scope="row"><?= $product["name"] ?></th>
                    <td>&#8369;<?= $product["price"] ?></td>
                    <td><?= $cart[$product["product_id"]] ?></td>
                    <td>&#8369;<?= $product["price"] * $cart[$product["product_id"]] ?></td>
                    <td>
                      <form
                        action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                        method="post"
                        class="m-0 y-0 d-flex flex-row gap-1 justify-content-center">
                        <input
                          type="hidden"
                          name="cart_item_id"
                          value="<?= $product["product_id"] ?>" />
                        <button
                          type="submit"
                          class="btn btn-outline-secondary btn-sm"
                          name="cart_item_add">
                          <i class="bi bi-plus"></i>
                        </button>
                        <button
                          type="submit"
                          class="btn btn-outline-secondary btn-sm"
                          name="cart_item_remove">
                          <i class="bi bi-dash"></i>
                        </button>
                        <button
                          type="submit"
                          class="btn btn-danger btn-sm"
                          name="cart_item_delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="col-lg-4 p-3 p-sm-5">
            <h2 class="mb-5 text-body-emphasis">Order Details</h2>
            <div class="d-flex flex-column gap-1">
              <p class="m-0"><strong>Number of items:</strong> <?= array_sum($cart) ?></p>
              <p class="m-0"><strong>Total Cost:</strong> &#8369;<?= $cart_total ?></p>
            </div>
            <form
              action="<?php echo $_SERVER["PHP_SELF"]; ?>"
              method="post"
              class="mt-5">
              <button
                type="button"
                class="btn btn-outline-success px-3 rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#checkoutModal">
                Checkout
              </button>
              <button
                type="submit"
                class="btn btn-outline-danger px-3 rounded-pill"
                name="cart_item_empty">
                Empty Cart
              </button>
              <a
                class="btn btn-outline-secondary mt-2 px-3 rounded-pill"
                href="shop.php">
                Continue Shopping
              </a>
            </form>
          </section>
        </div>
      </div>
      <?php endif; ?>
    </main>

    <footer class="container py-3 mt-5 border-top text-center text-body-secondary">
      <p>© 2023 Ukay-Ukay Shopping</p>
    </footer>

    <div
      class="modal fade"
      id="checkoutModal"
      tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1
              class="modal-title fs-5"
              id="exampleModalLabel">
              Proceed checkout
            </h1>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to checkout?</p>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal">
              Cancel
            </button>
            <form
              action="<?php echo $_SERVER["PHP_SELF"]; ?>"
              method="post">
              <button
                type="submit"
                class="btn btn-success"
                name="cart_checkout">
                Checkout
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

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