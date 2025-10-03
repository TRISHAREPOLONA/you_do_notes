<?php
include("config.php");
session_start();

// Require login
if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

// Get logged-in user id (same helper logic as checkout)
function getLoggedInUserId($conn)
{
   if (!isset($_SESSION['user'])) return 0;
   if (is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
      return (int) $_SESSION['user']['id'];
   }
   if (is_numeric($_SESSION['user'])) {
      return (int) $_SESSION['user'];
   }
   $email = mysqli_real_escape_string($conn, $_SESSION['user']);
   $q = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
   if ($row = mysqli_fetch_assoc($q)) {
      $_SESSION['user'] = $row;
      return (int)$row['id'];
   }
   return 0;
}

$userId = getLoggedInUserId($conn);
if ($userId <= 0) {
   echo "User not found.";
   exit;
}

// Accept order_ref param
if (!isset($_GET['order_ref'])) {
   echo "No order specified.";
   exit;
}

$orderRef = mysqli_real_escape_string($conn, $_GET['order_ref']);

// Fetch all order rows with this order_ref that belong to the logged-in user
$sql = "
  SELECT o.*, p.title AS product_title, p.description AS product_description, p.file_path, p.price AS product_price
  FROM orders o
  JOIN products p ON o.product_id = p.id
  WHERE o.order_ref = '$orderRef' AND o.user_id = $userId
";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
   echo "Order not found or you don't have access.";
   exit;
}

// collect items and sums
$items = [];
$totalPaid = 0;
$paymentMethod = '';
$paymentStatus = '';
$gcashNumber = '';
$createdAt = '';
while ($row = mysqli_fetch_assoc($res)) {
   $items[] = $row;
   $totalPaid += (float)$row['total_amount'];
   $paymentMethod = $row['payment_method'];
   $paymentStatus = $row['payment_status'];
   $gcashNumber = $row['gcash_number'];
   $createdAt = $row['created_at'];
}
?>

<!DOCTYPE html>
<html>

<head>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <meta charset="utf-8">
   <title>Receipt - YOU DO NOTES</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         background: #f7f3ef;
         margin: 0;
         padding: 30px;
      }

      .container {
         max-width: 800px;
         margin: auto;
         background: #fffaf5;
         padding: 20px;
         border-radius: 10px;
         box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
      }

      h2 {
         text-align: center;
         color: #5a4b41;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
      }

      th,
      td {
         border: 1px solid #e6ddd3;
         padding: 10px;
         text-align: left;
      }

      th {
         background: #f0e8e0;
         color: #5a4b41;
      }

      .summary {
         text-align: right;
         margin-top: 15px;
         font-weight: bold;
      }

      .download {
         display: inline-block;
         padding: 10px 14px;
         background: #b08968;
         color: #fff;
         text-decoration: none;
         border-radius: 8px;
         margin-top: 10px;
      }

      .download:hover {
         background: #a0765b;
      }

      .back {
         display: inline-block;
         margin-top: 20px;
         background: #5a4b41;
         color: #fff;
         padding: 8px 12px;
         border-radius: 8px;
         text-decoration: none;
      }
   </style>
</head>

<body>
   <div class="container">
      <h2>Payment Receipt</h2>
      <p><strong>Order Reference:</strong> <?php echo htmlspecialchars($orderRef); ?></p>
      <p><strong>Date:</strong> <?php echo htmlspecialchars($createdAt); ?></p>
      <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($paymentMethod); ?></p>
      <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($paymentStatus); ?></p>

      <table>
         <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Row Total</th>
            <th>Download</th>
         </tr>

         <?php foreach ($items as $it): ?>
            <tr>
               <td><?php echo htmlspecialchars($it['product_title']); ?><br><small><?php echo htmlspecialchars($it['product_description']); ?></small></td>
               <td>₱<?php echo number_format($it['product_price'], 2); ?></td>
               <td><?php echo (int)$it['quantity']; ?></td>
               <td>₱<?php echo number_format($it['total_amount'], 2); ?></td>
               <td>
                  <?php if ($it['payment_status'] === 'Paid' && !empty($it['file_path'])): ?>
                     <a class="download" href="<?php echo htmlspecialchars($it['file_path']); ?>" download>Download</a>
                  <?php else: ?>
                     <span style="color:orange;">Unavailable</span>
                  <?php endif; ?>
               </td>
            </tr>
         <?php endforeach; ?>
      </table>

      <div class="summary">Total Paid: ₱<?php echo number_format($totalPaid, 2); ?></div>

      <a class="back" href="products.php">&larr; Back to Store</a>
   </div>
</body>

</html>