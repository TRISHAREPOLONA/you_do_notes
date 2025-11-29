<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");

// Very basic connection check
if (!$conn) {
    echo "Database connection failed";
    exit;
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='user'");
    header("Location: admin_users.php");
    exit;
}

// Get all users - using only columns that exist in your database
$result = mysqli_query($conn, "SELECT id, name, email, contact, address, role FROM users WHERE role='user' ORDER BY id DESC");

// Count total users
$total_users = mysqli_num_rows($result);

// Count users who have uploaded products (sellers)
$sellers_result = mysqli_query($conn, "SELECT COUNT(DISTINCT seller_email) as seller_count FROM products WHERE seller_email != 'admin@youdo.com'");
$sellers_data = mysqli_fetch_assoc($sellers_result);
$seller_count = $sellers_data['seller_count'];

// Buyers are total users minus sellers (approximate)
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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial; }
        body { background: #f8f9fa; }
        .container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #2c3e50; color: white; padding: 20px; }
        .sidebar h2 { text-align: center; margin-bottom: 20px; }
        .sidebar ul { list-style: none; }
        .sidebar li { padding: 10px; }
        .sidebar a { color: white; text-decoration: none; }
        .main { flex: 1; padding: 20px; }
        .header { background: white; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        
        /* Stats Cards */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 15px; border-radius: 5px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 5px; }
        .stat-card p { font-size: 24px; font-weight: bold; color: #2c3e50; }
        
        table { width: 100%; background: white; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
        .btn { padding: 5px 10px; color: white; text-decoration: none; border-radius: 3px; font-size: 12px; display: inline-block; }
        .btn-delete { background: #e74c3c; }
        .role-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .role-seller { background: #e8f5e8; color: #2e7d32; }
        .role-buyer { background: #e3f2fd; color: #1565c0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>YOU DO NOTES</h2>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_users.php">Users</a></li>
                <li><a href="manage_products.php">Products</a></li>
                <li><a href="sales_report.php">Sales Report</a></li>
            </ul>
        </div>
        
        <div class="main">
            <div class="header">
                <h1>Manage Users</h1>
            </div>
            
            <!-- User Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Sellers</h3>
                    <p><?php echo $seller_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Buyers</h3>
                    <p><?php echo $buyer_count > 0 ? $buyer_count : 0; ?></p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php 
                        // Reset pointer to beginning
                        mysqli_data_seek($result, 0);
                        while($user = mysqli_fetch_assoc($result)): 
                            
                            // Check if user is a seller (has uploaded products)
                            $seller_check = mysqli_query($conn, "SELECT COUNT(*) as product_count FROM products WHERE seller_email = '" . $user['email'] . "'");
                            $seller_data = mysqli_fetch_assoc($seller_check);
                            $is_seller = $seller_data['product_count'] > 0;
                            $user_role = $is_seller ? 'Seller' : 'Buyer';
                        ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['contact'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo strtolower($user_role); ?>">
                                    <?php echo $user_role; ?>
                                    <?php if ($is_seller): ?>
                                        <br><small>(<?php echo $seller_data['product_count']; ?> products)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-delete" 
                                   onclick="return confirm('Delete user: <?php echo addslashes($user['name']); ?>?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                No users found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>