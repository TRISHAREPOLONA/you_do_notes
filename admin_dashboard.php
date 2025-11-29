<?php
include("config.php");
session_start();


// Total users
$total_users = 0;
$user_query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$user_result = mysqli_query($conn, $user_query);
if ($user_result) $total_users = mysqli_fetch_assoc($user_result)['total_users'];

// Recent orders (last 7 days)
$recent_orders = 0;
$recent_query = "SELECT COUNT(*) as recent_orders FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_result = mysqli_query($conn, $recent_query);
if ($recent_result) $recent_orders = mysqli_fetch_assoc($recent_result)['recent_orders'];

// Total orders
$total_orders = 0;
$order_query = "SELECT COUNT(*) as total_orders FROM orders";
$order_result = mysqli_query($conn, $order_query);
if ($order_result) $total_orders = mysqli_fetch_assoc($order_result)['total_orders'];

// Number of products
$total_products = 0;
$product_query = "SELECT COUNT(*) as total_products FROM products";
$product_result = mysqli_query($conn, $product_query);
if ($product_result) $total_products = mysqli_fetch_assoc($product_result)['total_products'];

// Total revenue
$total_revenue = 0;
$revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE payment_status = 'Paid'";
$revenue_result = mysqli_query($conn, $revenue_query);
if ($revenue_result) $total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'];

// Today's sales
$today_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as today_sales FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'Paid'";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = $today_sales_result ? mysqli_fetch_assoc($today_sales_result)['today_sales'] : 0;

// Monthly sales
$month_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as month_sales FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND payment_status = 'Paid'";
$month_sales_result = mysqli_query($conn, $month_sales_query);
$month_sales = $month_sales_result ? mysqli_fetch_assoc($month_sales_result)['month_sales'] : 0;

// Platform earnings
$platform_earnings_query = "SELECT COALESCE(SUM(platform_commission), 0) as platform_earnings FROM orders WHERE payment_status = 'Paid'";
$platform_earnings_result = mysqli_query($conn, $platform_earnings_query);
$platform_earnings = $platform_earnings_result ? mysqli_fetch_assoc($platform_earnings_result)['platform_earnings'] : 0;

// Recent 5 orders
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
$orders_list = ($orders_result) ? mysqli_fetch_all($orders_result, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - YOU DO NOTES</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

body { background: #f5ebe0; }


.dashboard { display: flex; min-height: 100vh; }


.sidebar {
    width: 260px;
    background: linear-gradient(180deg, #b08968, #a0765b);
    color: white;
    padding: 20px;
}
.sidebar h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 22px;
    font-weight: 600;
}
.sidebar ul { list-style: none; margin-top: 10px; }
.sidebar ul li { padding: 12px; border-radius: 8px; transition: 0.3s; }
.sidebar ul li:hover { background: rgba(255,255,255,0.15); }
.sidebar ul li a { color: white; text-decoration: none; font-size: 15px; display: block; }


.main-content { flex: 1; padding: 25px; }


.dashboard-header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.dashboard-header h1 { color: #6b4f4f; }


.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.card h3 { color: #7f5539; font-size: 15px; margin-bottom: 5px; }
.card p {
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 3px;
    color: #5e412f;
}
.subtext { font-size: 12px; color: #9a8c98; }


.small-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.small-card {
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}
.small-card h3 { font-size: 13px; color: #7f5539; margin-bottom: 5px; }
.small-card p { font-size: 20px; font-weight: bold; color: #5e412f; }



.sales-overview {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.sales-overview h2 { color: #6b4f4f; margin-bottom: 15px; }


table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #e8d5c4;
    font-size: 14px;
}
th {
    background: #f0e1d2;
    color: #6b4f4f;
    font-weight: bold;
}
tr:hover { background: #f7ede2; }


.status {
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
}
.status-paid {
    background: #e6f4ea;
    color: #137333;
}
.status-pending {
    background: #fdf2d0;
    color: #b06f00;
}

.earnings-badge {
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
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
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
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
                <div class="subtext">Overall Purchases</div>
            </div>
            <div class="card">
                <h3>Total Notes</h3>
                <p><?php echo $total_products; ?></p>
                <div class="subtext">Available Products</div>
            </div>
            <div class="card">
                <h3>Total Revenue</h3>
                <p>₱<?php echo number_format($total_revenue, 2); ?></p>
                <div class="subtext">All-Time Sales</div>
            </div>
        </div>

        <!-- Sales Overview -->
        <div class="sales-overview">
            <h2>Sales Highlights</h2>

            <!-- Small Cards -->
            <div class="small-cards">
                <div class="small-card">
                    <h3>Today's Sales</h3>
                    <p>₱<?php echo number_format($today_sales, 2); ?></p>
                </div>
                <div class="small-card">
                    <h3>This Month</h3>
                    <p>₱<?php echo number_format($month_sales, 2); ?></p>
                </div>
                <div class="small-card">
                    <h3>Platform Earnings</h3>
                    <p>₱<?php echo number_format($platform_earnings, 2); ?></p>
                </div>
                <div class="small-card">
                    <h3>Success Rate</h3>
                    <p><?php echo $total_orders > 0 ? round(($recent_orders / $total_orders) * 100, 1) : 0; ?>%</p>
                </div>
            </div>

            <!-- Orders Table -->
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
                            <td colspan="8" style="text-align:center; padding: 30px; color:#8d7664;">
                                <i class="fas fa-inbox" style="font-size:40px; margin-bottom:10px; color:#c9b8a8;"></i><br>
                                No recent orders found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders_list as $order): ?>
                        <tr>
                            <td><?php echo $order['order_ref']; ?></td>
                            <td><?php echo $order['user_name']; ?></td>
                            <td><?php echo $order['product_title']; ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>

                            <td><span class="earnings-badge">
                                ₱<?php echo number_format($order['platform_commission'], 2); ?>
                            </span></td>

                            <td><span class="earnings-badge">
                                ₱<?php echo number_format($order['seller_earnings'], 2); ?>
                            </span></td>

                            <td>
                                <span class="status <?php echo $order['payment_status'] == 'Paid' ? 'status-paid' : 'status-pending'; ?>">
                                    <?php echo $order['payment_status']; ?>
                                </span>
                            </td>
                            <td><?php echo date("M j, Y g:i A", strtotime($order['created_at'])); ?></td>
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
