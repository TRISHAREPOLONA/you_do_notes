<?php
include("../config.php");
session_start();

 if ($user['role'] == 'admin') {
    header("Location: login.php");
    exit;
}

// FILTER LOGIC -------------------------
$time_filter = $_GET['filter'] ?? null;
$selected_month = $_GET['month'] ?? null;
$selected_year = $_GET['year'] ?? null;

$filter_condition = "";

// WEEKLY FILTER
if ($time_filter == "weekly") {
    $filter_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

// MONTHLY FILTER (THIS YEAR ONLY)
elseif ($time_filter == "monthly" && $selected_month) {
    $filter_condition = "
        AND MONTH(created_at) = '$selected_month'
        AND YEAR(created_at) = YEAR(CURDATE())
    ";
}

// YEARLY FILTER (YOU CHOOSE YEAR + MONTH)
elseif ($time_filter == "yearly" && $selected_year && $selected_month) {
    $filter_condition = "
        AND YEAR(created_at) = '$selected_year'
        AND MONTH(created_at) = '$selected_month'
    ";
}

// DEFAULT (no filter)
else {
    $filter_condition = "";
}

// ---------------------------------------
// DASHBOARD METRICS WITH FILTER APPLIED
// ---------------------------------------

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

// Filtered orders (based on filter condition)
$filter_orders = 0;
$filter_query = "SELECT COUNT(*) as filter_orders FROM orders WHERE 1 $filter_condition";
$filter_result = mysqli_query($conn, $filter_query);
if ($filter_result) $filter_orders = mysqli_fetch_assoc($filter_result)['filter_orders'];

// Total orders (all-time)
$total_orders = 0;
$order_query = "SELECT COUNT(*) as total_orders FROM orders";
$order_result = mysqli_query($conn, $order_query);
if ($order_result) $total_orders = mysqli_fetch_assoc($order_result)['total_orders'];

// Number of products
$total_products = 0;
$product_query = "SELECT COUNT(*) as total_products FROM products";
$product_result = mysqli_query($conn, $product_query);
if ($product_result) $total_products = mysqli_fetch_assoc($product_result)['total_products'];

// Filtered revenue
$filter_revenue = 0;
$filter_revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as filter_revenue FROM orders WHERE payment_status = 'Paid' $filter_condition";
$filter_revenue_result = mysqli_query($conn, $filter_revenue_query);
if ($filter_revenue_result) $filter_revenue = mysqli_fetch_assoc($filter_revenue_result)['filter_revenue'];

// Total revenue (all-time)
$total_revenue = 0;
$revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE payment_status = 'Paid'";
$revenue_result = mysqli_query($conn, $revenue_query);
if ($revenue_result) $total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'];

// Today's sales
$today_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as today_sales FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'Paid'";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = $today_sales_result ? mysqli_fetch_assoc($today_sales_result)['today_sales'] : 0;

// Monthly sales (current month)
$month_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as month_sales FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND payment_status = 'Paid'";
$month_sales_result = mysqli_query($conn, $month_sales_query);
$month_sales = $month_sales_result ? mysqli_fetch_assoc($month_sales_result)['month_sales'] : 0;

// Platform earnings
$platform_earnings_query = "SELECT COALESCE(SUM(platform_commission), 0) as platform_earnings FROM orders WHERE payment_status = 'Paid' $filter_condition";
$platform_earnings_result = mysqli_query($conn, $platform_earnings_query);
$platform_earnings = $platform_earnings_result ? mysqli_fetch_assoc($platform_earnings_result)['platform_earnings'] : 0;

// Determine what to show in the card
if ($time_filter == "weekly") {
    $card_title = "Weekly Orders";
    $card_subtext = "Last 7 Days";
} elseif ($time_filter == "monthly" && $selected_month) {
    $month_name = date("F", mktime(0, 0, 0, $selected_month, 1));
    $card_title = "Monthly Orders";
    $card_subtext = "$month_name " . date("Y");
} elseif ($time_filter == "yearly" && $selected_year && $selected_month) {
    $month_name = date("F", mktime(0, 0, 0, $selected_month, 1));
    $card_title = "Yearly Orders";
    $card_subtext = "$month_name $selected_year";
} else {
    $card_title = "Recent Orders";
    $card_subtext = "Last 7 Days";
}

// Recent 5 orders (apply filter if exists)
$orders_query = "
    SELECT o.id, o.order_ref, u.name as user_name, p.title as product_title, 
           o.total_amount, o.created_at, o.payment_status, o.platform_commission, o.seller_earnings
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    WHERE 1 $filter_condition
    ORDER BY o.created_at DESC 
    LIMIT 5
";
$orders_result = mysqli_query($conn, $orders_query);
$orders_list = ($orders_result) ? mysqli_fetch_all($orders_result, MYSQLI_ASSOC) : [];

// Month names for the modal
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
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
    position: relative;
    transition: all 0.3s;
    cursor: pointer;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card h3 { color: #7f5539; font-size: 15px; margin-bottom: 5px; }
.card p {
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 3px;
    color: #5e412f;
}
.subtext { font-size: 12px; color: #9a8c98; }

/* Total Users Card (Blue) */
.users-card {
    border-left: 4px solid #4a6fa5;
    background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
}
.users-card:hover {
    background: linear-gradient(135deg, #f0f4f8 0%, #e1e8f0 100%);
}

/* Filter Orders Card (Brown) */
.filter-card {
    border-left: 4px solid #b08968;
    background: linear-gradient(135deg, #ffffff 0%, #f9f5f0 100%);
}
.filter-card:hover {
    background: linear-gradient(135deg, #f9f5f0 0%, #f0e6db 100%);
}

/* Total Orders Card (Green) */
.orders-card {
    border-left: 4px solid #2e7d32;
    background: linear-gradient(135deg, #ffffff 0%, #f0f7f0 100%);
}
.orders-card:hover {
    background: linear-gradient(135deg, #f0f7f0 0%, #e1efe1 100%);
}

/* Total Products Card (Purple) */
.products-card {
    border-left: 4px solid #6a1b9a;
    background: linear-gradient(135deg, #ffffff 0%, #f5f0f8 100%);
}
.products-card:hover {
    background: linear-gradient(135deg, #f5f0f8 0%, #eae1f0 100%);
}

/* Revenue Card (Orange) */
.revenue-card {
    border-left: 4px solid #ef6c00;
    background: linear-gradient(135deg, #ffffff 0%, #fef5e9 100%);
}
.revenue-card:hover {
    background: linear-gradient(135deg, #fef5e9 0%, #fdebd4 100%);
}

/* Filter menu styles */
#filterMenu {
    display: none;
    position: absolute;
    top: 70px;
    right: 10px;
    background: white;
    border: 1px solid #e8d5c4;
    width: 180px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 999;
    overflow: hidden;
}
.filter-option {
    display: block;
    padding: 12px 15px;
    font-size: 13px;
    color: #7f5539;
    text-decoration: none;
    border-bottom: 1px solid #f0e1d2;
    transition: all 0.2s;
}
.filter-option:last-child {
    border-bottom: none;
}
.filter-option:hover {
    background: #f8f1e9;
    color: #b08968;
    padding-left: 20px;
}
.filter-option i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}

/* Active filter indicator */
.filter-active::after {
    content: "Active Filter";
    position: absolute;
    top: 8px;
    right: 8px;
    background: #b08968;
    color: white;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: bold;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 400px;
    animation: modalFade 0.3s;
}

@keyframes modalFade {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0e1d2;
}

.modal-header h3 {
    color: #6b4f4f;
    font-size: 18px;
    margin: 0;
}

.close-modal {
    background: none;
    border: none;
    font-size: 20px;
    color: #9a8c98;
    cursor: pointer;
    transition: color 0.3s;
}

.close-modal:hover {
    color: #b08968;
}

/* Month Grid */
.month-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}

.month-option {
    background: #f8f1e9;
    border: 2px solid #e8d5c4;
    border-radius: 10px;
    padding: 15px 5px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    color: #7f5539;
}

.month-option:hover {
    background: #b08968;
    color: white;
    border-color: #b08968;
    transform: translateY(-2px);
}

.month-option.active {
    background: #b08968;
    color: white;
    border-color: #b08968;
}

/* Year Selector */
.year-selector {
    margin: 15px 0;
    text-align: center;
}

.year-selector label {
    display: block;
    margin-bottom: 8px;
    color: #7f5539;
    font-weight: 500;
}

.year-input {
    width: 100%;
    padding: 10px;
    border: 2px solid #e8d5c4;
    border-radius: 8px;
    font-size: 14px;
    text-align: center;
    color: #5e412f;
}

.year-input:focus {
    outline: none;
    border-color: #b08968;
}

/* Modal Actions */
.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 14px;
}

.btn-primary {
    background: #b08968;
    color: white;
}

.btn-primary:hover {
    background: #a0765b;
}

.btn-secondary {
    background: #f0e1d2;
    color: #7f5539;
}

.btn-secondary:hover {
    background: #e8d5c4;
}

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

.filter-info {
    background: #f8f1e9;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #7f5539;
    border-left: 4px solid #b08968;
}
.filter-info i {
    margin-right: 8px;
    color: #b08968;
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

        <!-- Show filter info if active -->
        <?php if ($time_filter): ?>
        <div class="filter-info">
            <i class="fas fa-filter"></i>
            Currently viewing: <strong><?php echo $card_title; ?></strong> - <?php echo $card_subtext; ?>
            <a href="admin_dashboard.php" style="float:right; color:#b08968; text-decoration:none; font-size:12px;">
                <i class="fas fa-times"></i> Clear Filter
            </a>
        </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="cards">
            <!-- Clickable Total Users Card -->
            <div class="card users-card" onclick="window.location.href='manage_users.php'">
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <p><?php echo $total_users; ?></p>
                <div class="subtext">Total Users</div>
            </div>
            
            <!-- Clickable Recent Orders Card with Filter Menu -->
            <div class="card filter-card <?php echo $time_filter ? 'filter-active' : ''; ?>" onclick="toggleFilterMenu(event)" style="position:relative;">
                <h3><?php echo $card_title; ?> <i class="fas fa-filter" style="font-size:12px;"></i></h3>
                <p><?php echo $time_filter ? $filter_orders : $recent_orders; ?></p>
                <div class="subtext"><?php echo $card_subtext; ?></div>
                
                <!-- Filter Menu -->
                <div id="filterMenu">
                    <a class="filter-option" href="admin_dashboard.php?filter=weekly">
                        <i class="fas fa-calendar-week"></i> Weekly Orders
                    </a>
                    <a class="filter-option" href="#" onclick="showMonthModal('monthly')">
                        <i class="fas fa-calendar-alt"></i> Monthly Orders
                    </a>
                    <a class="filter-option" href="#" onclick="showMonthModal('yearly')">
                        <i class="fas fa-calendar"></i> Yearly Orders
                    </a>
                </div>
            </div>
            
            <!-- Clickable Total Orders Card -->
            <div class="card orders-card" onclick="window.location.href='sales_report.php'">
                <h3><i class="fas fa-shopping-bag"></i> Total Orders</h3>
                <p><?php echo $total_orders; ?></p>
                <div class="subtext">Overall Purchases</div>
            </div>
            
            <!-- Clickable Total Products Card -->
            <div class="card products-card" onclick="window.location.href='manage_products.php'">
                <h3><i class="fas fa-file-alt"></i> Total Notes</h3>
                <p><?php echo $total_products; ?></p>
                <div class="subtext">Available Products</div>
            </div>
            
            <div class="card revenue-card" onclick="window.location.href='sales_report.php'">
    <h3><i class="fas fa-money-bill-wave"></i> <?php echo $time_filter ? 'Filtered Revenue' : 'Total Revenue'; ?></h3>
    <p>₱<?php echo number_format($time_filter ? $filter_revenue : $total_revenue, 2); ?></p>
    <div class="subtext">All-Time Sales</div>
</div>
        </div>

        <!-- Sales Overview -->
        <div class="sales-overview">
            <h2>Sales Highlights</h2>

            <!-- Small Cards -->
            <div class="small-cards">
                <div class="small-card">
                    <h3><i class="fas fa-sun"></i> Today's Sales</h3>
                    <p>₱<?php echo number_format($today_sales, 2); ?></p>
                </div>
                <div class="small-card">
                    <h3><i class="fas fa-chart-bar"></i> This Month</h3>
                    <p>₱<?php echo number_format($month_sales, 2); ?></p>
                </div>
                <div class="small-card">
                    <h3><i class="fas fa-hand-holding-usd"></i> Platform Earnings</h3>
                    <p>₱<?php echo number_format($platform_earnings, 2); ?></p>
                </div>
                <div class="small-card">
                    <h3><i class="fas fa-box"></i> Total Products</h3>
                    <p><?php echo $total_products; ?></p>
                    <small>Available Notes</small>
                </div>
            </div>

            <!-- Orders Table -->
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> Order Ref</th>
                        <th><i class="fas fa-user"></i> Customer</th>
                        <th><i class="fas fa-file-alt"></i> Product</th>
                        <th><i class="fas fa-money-bill"></i> Amount</th>
                        <th><i class="fas fa-fee"></i> Platform Fee</th>
                        <th><i class="fas fa-coins"></i> Seller Earnings</th>
                        <th><i class="fas fa-info-circle"></i> Status</th>
                        <th><i class="fas fa-calendar"></i> Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders_list)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 30px; color:#8d7664;">
                                <i class="fas fa-inbox" style="font-size:40px; margin-bottom:10px; color:#c9b8a8;"></i><br>
                                No orders found
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

<!-- Month Selection Modal -->
<div id="monthModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Select Month</h3>
            <button class="close-modal" onclick="closeMonthModal()">&times;</button>
        </div>
        
        <div class="month-grid" id="monthGrid">
            <?php foreach ($months as $num => $name): ?>
            <div class="month-option" data-month="<?php echo $num; ?>">
                <?php echo substr($name, 0, 3); ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="year-selector" id="yearSelector" style="display: none;">
            <label for="yearInput">Select Year:</label>
            <input type="number" id="yearInput" class="year-input" 
                   min="2000" max="2030" 
                   value="<?php echo date('Y'); ?>">
        </div>
        
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeMonthModal()">Cancel</button>
            <button class="btn btn-primary" onclick="applyFilter()">Apply Filter</button>
        </div>
    </div>
</div>

<script>
let currentFilterType = '';
let selectedMonth = null;

function toggleFilterMenu(event) {
    event.stopPropagation(); // Prevent card click from triggering
    let menu = document.getElementById("filterMenu");
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

function showMonthModal(filterType) {
    currentFilterType = filterType;
    selectedMonth = null;
    
    // Reset all month options
    document.querySelectorAll('.month-option').forEach(option => {
        option.classList.remove('active');
    });
    
    // Show/hide year selector based on filter type
    const yearSelector = document.getElementById('yearSelector');
    const modalTitle = document.getElementById('modalTitle');
    
    if (filterType === 'yearly') {
        yearSelector.style.display = 'block';
        modalTitle.textContent = 'Select Month and Year';
    } else {
        yearSelector.style.display = 'none';
        modalTitle.textContent = 'Select Month';
    }
    
    // Show modal
    document.getElementById('monthModal').style.display = 'flex';
    
    // Close filter menu
    document.getElementById('filterMenu').style.display = 'none';
}

function closeMonthModal() {
    document.getElementById('monthModal').style.display = 'none';
}

// Month selection
document.addEventListener('DOMContentLoaded', function() {
    const monthOptions = document.querySelectorAll('.month-option');
    
    monthOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            monthOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            selectedMonth = this.getAttribute('data-month');
        });
    });
});

function applyFilter() {
    if (!selectedMonth) {
        alert('Please select a month');
        return;
    }
    
    let url = 'admin_dashboard.php?filter=' + currentFilterType + '&month=' + selectedMonth;
    
    if (currentFilterType === 'yearly') {
        const yearInput = document.getElementById('yearInput');
        const year = yearInput.value;
        
        if (!year || year < 2000 || year > 2030) {
            alert('Please enter a valid year between 2000 and 2030');
            return;
        }
        
        url += '&year=' + year;
    }
    
    window.location.href = url;
}

// Close filter menu when clicking outside
document.addEventListener("click", function(event) {
    let filterMenu = document.getElementById("filterMenu");
    let filterCard = document.querySelector(".filter-card");
    
    if (!filterCard.contains(event.target) && filterMenu.style.display === "block") {
        filterMenu.style.display = "none";
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('monthModal');
    if (event.target === modal) {
        closeMonthModal();
    }
});
</script>

</body>
</html>