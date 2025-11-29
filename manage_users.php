<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='user'");
    header("Location: admin_users.php");
    exit;
}

// Fetch users
$result = mysqli_query($conn, "SELECT id, name, email, contact, address, role FROM users WHERE role='user' ORDER BY id DESC");
$total_users = mysqli_num_rows($result);

// Count sellers
$sellers_result = mysqli_query($conn, "SELECT COUNT(DISTINCT seller_email) AS seller_count FROM products WHERE seller_email != 'admin@youdo.com'");
$seller_data = mysqli_fetch_assoc($sellers_result);
$seller_count = $seller_data['seller_count'];

$buyer_count = $total_users - $seller_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users - YOU DO NOTES</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Arial;
    background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
    min-height: 100vh;
    display: flex;
}


.sidebar {
    width: 260px;
    background: linear-gradient(135deg, #b08968 0%, #a0765b 100%);
    color: white;
    padding: 30px 20px;
    box-shadow: 4px 0 20px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.sidebar h2 {
    text-align: center;
    font-size: 1.6rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.sidebar ul {
    list-style: none;
}

.sidebar li {
    margin-bottom: 15px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    color: white;
    text-decoration: none;
    transition: .3s;
    font-weight: 500;
}

.sidebar a:hover {
    background: white;
    color: #5a4b41;
    transform: translateX(5px);
}

.main {
    flex: 1;
    padding: 40px 50px;
}

.header {
    text-align: center;
    margin-bottom: 40px;
}

.header h1 {
    color: #5a4b41;
    font-size: 2.4rem;
    font-weight: 700;
}


.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    transition: .3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 30px rgba(0,0,0,0.15);
}

.stat-card h3 {
    color: #8d7b68;
    margin-bottom: 8px;
}

.stat-card p {
    font-size: 2rem;
    font-weight: bold;
    color: #5a4b41;
}


.table-container {
    background: #ffffff;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #e8d9c5;
    padding: 12px;
    border-radius: 8px;
    color: #5a4b41;
    font-weight: 700;
}

td {
    padding: 14px;
    border-bottom: 1px solid #f0f0f0;
    color: #5a4b41;
}

tr:hover {
    background: #faf7f2;
}


.role-badge {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
}

.role-seller {
    background: #e8f5e8;
    color: #2e7d32;
}

.role-buyer {
    background: #e3f2fd;
    color: #1565c0;
}


.btn-delete {
    background: #e74c3c;
    color: white;
    padding: 8px 14px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: .3s;
}

.btn-delete:hover {
    background: #c0392b;
    transform: scale(1.05);
}
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
        <li><a href="sales_report.php"><i class="fas fa-file-invoice"></i> Sales Report</a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="header">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card"><h3>Total Users</h3><p><?= $total_users ?></p></div>
        <div class="stat-card"><h3>Sellers</h3><p><?= $seller_count ?></p></div>
        <div class="stat-card"><h3>Buyers</h3><p><?= max($buyer_count, 0) ?></p></div>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Address</th><th>Role</th><th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($user = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $seller_check = mysqli_query($conn, "SELECT COUNT(*) AS product_count FROM products WHERE seller_email='".$user['email']."'");
                    $seller_data = mysqli_fetch_assoc($seller_check);
                    $is_seller = $seller_data['product_count'] > 0;
                    ?>

                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['contact'] ?: 'N/A') ?></td>
                        <td><?= htmlspecialchars($user['address'] ?: 'N/A') ?></td>
                        <td>
                            <span class="role-badge <?= $is_seller ? 'role-seller' : 'role-buyer' ?>">
                                <?= $is_seller ? "Seller ({$seller_data['product_count']})" : "Buyer" ?>
                            </span>
                        </td>
                        <td>
                            <a class="btn-delete" 
                               onclick="return confirm('Delete user: <?= addslashes($user['name']) ?>?')" 
                               href="?delete=<?= $user['id'] ?>">
                               <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; padding:20px;">No users found</td></tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
