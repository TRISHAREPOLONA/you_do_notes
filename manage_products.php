<?php
include("config.php");
session_start();

// Approve product
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE products SET status='approved' WHERE id=$id");
    header("Location: manage_products.php");
    exit;
}

// Reject product
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    mysqli_query($conn, "UPDATE products SET status='rejected' WHERE id=$id");
    header("Location: manage_products.php");
    exit;
}

// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: manage_products.php");
    exit;
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
<title>Manage Products</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>


* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    display: flex;
    font-family: 'Segoe UI', Arial;
    background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
    min-height: 100vh;
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

.sidebar ul { list-style: none; }
.sidebar li { margin-bottom: 15px; }

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.15);
    padding: 12px 15px;
    border-radius: 12px;
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: .3s;
}

.sidebar a:hover {
    background: white;
    color: #5a4b41;
    transform: translateX(5px);
}

.sidebar .active a {
    background: white;
    color: #5a4b41;
}


.main {
    flex: 1;
    padding: 40px 50px;
}

.header {
    text-align: center;
    margin-bottom: 35px;
}

.header h1 {
    font-size: 2.4rem;
    color: #5a4b41;
    font-weight: 700;
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
    border-radius: 10px;
    color: #5a4b41;
    font-weight: 700;
}

td {
    padding: 14px;
    color: #5a4b41;
    border-bottom: 1px solid #f0f0f0;
}

tr:hover {
    background: #faf7f2;
}


.status {
    padding: 7px 14px;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-approved {
    background: #e8f6e8;
    color: #2e7d32;
}

.status-pending {
    background: #fff3d4;
    color: #b97600;
}

.status-rejected {
    background: #fde4e4;
    color: #c53030;
}

.btn {
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 0.87rem;
    font-weight: 600;
    transition: .3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-approve {
    background: #27ae60;
    color: white;
}
.btn-approve:hover {
    background: #1e8c4e;
    transform: scale(1.05);
}

.btn-reject {
    background: #e67e22;
    color: white;
}
.btn-reject:hover {
    background: #cf6e15;
    transform: scale(1.05);
}

.btn-delete {
    background: #e74c3c;
    color: white;
}
.btn-delete:hover {
    background: #c0392b;
    transform: scale(1.05);
}

.btn-view {
    background: #3498db;
    color: white;
}
.btn-view:hover {
    background: #207db8;
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
        <li class="active"><a href="manage_products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="sales_report.php"><i class="fas fa-file-invoice"></i> Sales Report</a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="header">
        <h1><i class="fas fa-box"></i> Manage Products</h1>
    </div>

    <div class="table-container">
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
                    <td colspan="8" style="text-align:center; padding:30px; color:#8d7b68;">
                        <i class="fas fa-inbox" style="font-size:40px; margin-bottom:10px; opacity:.4;"></i><br>
                        No products found
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars($p['seller_email']) ?></td>
                    <td>₱<?= number_format($p['price'], 2) ?></td>
                    <td><?= htmlspecialchars($p['course']) ?></td>

                    <td>
                        <span class="status status-<?= $p['status'] ?? "pending" ?>">
                            <?= ucfirst($p['status'] ?? "pending") ?>
                        </span>
                    </td>

                    <td>
                        <?php if (!empty($p['file_path'])): ?>
                            <a class="btn btn-view" target="_blank" href="<?= $p['file_path'] ?>">
                                <i class="fas fa-file"></i> File
                            </a>
                        <?php elseif (!empty($p['note_link'])): ?>
                            <a class="btn btn-view" target="_blank" href="<?= $p['note_link'] ?>">
                                <i class="fas fa-link"></i> Link
                            </a>
                        <?php else: ?>
                            <span style="color:#999;">N/A</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <?php if ($p['status'] !== "approved"): ?>
                                <a class="btn btn-approve" href="?approve=<?= $p['id'] ?>">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                            <?php endif; ?>

                            <?php if ($p['status'] !== "rejected"): ?>
                                <a class="btn btn-reject" href="?reject=<?= $p['id'] ?>">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            <?php endif; ?>

                            <a class="btn btn-delete" 
                               onclick="return confirm('Delete this product?')"
                               href="?delete=<?= $p['id'] ?>">
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

</body>
</html>
