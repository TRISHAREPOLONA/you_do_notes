<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// --- Notifications Setup (temporary placeholder) ---
$notification_count = 0; // default (no notifications yet)

// Search products
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM products WHERE title LIKE '%$search%' OR description LIKE '%$search%'";
} else {
    $query = "SELECT * FROM products";
}
$result = mysqli_query($conn, $query);
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
    /* Navbar */
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
    .navbar-left h2 {
      margin: 0;
      color: #5a4b41;
      font-size: 2rem;
      letter-spacing: 1px;
    }
    .navbar-center input {
      width: 300px;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #c2b9b0;
      font-size: 1rem;
    }
    .navbar-right {
      display: flex;
      align-items: center;
    }
    .navbar-right a {
      margin-left: 35px;
      color: #5a4b41;
      text-decoration: none;
      font-weight: bold;
      font-size: 1rem;
      transition: color 0.2s;
      position: relative;
    }
    .navbar-right a:hover {
      color: #b08968;
    }
    .navbar-right i {
      font-size: 1.4rem;
      cursor: pointer;
      transition: color 0.2s;
    }
    .navbar-right i:hover {
      color: #b08968;
    }

    /* Notification badge */
    .notification {
      position: relative;
    }
    .notification .badge {
      position: absolute;
      top: -8px;
      right: -10px;
      padding: 4px 7px;
      border-radius: 50%;
      background: red;
      color: white;
      font-size: 0.75rem;
      font-weight: bold;
    }

    /* Banner */
    .banner {
      background: url('assets/minimal.png') no-repeat center center/cover;
      height: 450px;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      color: #fff;
      text-align: center;
      margin-bottom: 30px;
    }
    .banner h1 {
      background: rgba(0,0,0,0.5);
      padding: 15px 25px;
      border-radius: 10px;
      font-size: 1.8rem;
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
      display: flex;
      justify-content: center;
      gap: 40px;
      margin: 60px 0;
      flex-wrap: wrap;
      text-align: center;
    }
    .section-box {
      background: #fffaf5;
      padding: 30px;
      border-radius: 15px;
      width: 280px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .section-box:hover {
      transform: scale(1.05);
    }
    .section-box h2 {
      color: #5a4b41;
      margin-bottom: 10px;
    }
    .section-box p {
      color: #6d5d52;
      margin: 15px 0;
    }
    .section-box a {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 16px;
      background: #b08968;
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
    }
    .section-box a:hover {
      background: #a0765b;
    }

    @media (max-width: 900px) {
      .navbar {
        flex-direction: column;
        align-items: stretch;
        padding: 10px;
      }
      .navbar-center input {
        width: 90vw;
        max-width: 300px;
      }
      .sections {
        flex-direction: column;
        gap: 20px;
      }
      .section-box {
        width: 90%;
        margin: 0 auto;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <h2>YOU DO NOTES</h2>
    </div>
    <div class="navbar-center">
      <form method="GET" action="products.php">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
      </form>
    </div>
    <div class="navbar-right">
      <!-- Notification bell FIRST -->
      <a href="notifications.php" class="notification" title="Notifications">
        <i class="fa-solid fa-bell"></i>
        <?php if ($notification_count > 0): ?>
          <span class="badge"><?php echo $notification_count; ?></span>
        <?php endif; ?>
      </a>

      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
      <a href="cart.php" title="Cart"><i class="fa-solid fa-cart-shopping"></i></a>
      <a href="profile.php" title="Profile"><i class="fa-solid fa-user"></i></a>
    </div>
  </div>

  <!-- Banner -->
  <div class="banner">
    <h1>We believe in Minimalism. Less is More.</h1>
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

    <!-- Sections -->
    <div class="sections">
      <div class="section-box">
        <h2>Our Services</h2>
        <p>High-quality study notes designed for learners.</p>
        <a href="services.php">Learn More</a>
      </div>
      <div class="section-box">
        <h2>Codes</h2>
        <p>Get exclusive discounts and promo codes.</p>
        <a href="codes.php">View Codes</a>
      </div>
      <div class="section-box">
        <h2>Portfolio</h2>
        <p>Browse our collection of past projects.</p>
        <a href="portfolio.php">View Portfolio</a>
      </div>
    </div>
  </div>
</body>
</html>
