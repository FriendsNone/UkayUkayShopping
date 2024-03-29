<?php

session_start();

if (!isset($_SESSION["customer"])) {
  header("Location: login.php?status=login");
  exit();
}

require_once "includes/database.php";

$stmt = $conn->prepare("SELECT * FROM product_category");
$stmt->execute();
$result = $stmt->get_result();

$categories = [];

while ($row = $result->fetch_assoc()) {
  $categories[] = $row;
}

$stmt = $conn->prepare("SELECT * FROM product");
$stmt->execute();
$result = $stmt->get_result();

$products = [];

while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}

if (isset($_GET["search"]) && !empty($_GET["search"])) {
  $search = $_GET["search"];

  $products = array_filter($products, function ($product) use ($search) {
    return strpos($product["name"], $search) !== false;
  });
}

if (isset($_GET["category"]) && $_GET["category"] != 0) {
  $category = $_GET["category"];

  $products = array_filter($products, function ($product) use ($category) {
    return $product["category_id"] == $category;
  });
}

if (isset($_GET["sort"]) && $_GET["sort"] != 0) {
  $sort = $_GET["sort"];

  switch ($sort) {
    case 1:
      usort($products, function ($a, $b) {
        return strnatcmp($a["name"], $b["name"]);
      });
      break;
    case 2:
      usort($products, function ($a, $b) {
        return strnatcmp($b["name"], $a["name"]);
      });
      break;
    case 3:
      usort($products, function ($a, $b) {
        return $a["price"] <=> $b["price"];
      });
      break;
    case 4:
      usort($products, function ($a, $b) {
        return $b["price"] <=> $a["price"];
      });
      break;
    case 5:
      usort($products, function ($a, $b) {
        return strtotime($a["date_added"]) <=> strtotime($b["date_added"]);
      });
      break;
    case 6:
      usort($products, function ($a, $b) {
        return strtotime($b["date_added"]) <=> strtotime($a["date_added"]);
      });
      break;
  }
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
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0" />
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
          class="bi bi-basket mb-5"
          viewBox="0 0 16 16">
          <path
            d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z" />
        </svg>
        <h1 class="text-body-emphasis">All Products</h1>
      </section>

      <section class="container mb-5 mt-sm-5 p-3 p-sm-5 bg-body-tertiary rounded">
        <form
          class="row g-3 mb-5"
          action="<?php echo $_SERVER["PHP_SELF"]; ?>"
          method="get">
          <div class="col-md-4">
            <label
              for="search"
              class="form-label">
              Search
            </label>
            <input
              type="text"
              class="form-control"
              id="search"
              name="search"
              placeholder="Search"/>
          </div>
          <div class="col-md-4">
            <label
              for="sort"
              class="form-label">
              Sort by
            </label>
            <select
              id="sort"
              name="sort"
              class="form-select">
              <option value="0">Choose...</option>
              <option value="1">Name (A-Z)</option>
              <option value="2">Name (Z-A)</option>
              <option value="3">Price (low to high)</option>
              <option value="4">Price (high to low)</option>
              <option value="5">Date added (oldest to newest)</option>
              <option value="6">Date added (newest to oldest)</option>
            </select>
          </div>
          <div class="col-md-4">
            <label
              for="category"
              class="form-label">
              Category
            </label>
            <select
              id="category"
              name="filter"
              class="form-select">
              <option value="0">Choose...</option>
              <?php foreach ($categories as $category): ?>
              <option value="<?= $category["id"] ?>"><?= $category["name"] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>

        <div class="row row-cols-1 row-cols-lg-2 g-3">
          <?php foreach ($products as $product): ?>
          <div class="col">
            <div class="card rounded h-100">
              <div class="row g-0 h-100">
                <div class="col-12 col-sm-4">
                  <img
                    src="uploads/product/<?= $product["product_picture"] ?>"
                    class="img-fluid rounded h-100"
                    alt="<?= $product["name"] ?>" />
                </div>
                <div class="col-12 col-sm-8">
                  <div class="card-body h-100 d-flex flex-column justify-content-end">
                    <div class="d-flex flex-column justify-content-start flex-grow-1 gap-1 mb-3">
                      <h5 class="card-title m-0">
                        <?= $product["name"] ?>
                      </h5>
                      <h6 class="card-subtitle text-secondary">
                        <span class="card-subtitle <?= $product["quantity"] > 0 ? "text-success" : "text-danger" ?>">
                          <?= $product["quantity"] > 0 ? "In stock ({$product["quantity"]})" : "Out of stock" ?>
                        </span>
                        <i class="bi bi-dot"></i>
                        &#8369;<?= $product["price"] ?>
                      </h6>
                    </div>
                    <p class="card-text">
                      <?= $product["description"] ?>
                    </p>
                    <?php if ($product["quantity"] > 0): ?>
                    <form
                      action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                      method="post"
                      class="d-flex flex-column flex-sm-row gap-3">
                      <input type="hidden" name="cart_product_id" value="<?= $product["product_id"] ?>">
                      <div class="input-group w-100">
                        <button
                          class="btn btn-outline-secondary"
                          type="button"
                          id="quantity-minus"
                          onclick="this.parentNode.querySelector('input#cart_quantity').stepDown()">
                          <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="cart_quantity" name="cart_quantity"
                        min="1" max="<?= $product["quantity"] ?>" value="1" />
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
        <h1>TODO: Add pagination</h1>
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
      const theme = localStorage.getItem("theme");
      if (theme) document.body.dataset.bsTheme = theme;

      const sort = document.getElementById('sort');
        const category = document.getElementById('category');
        const search = document.getElementById('search');

        sort.addEventListener('change', function () {
          this.form.submit();
        });

        category.addEventListener('change', function () {
          this.form.submit();
        });

        search.addEventListener('keyup', function (event) {
          if (event.key === 'Enter') {
              this.form.submit();
          }
        });

        sort.value = <?= isset($_GET["sort"]) ? $_GET["sort"] : 0 ?>;
        category.value = <?= isset($_GET["filter"]) ? $_GET["filter"] : 0 ?>;
        search.value = '<?= isset($_GET["search"]) ? $_GET["search"] : "" ?>';
    </script>
  </body>
</html>
