<?php
include("../config.php");
session_start();

// ✅ Redirect if not logged in
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

$email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
$query = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
$userData = mysqli_fetch_assoc($query);
$user_id = $userData['id'] ?? null;

if (!$user_id) {
   die("User not found.");
}

// ✅ Fetch orders + join with products table to get product title and price
$orders = mysqli_query($conn, "
   SELECT o.*, p.title AS product_title, p.price AS product_price 
   FROM orders o 
   LEFT JOIN products p ON o.product_id = p.id 
   WHERE o.user_id='$user_id' 
   ORDER BY o.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <title>My Orders - YOU DO NOTES</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
      body {
         font-family: 'Segoe UI', Arial, sans-serif;
         background: #f7f3ef;
         margin: 0;
         padding: 0;
      }

      .container {
         max-width: 800px;
         margin: 50px auto;
         background: #fffaf5;
         padding: 25px;
         border-radius: 15px;
         box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      }

      h2 {
         color: #5a4b41;
         text-align: center;
         margin-bottom: 20px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
      }

      th,
      td {
         padding: 12px;
         text-align: left;
         border-bottom: 1px solid #ddd;
      }

      th {
         background: #b08968;
         color: white;
      }

      tr:hover {
         background: #f5eee7;
      }

      .empty {
         text-align: center;
         color: #777;
         margin-top: 20px;
         font-style: italic;
      }

      .back-btn {
         display: block;
         text-align: center;
         margin-top: 25px;
         background: #5a4b41;
         color: white;
         padding: 10px;
         border-radius: 8px;
         text-decoration: none;
         transition: background 0.2s;
      }

      .back-btn:hover {
         background: #b08968;
      }
   </style>
</head>

<body>
   <div class="container">
      <h2><i class="fa-solid fa-box"></i> My Orders</h2>

      <?php if (mysqli_num_rows($orders) > 0): ?>
         <table>
            <tr>
               <th>Order ID</th>
               <th>Product Title</th>
               <th>Total</th>
               <th>Status</th>
               <th>Date Ordered</th>
            </tr>
            <?php while ($order = mysqli_fetch_assoc($orders)): ?>
               <tr>
                  <td>#<?= htmlspecialchars($order['id']); ?></td>
                  <td><?= htmlspecialchars($order['product_title'] ?? 'Unknown Product'); ?></td>
                  <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                  <td><?= htmlspecialchars($order['status']); ?></td>
                  <td><?= htmlspecialchars($order['created_at']); ?></td>
               </tr>
            <?php endwhile; ?>
         </table>
      <?php else: ?>
         <p class="empty">You have no orders yet.</p>
      <?php endif; ?>

      <a href="profile.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
   </div>
</body>

</html>
