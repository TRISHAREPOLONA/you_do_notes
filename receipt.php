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
  SELECT o.*, p.title AS product_title, p.description AS product_description, p.file_path, p.price AS product_price,
         p.seller_email, u.name AS seller_name, o.platform_commission, o.seller_earnings
  FROM orders o
  JOIN products p ON o.product_id = p.id
  LEFT JOIN users u ON p.seller_email = u.email
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
$totalCommission = 0;
$totalSellerEarnings = 0;
$paymentMethod = '';
$paymentStatus = '';
$gcashNumber = '';
$createdAt = '';
while ($row = mysqli_fetch_assoc($res)) {
   $items[] = $row;
   $totalPaid += (float)$row['total_amount'];
   $totalCommission += (float)$row['platform_commission'];
   $totalSellerEarnings += (float)$row['seller_earnings'];
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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
            min-height: 100vh;
            padding: 30px;
        }

        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #b08968 0%, #a0765b 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .receipt-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .receipt-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .receipt-body {
            padding: 40px;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .info-item {
            text-align: center;
        }

        .info-item .label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-item .value {
            font-size: 1.1rem;
            color: #333;
            font-weight: 700;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .items-table th {
            background: #5a4b41;
            color: white;
            padding: 18px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .items-table td {
            padding: 18px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .items-table tr:hover {
            background: #fafafa;
        }

        .item-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .item-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .item-seller {
            color: #b08968;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .download-btn {
            background: #b08968;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .download-btn:hover {
            background: #a0765b;
            transform: translateY(-2px);
        }

        .earnings-breakdown {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
        }

        .breakdown-title {
            color: #5a4b41;
            font-size: 1.3rem;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 700;
        }

        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: center;
        }

        .breakdown-item {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .breakdown-item .amount {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .breakdown-item.platform .amount {
            color: #e74c3c;
        }

        .breakdown-item.seller .amount {
            color: #27ae60;
        }

        .breakdown-item.total .amount {
            color: #5a4b41;
        }

        .breakdown-item .label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .receipt-footer {
            text-align: center;
            padding: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .back-btn {
            background: #5a4b41;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #b08968;
            transform: translateY(-2px);
        }

        .thank-you {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #e8f5e8;
            border-radius: 10px;
            color: #2d5016;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .receipt-body {
                padding: 20px;
            }
            
            .order-info {
                grid-template-columns: 1fr;
            }
            
            .items-table {
                font-size: 0.9rem;
            }
            
            .items-table th,
            .items-table td {
                padding: 12px 8px;
            }
            
            .breakdown-grid {
                grid-template-columns: 1fr;
            }
        }
   </style>
</head>

<body>
   <div class="receipt-container">
      <div class="receipt-header">
         <h1><i class="fas fa-receipt"></i> Payment Receipt</h1>
         <p>Thank you for your purchase!</p>
      </div>

      <div class="receipt-body">
         <div class="order-info">
            <div class="info-item">
               <div class="label">Order Reference</div>
               <div class="value"><?php echo htmlspecialchars($orderRef); ?></div>
            </div>
            <div class="info-item">
               <div class="label">Date & Time</div>
               <div class="value"><?php echo htmlspecialchars($createdAt); ?></div>
            </div>
            <div class="info-item">
               <div class="label">Payment Method</div>
               <div class="value"><?php echo htmlspecialchars($paymentMethod); ?></div>
            </div>
            <div class="info-item">
               <div class="label">Status</div>
               <div class="value" style="color: #27ae60;"><?php echo htmlspecialchars($paymentStatus); ?></div>
            </div>
         </div>

         <table class="items-table">
            <thead>
               <tr>
                  <th>Item Details</th>
                  <th>Price</th>
                  <th>Qty</th>
                  <th>Total</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($items as $it): ?>
                  <tr>
                     <td>
                        <div class="item-title"><?php echo htmlspecialchars($it['product_title']); ?></div>
                        <div class="item-description"><?php echo htmlspecialchars($it['product_description']); ?></div>
                        <?php if (!empty($it['seller_name'])): ?>
                           <div class="item-seller">Seller: <?php echo htmlspecialchars($it['seller_name']); ?></div>
                        <?php endif; ?>
                     </td>
                     <td>₱<?php echo number_format($it['product_price'], 2); ?></td>
                     <td><?php echo (int)$it['quantity']; ?></td>
                     <td><strong>₱<?php echo number_format($it['total_amount'], 2); ?></strong></td>
                     <td>
                        <?php if ($it['payment_status'] === 'Paid' && !empty($it['file_path'])): ?>
                           <a class="download-btn" href="<?php echo htmlspecialchars($it['file_path']); ?>" download>
                              <i class="fas fa-download"></i> Download
                           </a>
                        <?php else: ?>
                           <span style="color: #e74c3c; font-size: 0.9rem;">
                              <i class="fas fa-clock"></i> Processing
                           </span>
                        <?php endif; ?>
                     </td>
                  </tr>
               <?php endforeach; ?>
            </tbody>
         </table>

         <div class="earnings-breakdown">
            <h3 class="breakdown-title"><i class="fas fa-chart-pie"></i> Earnings Breakdown</h3>
            <div class="breakdown-grid">
               <div class="breakdown-item total">
                  <div class="amount">₱<?php echo number_format($totalPaid, 2); ?></div>
                  <div class="label">Total Payment</div>
               </div>
               <div class="breakdown-item platform">
                  <div class="amount">-₱<?php echo number_format($totalCommission, 2); ?></div>
                  <div class="label">Platform Commission (20%)</div>
               </div>
               <div class="breakdown-item seller">
                  <div class="amount">₱<?php echo number_format($totalSellerEarnings, 2); ?></div>
                  <div class="label">Seller Earnings</div>
               </div>
            </div>
         </div>

         <div class="thank-you">
            <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <h3>Purchase Completed Successfully!</h3>
            <p>Your notes are ready for download. Thank you for supporting our community of sellers!</p>
         </div>
      </div>

      <div class="receipt-footer">
         <a class="back-btn" href="products.php">
            <i class="fas fa-arrow-left"></i> Continue Shopping
         </a>
      </div>
   </div>
</body>
</html>