<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

// ✅ If it's an array, extract the email
if (is_array($_SESSION['user'])) {
   $email = $_SESSION['user']['email'];
} else {
   $email = $_SESSION['user'];
}

// ✅ Fetch current user details
$user_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// ✅ Update payment details if submitted
if (isset($_POST['update_payment'])) {
   $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
   $account = mysqli_real_escape_string($conn, $_POST['account_number']);

   if ($method === 'GCash') {
      $update = "UPDATE users 
               SET payment_method='GCash', gcash_number='$account', paymaya_number=NULL 
               WHERE email='$email'";
   } else {
      $update = "UPDATE users 
               SET payment_method='PayMaya', paymaya_number='$account', gcash_number=NULL 
               WHERE email='$email'";
   }

   if (mysqli_query($conn, $update)) {
      header("Location: seller.php?success=1");
      exit;
   } else {
      echo "Error updating payment info: " . mysqli_error($conn);
   }
}

// ✅ Seller Stats (Balance / Earnings / Sales)
$total_sales = 0;
$total_earnings = 0;
$balance = 0;

// Make sure you have an `orders` table where purchases are logged
$stats_query = "SELECT 
                  COUNT(id) as sales, 
                  SUM(seller_earnings) as earnings 
               FROM orders 
               WHERE seller_id = '{$user['id']}' AND status='Completed'";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$total_sales = $stats['sales'] ?? 0;
$total_earnings = $stats['earnings'] ?? 0;


if ($stats_result && mysqli_num_rows($stats_result) > 0) {
   $stats = mysqli_fetch_assoc($stats_result);
   $total_sales = $stats['sales'] ?? 0;
   $total_earnings = $stats['earnings'] ?? 0;
   $balance = $total_earnings; // Later: subtract withdrawals if you add that feature
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <meta charset="UTF-8">
   <title>Seller Dashboard - YOU DO NOTES</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
      body {
         font-family: 'Poppins', sans-serif;
         background: #f9f6f1;
         /* beige background */
         margin: 0;
         padding: 0;
      }

      .container {
         width: 90%;
         max-width: 1000px;
         margin: 40px auto;
         background: #fff;
         border-radius: 15px;
         padding: 25px;
         box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      }

      h2 {
         text-align: center;
         color: #444;
         margin-bottom: 20px;
      }

      h3 {
         margin-top: 30px;
         color: #333;
      }

      p {
         color: #666;
      }

      /* Upload form */
      form {
         background: #fdfaf6;
         padding: 20px;
         border-radius: 10px;
         margin-bottom: 30px;
         box-shadow: inset 0px 2px 6px rgba(0, 0, 0, 0.05);
      }

      input,
      textarea,
      button {
         width: 100%;
         padding: 10px;
         margin: 8px 0;
         border-radius: 8px;
         border: 1px solid #ddd;
         font-size: 14px;
      }

      textarea {
         resize: none;
         height: 80px;
      }

      button {
         background: #c8a97e;
         color: white;
         border: none;
         cursor: pointer;
         font-weight: bold;
         transition: background 0.3s ease;
      }

      button:hover {
         background: #b08968;
      }

      /* Notes Grid */
      .notes-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
         gap: 20px;
         margin-top: 20px;
      }

      .note-card {
         background: #fff;
         border: 1px solid #eee;
         border-radius: 12px;
         padding: 15px;
         text-align: center;
         box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.05);
         transition: transform 0.2s ease;
      }

      .note-card:hover {
         transform: translateY(-5px);
      }

      .note-card img {
         width: 100%;
         height: 140px;
         object-fit: cover;
         border-radius: 10px;
         margin-bottom: 10px;
      }

      .note-card h3 {
         font-size: 16px;
         color: #444;
         margin: 10px 0;
      }

      .note-card p {
         font-size: 14px;
         color: #777;
         margin: 5px 0;
      }

      .note-card a {
         display: inline-block;
         margin: 5px;
         padding: 8px 12px;
         border-radius: 8px;
         text-decoration: none;
         font-size: 13px;
         font-weight: bold;
         color: #fff;
         background: #c8a97e;
         transition: background 0.3s ease;
      }

      .note-card a:hover {
         background: #b08968;
      }

      .stats-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         gap: 20px;
         margin: 20px 0;
      }

      .stat-card {
         background: #fdfaf6;
         border: 1px solid #eee;
         border-radius: 12px;
         text-align: center;
         padding: 20px;
         box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.05);
      }

      .stat-card h3 {
         font-size: 22px;
         margin: 0;
         color: #444;
      }

      .stat-card p {
         color: #777;
         font-size: 14px;
         margin: 5px 0 0;
      }
   </style>
