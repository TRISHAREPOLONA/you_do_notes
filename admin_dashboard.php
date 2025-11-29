<?php
include("config.php");
session_start();

// Fetch data from database
$total_users = 0;
$recent_orders = 0;
$total_orders = 0;
$total_products = 0;
$total_revenue = 0;
$orders_list = [];

// Total users
$user_query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$user_result = mysqli_query($conn, $user_query);
if ($user_result) {
    $total_users = mysqli_fetch_assoc($user_result)['total_users'];
}

// Recent orders (last 7 days)
$recent_query = "SELECT COUNT(*) as recent_orders FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_result = mysqli_query($conn, $recent_query);
if ($recent_result) {
    $recent_orders = mysqli_fetch_assoc($recent_result)['recent_orders'];
}

// Total orders
$order_query = "SELECT COUNT(*) as total_orders FROM orders";
$order_result = mysqli_query($conn, $order_query);
if ($order_result) {
    $total_orders = mysqli_fetch_assoc($order_result)['total_orders'];
}

// Number of products (notes)
$product_query = "SELECT COUNT(*) as total_products FROM products";
$product_result = mysqli_query($conn, $product_query);
if ($product_result) {
    $total_products = mysqli_fetch_assoc($product_result)['total_products'];
}

// Total revenue
$revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE payment_status = 'Paid'";
$revenue_result = mysqli_query($conn, $revenue_query);
if ($revenue_result) {
    $total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'];
}

// Today's sales
$today_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as today_sales FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'Paid'";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = $today_sales_result ? mysqli_fetch_assoc($today_sales_result)['today_sales'] : 0;

// This month sales
$month_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as month_sales FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND payment_status = 'Paid'";
$month_sales_result = mysqli_query($conn, $month_sales_query);
$month_sales = $month_sales_result ? mysqli_fetch_assoc($month_sales_result)['month_sales'] : 0;

// Platform earnings
$platform_earnings_query = "SELECT COALESCE(SUM(platform_commission), 0) as platform_earnings FROM orders WHERE payment_status = 'Paid'";
$platform_earnings_result = mysqli_query($conn, $platform_earnings_query);
$platform_earnings = $platform_earnings_result ? mysqli_fetch_assoc($platform_earnings_result)['platform_earnings'] : 0;

// Recent orders list
$orders_query = "
    SELECT o.id, o.order_ref, u.name as user_name, p.title as product_title, 
           o.total_amount, o.created_at, o.payment_status, o.platform_commission, o.seller_earnings
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at DESC 
    LIMIT 5
";
$orders_result = mysqli_query($conn, $orders_query);
if ($orders_result) {
    $orders_list = mysqli_fetch_all($orders_result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YOU DO NOTES</title>
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

        /* Sidebar - Same as your design */
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        /* Header - Same style as your navbar */
        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Cards - Same style as your note cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .small-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .small-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .small-card h3 {
            color: #666;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .small-card p {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .card .subtext {
            font-size: 12px;
            color: #888;
        }

        .small-card .subtext {
            font-size: 11px;
            color: #888;
        }

        /* Table - Same style as your tables */
        .sales-overview {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .sales-overview h2 {
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

        /* Status badges - Same style as your design */
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-paid {
            background: #e6f4ea;
            color: #137333;
        }

        .status-pending {
            background: #fef7e0;
            color: #b06000;
        }

        .earnings-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            background: #e3f2fd;
            color: #1565c0;
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
                <li><a href="sales_report.php"><i class="fas fa-chart-bar"></i> Sales Report</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Admin Dashboard</h1>
            </div>

            <!-- Summary Cards -->
            <div class="cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                    <div class="subtext">Registered Students</div>
                </div>
                <div class="card">
                    <h3>Recent Orders</h3>
                    <p><?php echo $recent_orders; ?></p>
                    <div class="subtext">Last 7 Days</div>
                </div>
                <div class="card">
                    <h3>Total Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                    <div class="subtext">All Time</div>
                </div>
                <div class="card">
                    <h3>Available Notes</h3>
                    <p><?php echo $total_products; ?></p>
                    <div class="subtext">Total Products</div>
                </div>
                <div class="card">
                    <h3>Total Revenue</h3>
                    <p>₱<?php echo number_format($total_revenue, 2); ?></p>
                    <div class="subtext">All Time Sales</div>
                </div>
            </div>

            <!-- Sales Overview -->
            <div class="sales-overview">
                <h2>Sales Overview</h2>
                
                <!-- Quick Sales Stats -->
                <div class="small-cards">
                    <div class="small-card">
                        <h3>Today's Sales</h3>
                        <p>₱<?php echo number_format($today_sales, 2); ?></p>
                        <div class="subtext">Today's Revenue</div>
                    </div>
                    <div class="small-card">
                        <h3>This Month</h3>
                        <p>₱<?php echo number_format($month_sales, 2); ?></p>
                        <div class="subtext">Monthly Revenue</div>
                    </div>
                    <div class="small-card">
                        <h3>Platform Earnings</h3>
                        <p>₱<?php echo number_format($platform_earnings, 2); ?></p>
                        <div class="subtext">20% Commission</div>
                    </div>
                    <div class="small-card">
                        <h3>Success Rate</h3>
                        <p><?php echo $total_orders > 0 ? round(($recent_orders / $total_orders) * 100, 1) : 0; ?>%</p>
                        <div class="subtext">Recent Activity</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Order Ref</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Platform Fee</th>
                            <th>Seller Earnings</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders_list)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #666; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block; color: #ccc;"></i>
                                    No recent sales found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($orders_list as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_ref']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['product_title']); ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="earnings-badge">
                                        ₱<?php echo number_format($order['platform_commission'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="earnings-badge">
                                        ₱<?php echo number_format($order['seller_earnings'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status <?php echo $order['payment_status'] === 'Paid' ? 'status-paid' : 'status-pending'; ?>">
                                        <?php echo htmlspecialchars($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
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