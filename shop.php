<?php

session_start();
require_once "includes/database.php";

if (!isset($_SESSION["customer"])) {
  header("Location: login.php?error=login");
  exit();
}

$stmt = $conn->prepare("SELECT * FROM product");
$stmt->execute();
$result = $stmt->get_result();

$products = [];

while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}

if (isset($_POST["add_to_cart"])) {
  $cart_product_id = $_POST["cart_product_id"];
  $cart_quantity = $_POST["cart_quantity"];

  if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
  }

  if (isset($_SESSION["cart"][$cart_product_id])) {
    $_SESSION["cart"][$cart_product_id] += $cart_quantity;
  } else {
    $_SESSION["cart"][$cart_product_id] = $cart_quantity;
  }

  header("Location: shop.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products - Ukay-Ukay Shopping</title>
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
            class="bi bi-basket mb-5"
            viewBox="0 0 16 16">
            <path
              d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z" />
          </svg>
          <h1 class="text-body-emphasis">All Products</h1>
        </div>
      </section>

      <section class="container my-5">
        <div class="p-3 p-md-5 bg-body-tertiary rounded-3">
          <div class="row row-cols-1 row-cols-lg-2 g-3">
            <?php foreach ($products as $product): ?>
              <div class="col">
                <div class="card h-100">
                  <div class="row g-0 h-100">
                    <div class="col-4">
                      <img
                        src="uploads/product/<?php echo $product["product_picture"]; ?>"
                        class="img-fluid rounded-start h-100"
                        alt="<?php echo $product["name"]; ?>" />
                    </div>
                    <div class="col-8">
                      <div class="card-body h-100 d-flex flex-column justify-content-end">
                        <div class="d-flex flex-column justify-content-start flex-grow-1 gap-1 mb-3">
                          <h5 class="card-title m-0"><?php echo $product["name"]; ?></h5>
                          <h6 class="card-subtitle text-secondary">
                          <?php if ($product["quantity"] > 0): ?>
                            <span class="card-subtitle text-success">In stock (<?php echo $product[
                              "quantity"
                            ]; ?>)</span>
                          <?php else: ?>
                            <span class="card-subtitle text-danger">Out of stock</span>
                          <?php endif; ?>
                          <i class="bi bi-dot"></i>
                          &#8369;<?php echo $product["price"]; ?>
                          </h6>
                        </div>
                        <p class="card-text"><?php echo $product["description"]; ?></p>
                        <?php if ($product["quantity"] > 0): ?>
                          <form 
                          action="<?php echo $_SERVER["PHP_SELF"]; ?>" 
                          method="post" 
                          class="d-flex flex-column flex-sm-row gap-3">
                          <input
                            type="hidden"
                            name="cart_product_id"
                            value="<?php echo $product["product_id"]; ?>">
                            <div class="input-group w-100">
                              <button
                                class="btn btn-outline-secondary"
                                type="button"
                                id="quantity-minus"
                                onclick="this.parentNode.querySelector('input#cart_quantity').stepDown()">
                                <i class="bi bi-dash"></i>
                              </button>
                              <input
                                type="number"
                                class="form-control text-center"
                                id="cart_quantity"
                                name="cart_quantity"
                                min="1"
                                max="<?php echo $product["quantity"]; ?>"
                                value="1" />
                              <button
                                class="btn btn-outline-secondary"
                                type="button"
                                id="quantity-plus"
                                onclick="this.parentNode.querySelector('input#cart_quantity').stepUp()">
                                <i class="bi bi-plus"></i>
                              </button>
                            </div>
                            <button
                              type="submit"
                              class="btn btn-primary w-100"
                              name="add_to_cart">
                              Add to cart
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
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
      const theme = localStorage.getItem("theme");

      if (theme) {
        document.body.dataset.bsTheme = theme;
      }
    </script>
  </body>
</html>