</head>

<body>
   <div class="container">
      <h2>Seller Dashboard</h2>
      <p>Welcome, <strong><?php echo htmlspecialchars($email); ?></strong></p>
         <div style="display:flex; gap:30px; margin:20px 0;">
   <div style="flex:1; background:#fdfaf6; padding:20px; border-radius:12px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
      <h3><?php echo $total_sales; ?></h3>
      <p>Total Sales</p>
   </div>
   <div style="flex:1; background:#fdfaf6; padding:20px; border-radius:12px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
      <h3>₱<?php echo number_format($total_earnings,2); ?></h3>
      <p>Total Earnings</p>
   </div>
</div>
      <!-- Payment Info -->
      <h3>Payment Information</h3>
      <form method="POST" action="">
         <label>Preferred Payment Method</label>
         <select name="payment_method" required>
            <option value="GCash" <?php if (($user['payment_method'] ?? '') == 'GCash') echo 'selected'; ?>>GCash</option>
            <option value="PayMaya" <?php if (($user['payment_method'] ?? '') == 'PayMaya') echo 'selected'; ?>>PayMaya</option>
         </select>

         <label>Fill Number</label>
         <input type="text" name="account_number" value="<?php echo htmlspecialchars($user['gcash_number'] ?: $user['paymaya_number'] ?: ''); ?>" placeholder="09XXXXXXXXX" required>

         <button type="submit" name="update_payment">Update Payment Info</button>
      </form>

      <!-- Upload Form -->
      <h3>Upload New Note</h3>
      <form method="POST" enctype="multipart/form-data" action="upload_note.php">
         <input type="text" name="title" placeholder="Note Title" required><br>
         <textarea name="description" placeholder="Description" required></textarea><br>
         <input type="number" name="price" placeholder="Price" required><br>
         <input type="file" name="image" accept="image/*" required><br>
         <input type="url" name="note_link" placeholder="https://..."><br><br>
         <button type="submit" name="upload" class="btn">Upload Note</button>
      </form>

      <hr>

      <!-- Seller's Notes -->
      <h3>Your Notes</h3>
      <div class="notes-grid">
         <?php
         // ✅ Check if seller_email exists in products table
         $check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'seller_email'");
         if (mysqli_num_rows($check) > 0) {
            $query = "SELECT * FROM products WHERE seller_email = '$email'";
         } else {
            // fallback: just show all products
            $query = "SELECT * FROM products";
         }

         $result = mysqli_query($conn, $query);

         if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) { ?>
               <div class="note-card">
                  <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
                  <h3><?php echo $row['title']; ?></h3>
                  <p><?php echo $row['description']; ?></p>
                  <p><strong>₱<?php echo $row['price']; ?></strong></p>
                  <a href="edit_note.php?id=<?php echo $row['id']; ?>" class="btn">Edit</a>
                  <a href="delete_note.php?id=<?php echo $row['id']; ?>" class="btn">Delete</a>
               </div>
         <?php }
         } else {
            echo "<p>No notes uploaded yet.</p>";
         }
         ?>
      </div>
   </div>
</body>

</html>