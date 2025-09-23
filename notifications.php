<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

$user_email = $_SESSION['user'];

// Fetch notifications for this user
$query = "SELECT * FROM notifications WHERE user_email='$user_email' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Mark all as read when opened
mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE user_email='$user_email'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <title>Notifications - YOU DO NOTES</title>
   <link rel="stylesheet" href="assets/style.css">
   <style>
      .notification-card {
         background: #fffaf5;
         padding: 20px;
         margin: 10px 0;
         border-radius: 12px;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .notification-card a {
         text-decoration: none;
         color: #b08968;
      }

      .notification-card a:hover {
         text-decoration: underline;
      }
   </style>
</head>

<body>
   <div class="container">
      <h2>Your Notifications</h2>

      <?php if (mysqli_num_rows($result) > 0): ?>
         <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="notification-card">
               <p><?php echo htmlspecialchars($row['message']); ?></p>
               <?php if (!empty($row['link'])): ?>
                  <a href="<?php echo $row['link']; ?>">View</a>
               <?php endif; ?>
               <small style="float:right;"><?php echo $row['created_at']; ?></small>
            </div>
         <?php endwhile; ?>
      <?php else: ?>
         <p>No notifications yet.</p>
      <?php endif; ?>

      <a href="products.php" class="btn">Back to Products</a>
   </div>
</body>

</html>