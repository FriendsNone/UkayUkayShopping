<?php

session_start(); ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0" />
    <title>Ukay-Ukay Shopping</title>
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
            <div class="navbar-nav ms-auto small">
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
      <section class="container mb-5 mt-sm-5 px-3 py-5 px-sm-5 text-center bg-body-tertiary rounded">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="100"
          height="100"
          fill="currentColor"
          class="bi bi-bag-heart mb-5"
          viewBox="0 0 16 16">
          <path
            fill-rule="evenodd"
            d="M10.5 3.5a2.5 2.5 0 0 0-5 0V4h5v-.5Zm1 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0ZM14 14V5H2v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1ZM8 7.993c1.664-1.711 5.825 1.283 0 5.132-5.825-3.85-1.664-6.843 0-5.132Z" />
        </svg>
        <h1 class="text-body-emphasis">50% off for the first 100 buyers</h1>
        <p class="col-lg-8 mx-auto fs-5 text-muted">
          Lorem ipsum, dolor sit amet consectetur adipisicing elit. Nulla, sint esse molestias doloribus totam pariatur voluptatem repellendus amet nemo molestiae, ipsa nobis nesciunt minima nisi.
        </p>
        <a
          class="btn btn-primary btn-lg mt-5 px-4 rounded-pill"
          href="shop.php"
          role="button">
          Shop now!
        </a>
      </section>

      <section>
        <h1>TODO: Add featured items carousel</h1>
      </section>
    </main>

    <footer class="container py-3 mt-5 border-top text-center text-body-secondary">
      <p>© 2023 Ukay-Ukay Shopping</p>
      <input
        type="checkbox"
        class="btn-check"
        id="theme" />
      <label
        class="btn btn-sm btn-outline-secondary"
        for="theme">
        <i class="bi bi-magic"></i>
      </label>
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
      crossorigin="anonymous"></script>

    <script>
      const themeSwitcher = document.querySelector("#theme");
      const theme = localStorage.getItem("theme");

      if (theme) document.body.dataset.bsTheme = theme;
      if (document.body.dataset.bsTheme === "dark") themeSwitcher.checked = true;

      themeSwitcher.addEventListener("change", (e) => {
        if (e.target.checked) {
          document.body.dataset.bsTheme = "dark";
          localStorage.setItem("theme", "dark");
        } else {
          document.body.dataset.bsTheme = "light";
          localStorage.setItem("theme", "light");
        }
      });
    </script>
  </body>
</html>