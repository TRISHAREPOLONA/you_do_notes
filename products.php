<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get products from DB
$result = mysqli_query($conn, "SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products - YOU DO NOTES</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f7f3ef;
    }
    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px 0;
    }
    /* Top Navbar */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      background-color: #fffaf5;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .navbar-left {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: flex-start;
    }
    .navbar-left h2 {
      margin: 0;
      color: #5a4b41;
      font-size: 2rem;
      letter-spacing: 1px;
    }
    .navbar-center {
      flex: 2;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .navbar-center form {
      width: 100%;
      display: flex;
      justify-content: center;
    }
    .navbar-center input {
      width: 300px;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #c2b9b0;
      font-size: 1rem;
    }
    .navbar-right {
      flex: 1;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }
    .navbar-right a {
      margin-left: 20px;
      color: #5a4b41;
      text-decoration: none;
      font-weight: bold;
      font-size: 1rem;
      transition: color 0.2s;
    }
    .navbar-right a:hover {
      color: #b08968;
    }
    .navbar-right i {
      font-size: 1.4rem;
      cursor: pointer;
      transition: color 0.2s;
      margin-left: 20px; /* spacing from other links */
    }
    .navbar-right i:hover {
      color: #b08968;
    }

    /* Product catalog grid */
    .catalog {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    .product-card {
      background: #fffaf5;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 220px;
    }
    .product-card h3 {
      margin: 10px 0;
      color: #5a4b41;
    }
    .product-card p {
      color: #6d5d52;
    }
    .btn {
      background: #b08968;
      color: #fff;
      padding: 8px 18px;
      border-radius: 8px;
      text-decoration: none;
      margin-top: 10px;
      transition: background 0.2s;
    }
    .btn:hover {
      background: #a0765b;
    }
    /* Sections below */
    .sections {
      margin-top: 50px;
      text-align: center;
      width: 100%;
    }
    .sections h2 {
      color: #5a4b41;
      margin-bottom: 20px;
    }
    .sections .section-box {
      background: #fffaf5;
      margin: 20px auto;
      padding: 30px;
      width: 80%;
      border-radius: 15px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
    }
    @media (max-width: 900px) {
      .navbar {
        flex-direction: column;
        align-items: stretch;
        padding: 10px 10px;
      }
      .navbar-left, .navbar-center, .navbar-right {
        justify-content: center;
        margin: 5px 0;
      }
      .navbar-center input {
        width: 90vw;
        max-width: 300px;
      }
      .navbar-right i {
        margin-left: 10px;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation bar -->
  <div class="navbar">
    <div class="navbar-left">
      <h2>YOU DO NOTES</h2>
    </div>
    <div class="navbar-center">
      <form method="GET" action="products.php">
        <input type="text" name="search" placeholder="Search products...">
      </form>
    </div>
    <div class="navbar-right">
      <a href="#about">About</a>
      <a href="#services">Services</a>
      <a href="#contact">Contact</a>
      <a href="cart.php" title="Cart"><i class="fa-solid fa-cart-shopping"></i></a>
      <a href="profile.php" title="Profile"><i class="fa-solid fa-user"></i></a>
    </div>
  </div>

  <div class="container">
    <!-- Product catalog -->
    <div class="catalog">
      <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <div class="product-card">
          <h3><?php echo $row['title']; ?></h3>
          <p><?php echo $row['description']; ?></p>
          <p><strong>₱<?php echo $row['price']; ?></strong></p>
          <a href="cart.php?id=<?php echo $row['id']; ?>" class="btn">Add to Cart</a>
        </div>
      <?php } ?>
    </div>

    <!-- Extra Sections -->
    <div class="sections" id="services">
      <div class="section-box">
        <h2>Our Services</h2>
        <p>We provide high-quality digital notes and study materials designed for students and professionals.</p>
      </div>
      <div class="section-box" id="codes">
        <h2>Codes</h2>
        <p>Access exclusive discount codes and promo offers for consistent users.</p>
      </div>
      <div class="section-box" id="portfolio">
        <h2>Portfolio</h2>
        <p>Check out our collection of past projects and collaborations with students and educators.</p>
      </div>
    </div>
  </div>
</body>
</html>
