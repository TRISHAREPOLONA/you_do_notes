<?php
include("../config.php");
session_start();

// FILTER LOGIC -------------------------
$time_filter = $_GET['filter'] ?? null;
$selected_month = $_GET['month'] ?? null;
$selected_year = $_GET['year'] ?? null;

$filter_condition = "WHERE o.payment_status = 'Paid'";

// WEEKLY FILTER
if ($time_filter == "weekly") {
    $filter_condition .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

// MONTHLY FILTER (THIS YEAR ONLY)
elseif ($time_filter == "monthly" && $selected_month) {
    $filter_condition .= "
        AND MONTH(o.created_at) = '$selected_month'
        AND YEAR(o.created_at) = YEAR(CURDATE())
    ";
}

// YEARLY FILTER (YOU CHOOSE YEAR + MONTH)
elseif ($time_filter == "yearly" && $selected_year && $selected_month) {
    $filter_condition .= "
        AND YEAR(o.created_at) = '$selected_year'
        AND MONTH(o.created_at) = '$selected_month'
    ";
}

// DEFAULT (no filter)
else {
    $filter_condition = "WHERE o.payment_status = 'Paid'";
}

// Determine what to show in the filter info
if ($time_filter == "weekly") {
    $filter_title = "Weekly Sales";
    $filter_subtext = "Last 7 Days";
} elseif ($time_filter == "monthly" && $selected_month) {
    $month_name = date("F", mktime(0, 0, 0, $selected_month, 1));
    $filter_title = "Monthly Sales";
    $filter_subtext = "$month_name " . date("Y");
} elseif ($time_filter == "yearly" && $selected_year && $selected_month) {
    $month_name = date("F", mktime(0, 0, 0, $selected_month, 1));
    $filter_title = "Yearly Sales";
    $filter_subtext = "$month_name $selected_year";
} else {
    $filter_title = "All Sales";
    $filter_subtext = "All Paid Orders";
}

// Fetch filtered sales
$sales_query = "
    SELECT o.*, u.name as user_name, u.email as user_email, 
           p.title as product_title, p.seller_email,
           o.platform_commission, o.seller_earnings
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    $filter_condition
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

.header { 
    text-align:center; 
    margin-bottom:35px; 
}
.header h1 { 
    font-size:2.4rem; 
    color:#5a4b41; 
    font-weight:700; 
    margin-bottom: 15px;
}

/* Filter Container above table */
.table-filter-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f1e9;
    padding: 15px 25px;
    border-radius: 12px 12px 0 0;
    border-left: 4px solid #b08968;
    margin-top: 40px;
}

.filter-info-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.filter-info-text {
    font-size: 16px;
    color: #7f5539;
    font-weight: 500;
}

.filter-info-text strong {
    color: #5a4b41;
}

.filter-info-text small {
    color: #9a8c98;
    font-size: 14px;
    margin-left: 10px;
}

/* Filter Button Styles */
.filter-btn {
    background: #b08968;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.filter-btn:hover {
    background: #a0765b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(160, 118, 91, 0.3);
}

.filter-btn i {
    font-size: 14px;
}

/* Filter Menu Styles */
#filterMenu {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background: white;
    border: 1px solid #e8d5c4;
    width: 180px;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    z-index: 1000;
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

.clear-filter-btn {
    background: none;
    border: 2px solid #b08968;
    color: #b08968;
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s;
}

.clear-filter-btn:hover {
    background: #b08968;
    color: white;
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
    border-radius:0 0 20px 20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}
table {
    width:100%;
    border-collapse:collapse;
}
th {
    background:#e8d9c5;
    padding:12px;
    color:#5a4b41;
    font-weight:700;
    border-top: 1px solid #e0d0b8;
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

    <!-- Filter and Table Container -->
    <div class="table-filter-container" style="position:relative;">
        <div class="filter-info-left">
            <span class="filter-info-text">
                <i class="fas fa-filter"></i>
                <strong><?php echo $filter_title; ?></strong>
                <small><?php echo $filter_subtext; ?></small>
            </span>
        </div>
        
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
            <!-- Filter Button -->
            <button class="filter-btn" onclick="toggleFilterMenu(event)">
                <i class="fas fa-filter"></i>
                <?php 
                if ($time_filter == "weekly") echo "Weekly";
                elseif ($time_filter == "monthly") echo "Monthly";
                elseif ($time_filter == "yearly") echo "Yearly";
                else echo "Filter";
                ?>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <!-- Clear Filter Button -->
            <?php if ($time_filter): ?>
            <button class="clear-filter-btn" onclick="window.location.href='sales_report.php'">
                <i class="fas fa-times"></i> Clear
            </button>
            <?php endif; ?>
            
            <!-- Filter Menu -->
            <div id="filterMenu">
                <a class="filter-option" href="sales_report.php?filter=weekly">
                    <i class="fas fa-calendar-week"></i> Weekly Sales
                </a>
                <a class="filter-option" href="#" onclick="showMonthModal('monthly')">
                    <i class="fas fa-calendar-alt"></i> Monthly Sales
                </a>
                <a class="filter-option" href="#" onclick="showMonthModal('yearly')">
                    <i class="fas fa-calendar"></i> Yearly Sales
                </a>
            </div>
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
    event.stopPropagation();
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
    
    let url = 'sales_report.php?filter=' + currentFilterType + '&month=' + selectedMonth;
    
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
    let filterBtn = document.querySelector(".filter-btn");
    
    if (!filterBtn.contains(event.target) && filterMenu.style.display === "block") {
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