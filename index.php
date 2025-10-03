<?php
include("config.php");
session_start();

// default (no notifications yet)
$user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '';
$notif_query = "SELECT COUNT(*) AS count 
                FROM notifications 
                WHERE user_email='$user_email' 
                AND is_read=0";

$notif_result = mysqli_query($conn, $notif_query);
$notification_count = mysqli_fetch_assoc($notif_result)['count'];

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta charset="UTF-8">
  <title>Products - YOU DO NOTES</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/style.css">
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
      <a href="notifications.php" class="notification" title="Notifications">
        <i class="fa-solid fa-bell"></i>
        <?php if ($notification_count > 0): ?>
          <span class="badge"><?php echo $notification_count; ?></span>
        <?php endif; ?>
      </a>

      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
      <a href="cart.php" class="cart-link" title="Cart">
        <i class="fa-solid fa-cart-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
          <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
      </a>
      <a href="profile.php" title="Profile"><i class="fa-solid fa-user"></i></a>
    </div>
  </div>

  <!-- Role Selection -->
  <section id="user-choice">
    <div class="container">
      <h2>Choose your role</h2>
      <div class="choice-buttons">
        <a href="#notes" class="btn">Buyer (Browse Notes)</a>
        <a href="seller.php" class="btn">Seller (Upload & Manage Notes)</a>
      </div>
    </div>
  </section>

  <!-- Banner -->
  <div class="banner">
    <h1>We believe in Minimalism. Less is More.</h1>
  </div>

  <!-- Available Notes Section -->
  <section id="notes">
    <div class="container">
      <h2>Available Notes</h2>
      <div class="notes-grid">
        <?php
        // Display all products from DB
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
  // Check if this product matches the search
  $isMatch = !empty($search) && (
      stripos($row['title'], $search) !== false ||
      stripos($row['description'], $search) !== false
  );
  ?>
  <div class="note-card <?php echo $isMatch ? 'highlight' : ''; ?>">
    <div class="note-preview">
      <?php if (!empty($row['file_path'])): ?>
        <iframe src="<?php echo $row['file_path']; ?>#toolbar=0" 
                style="width:100%; height:200px; filter: blur(5px); pointer-events:none;">
        </iframe>
      <?php else: ?>
        <img src="images/default.jpg" alt="No preview available" style="width:100%; filter: blur(5px);">
      <?php endif; ?>
      <div class="locked-message">Preview Blurred – Buy to Unlock</div>
    </div>

    <h3><?php echo $row['title']; ?></h3>
    <p><?php echo $row['description']; ?></p>
    <p><em><?php echo $row['course']; ?></em></p>
    <p><strong>₱<?php echo $row['price']; ?></strong></p>
    <a href="product_details.php?id=<?php echo $row['id']; ?>" class="btn">View Details</a>
  </div>
<?php } ?>

      </div>
    </div>
  </section>

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
<script>
document.addEventListener("DOMContentLoaded", function() {
  const firstHighlight = document.querySelector(".note-card.highlight");
  if (firstHighlight) {
    firstHighlight.scrollIntoView({ behavior: "smooth", block: "center" });
  }
});
</script>

</body>

</html>