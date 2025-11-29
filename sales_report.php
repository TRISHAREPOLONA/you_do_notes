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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            color: white;
            font-size: 1.2em;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .sidebar ul li:hover {
            background-color: #34495e;
        }

        .sidebar ul li.active {
            background-color: #34495e;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .sidebar ul li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .summary-card h3 {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .summary-card p {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .sales-table {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .sales-table h2 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
            color: #2c3e50;
            font-weight: 600;
        }

        table tr:hover {
            background-color: #f9f9f9;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background: #e6f4ea;
            color: #137333;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2><i class="fas fa-graduation-cap"></i> YOU DO NOTES</h2>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="manage_products.php"><i class="fas fa-file-alt"></i> Products</a></li>
                <li class="active"><a href="admin_sales.php"><i class="fas fa-chart-bar"></i> Sales Report</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Sales Report</h1>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Sales</h3>
                    <p>₱<?php echo number_format($total_revenue, 2); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Platform Earnings</h3>
                    <p>₱<?php echo number_format($total_commission, 2); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Seller Earnings</h3>
                    <p>₱<?php echo number_format($total_seller_earnings, 2); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total Transactions</h3>
                    <p><?php echo count($sales); ?></p>
                </div>
            </div>

            <div class="sales-table">
                <h2>Transaction History</h2>
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
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #666; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block; color: #ccc;"></i>
                                    No sales recorded yet
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['order_ref']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($sale['user_name']); ?><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($sale['user_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($sale['product_title']); ?></td>
                                <td>₱<?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($sale['platform_commission'], 2); ?></td>
                                <td>₱<?php echo number_format($sale['seller_earnings'], 2); ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php echo htmlspecialchars($sale['payment_method']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($sale['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>