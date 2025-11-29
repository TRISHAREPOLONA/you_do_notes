<?php
include("config.php");
session_start();

// Fetch all completed transactions
$sales_query = "
    SELECT o.*, u.name as user_name, u.email as user_email, 
           p.title as product_title, p.seller_email,
           o.platform_commission, o.seller_earnings
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    WHERE o.payment_status = 'Paid'
    ORDER BY o.created_at DESC
";
$sales_result = mysqli_query($conn, $sales_query);
$sales = mysqli_fetch_all($sales_result, MYSQLI_ASSOC);

// Calculate totals
$total_revenue = 0;
$total_commission = 0;
$total_seller_earnings = 0;

foreach ($sales as $sale) {
    $total_revenue += $sale['total_amount'];
    $total_commission += $sale['platform_commission'];
    $total_seller_earnings += $sale['seller_earnings'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Report - YOU DO NOTES</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Segoe UI', Arial;
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
}


.sidebar {
    width: 260px;
    background: linear-gradient(135deg, #b08968 0%, #a0765b 100%);
    color: white;
    padding: 30px 20px;
    box-shadow: 4px 0 20px rgba(0,0,0,0.15);
}
.sidebar h2 {
    text-align: center;
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 25px;
}
.sidebar ul { list-style:none; }
.sidebar li { margin-bottom:15px; }
.sidebar a {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 15px;
    background: rgba(255,255,255,0.15);
    border-radius:12px;
    color:white;
    text-decoration:none;
    font-weight:500;
    transition:.3s;
}
.sidebar a:hover { background:white; color:#5a4b41; transform:translateX(5px); }
.sidebar .active a { background:white; color:#5a4b41; }


.main {
    flex:1;
    padding:40px 50px;
}

.header { text-align:center; margin-bottom:35px; }
.header h1 { font-size:2.4rem; color:#5a4b41; font-weight:700; }


.summary-cards {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(260px,1fr));
    gap:25px;
    margin-bottom:40px;
}
.summary-card {
    background:white;
    padding:25px;
    border-radius:20px;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    transition:.3s;
}
.summary-card:hover { transform:translateY(-5px); box-shadow:0 14px 30px rgba(0,0,0,0.15); }
.summary-card h3 { color:#8d7b68; margin-bottom:8px; }
.summary-card p { font-size:2rem; font-weight:bold; color:#5a4b41; }


.table-container {
    background:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}
table {
    width:100%;
    border-collapse:collapse;
}
th {
    background:#e8d9c5;
    padding:12px;
    border-radius:8px;
    color:#5a4b41;
    font-weight:700;
}
td { padding:14px; border-bottom:1px solid #f0f0f0; color:#5a4b41; }
tr:hover { background:#faf7f2; }


.badge {
    padding:6px 14px;
    border-radius:30px;
    font-size:0.85rem;
    font-weight:600;
    display:inline-block;
}
.badge-success { background:#e8f6e8; color:#2e7d32; }
</style>
</head>

<body>
<!-- SIDEBAR -->
<div class="sidebar">
    <h2>YOU DO NOTES</h2>
    <ul>
        <li><a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
        <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="manage_products.php"><i class="fas fa-box"></i> Products</a></li>
        <li class="active"><a href="sales_report.php"><i class="fas fa-file-invoice"></i> Sales Report</a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="header">
        <h1><i class="fas fa-chart-bar"></i> Sales Report</h1>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total Sales</h3>
            <p>₱<?= number_format($total_revenue,2) ?></p>
        </div>
        <div class="summary-card">
            <h3>Platform Earnings</h3>
            <p>₱<?= number_format($total_commission,2) ?></p>
        </div>
        <div class="summary-card">
            <h3>Seller Earnings</h3>
            <p>₱<?= number_format($total_seller_earnings,2) ?></p>
        </div>
        <div class="summary-card">
            <h3>Total Transactions</h3>
            <p><?= count($sales) ?></p>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order Ref</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Platform Fee</th>
                    <th>Seller Earnings</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($sales)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:#8d7b68;">
                            <i class="fas fa-inbox" style="font-size:40px; margin-bottom:10px; opacity:.4;"></i><br>
                            No sales recorded yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['order_ref']) ?></td>
                            <td>
                                <?= htmlspecialchars($sale['user_name']) ?><br>
                                <small style="color:#666"><?= htmlspecialchars($sale['user_email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($sale['product_title']) ?></td>
                            <td>₱<?= number_format($sale['total_amount'],2) ?></td>
                            <td>₱<?= number_format($sale['platform_commission'],2) ?></td>
                            <td>₱<?= number_format($sale['seller_earnings'],2) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($sale['payment_method']) ?></span></td>
                            <td><?= date('M j, Y g:i A', strtotime($sale['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
