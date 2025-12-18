<?php
include("../config.php");
session_start();

// Check if admin
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// View file content
if (isset($_GET['view_content'])) {
    $id = intval($_GET['view_content']);
    $query = "SELECT * FROM products WHERE id=$id";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
    
    if ($product) {
        // Display file content based on type
        if (!empty($product['file_path'])) {
            $file_path = $product['file_path'];
            $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            
            // Check if file exists
            if (file_exists($file_path)) {
                // Determine how to display based on file type
                switch($file_ext) {
                    case 'pdf':
                        header('Content-type: application/pdf');
                        readfile($file_path);
                        exit;
                        
                    case 'txt':
                        header('Content-type: text/plain');
                        echo file_get_contents($file_path);
                        exit;
                        
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        header('Content-type: image/' . $file_ext);
                        readfile($file_path);
                        exit;
                        
                    default:
                        // For other file types, show download option
                        echo "<html><body>";
                        echo "<h2>File Preview</h2>";
                        echo "<p>File: " . htmlspecialchars(basename($file_path)) . "</p>";
                        echo "<p>Size: " . filesize($file_path) . " bytes</p>";
                        echo "<p><a href='$file_path' download>Download File</a></p>";
                        echo "<p><a href='manage_products.php'>Back to Products</a></p>";
                        echo "</body></html>";
                        exit;
                }
            } else {
                die("File not found: " . htmlspecialchars($file_path));
            }
        } elseif (!empty($product['note_link'])) {
            // For links, redirect or embed
            header("Location: " . $product['note_link']);
            exit;
        } else {
            die("No file or link available for this product.");
        }
    }
    exit;
}

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
    border: none;
    cursor: pointer;
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

.btn-preview {
    background: #9b59b6;
    color: white;
}
.btn-preview:hover {
    background: #8e44ad;
    transform: scale(1.05);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
}

.modal-content {
    background-color: #fffaf5;
    margin: 5% auto;
    padding: 30px;
    border-radius: 20px;
    width: 80%;
    max-width: 900px;
    max-height: 80vh;
    overflow: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.preview-frame {
    width: 100%;
    height: 600px;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin-top: 20px;
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
                            <a class="btn btn-view" href="<?= $p['file_path'] ?>" target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <?php if (in_array(strtolower(pathinfo($p['file_path'], PATHINFO_EXTENSION)), ['pdf', 'txt', 'jpg', 'jpeg', 'png'])): ?>
                                <button class="btn btn-preview" onclick="previewFile(<?= $p['id'] ?>)">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            <?php endif; ?>
                        <?php elseif (!empty($p['note_link'])): ?>
                            <a class="btn btn-view" href="<?= $p['note_link'] ?>" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Visit Link
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

<!-- Preview Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modalTitle">File Preview</h3>
        <div id="previewContent">
            <iframe id="previewFrame" class="preview-frame" src=""></iframe>
        </div>
    </div>
</div>

<script>
// Modal functionality
const modal = document.getElementById('previewModal');
const closeBtn = document.querySelector('.close');
const previewFrame = document.getElementById('previewFrame');

function previewFile(productId) {
    // Load the file content in an iframe
    previewFrame.src = 'manage_products.php?view_content=' + productId;
    modal.style.display = 'block';
}

// Close modal when clicking X
closeBtn.onclick = function() {
    modal.style.display = 'none';
    previewFrame.src = ''; // Clear the iframe
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
        previewFrame.src = '';
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        modal.style.display = 'none';
        previewFrame.src = '';
    }
});
</script>

</body>
</html>