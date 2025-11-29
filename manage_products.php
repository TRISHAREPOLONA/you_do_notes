<?php
include("config.php");
session_start();

// Handle Admin Actions
if (isset($_GET['approve'])) {
    $id = mysqli_real_escape_string($conn, $_GET['approve']);
    mysqli_query($conn, "UPDATE products SET status='approved' WHERE id='$id'");
    header("Location: manage_products.php"); exit;
}
if (isset($_GET['reject'])) {
    $id = mysqli_real_escape_string($conn, $_GET['reject']);
    mysqli_query($conn, "UPDATE products SET status='rejected' WHERE id='$id'");
    header("Location: manage_products.php"); exit;
}
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: manage_products.php"); exit;
}

// Fetch all products
$products_query = "SELECT * FROM products ORDER BY id DESC";
$products_result = mysqli_query($conn, $products_query);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - YOU DO NOTES</title>
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

        /* Table - Same style as your tables */
        .products-table {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .products-table h2 {
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

        /* Action buttons */
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin: 2px;
            display: inline-block;
        }

        .btn-approve {
            background: #27ae60;
            color: white;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-delete {
            background: #c0392b;
            color: white;
        }

        .btn-view {
            background: #3498db;
            color: white;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-approved {
            background: #e6f4ea;
            color: #137333;
        }

        .status-pending {
            background: #fef7e0;
            color: #b06000;
        }

        .status-rejected {
            background: #fde8e8;
            color: #c53030;
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
                <li class="active"><a href="manage_products.php"><i class="fas fa-file-alt"></i> Products</a></li>
              <li><a href="sales_report.php"><i class="fas fa-chart-bar"></i> Sales Report</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Manage Products</h1>
            </div>

            <div class="products-table">
                <h2>All Products</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>File/Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #666; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block; color: #ccc;"></i>
                                    No products found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['seller_email']); ?></td>
                                <td>₱<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($p['course']); ?></td>
                                <td>
                                    <span class="status status-<?php echo $p['status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($p['status'] ?? 'pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($p['file_path'])): ?>
                                        <a href="<?php echo $p['file_path']; ?>" target="_blank" class="btn btn-view">
                                            <i class="fas fa-file"></i> File
                                        </a>
                                    <?php elseif (!empty($p['note_link'])): ?>
                                        <a href="<?php echo $p['note_link']; ?>" target="_blank" class="btn btn-view">
                                            <i class="fas fa-link"></i> Link
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <?php if (($p['status'] ?? 'pending') !== 'approved'): ?>
                                            <a href="manage_products.php?approve=<?php echo $p['id']; ?>" class="btn btn-approve">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                        <?php endif; ?>
                                        <?php if (($p['status'] ?? 'pending') !== 'rejected'): ?>
                                            <a href="manage_products.php?reject=<?php echo $p['id']; ?>" class="btn btn-reject">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        <a href="manage_products.php?delete=<?php echo $p['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
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