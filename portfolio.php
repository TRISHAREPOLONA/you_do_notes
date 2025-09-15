<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// --- ADMIN UPLOAD FUNCTIONALITY ---
$message = "";
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    if (isset($_POST['upload'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = $_POST['price'];

        $file_path = "";
        if (!empty($_FILES['file']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_path = $target_dir . basename($_FILES["file"]["name"]);
            move_uploaded_file($_FILES["file"]["tmp_name"], $file_path);
        }

        $sql = "INSERT INTO study_guides (title, description, price, file_path) 
                VALUES ('$title', '$description', '$price', '$file_path')";
        if (mysqli_query($conn, $sql)) {
            $message = "✅ Study guide uploaded successfully!";
        } else {
            $message = "❌ Error: " . mysqli_error($conn);
        }
    }
}

// Fetch study guides
$query = "SELECT * FROM study_guides ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Study Guides - YOU DO NOTES</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {
    background: #f7f3ef;
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
}

/* Navbar */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    background: #fffaf5;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    z-index: 1000;
}
.navbar .logo {
    font-weight: bold;
    color: #5a4b41;
    text-decoration: none;
    font-size: 1.4rem;
}
.navbar .nav-links {
    display: flex;
    gap: 30px;
    margin-right: 90px;
}
.navbar .nav-links a {
    text-decoration: none;
    color: #5a4b41;
    font-weight: 500;
    font-size: 1rem;
    transition: color 0.2s;
}
.navbar .nav-links a:hover {
    color: #b08968;
}

/* Admin Upload Form */
.upload-form {
    background: #fffaf5;
    padding: 20px;
    border-radius: 15px;
    max-width: 700px;
    margin: 20px auto;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.upload-form input, .upload-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
}
.upload-form button {
    background: #5a4b41;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
}
.upload-form button:hover {
    background: #b08968;
}
.message {
    text-align: center;
    margin-bottom: 15px;
    color: green;
}

.container {
    max-width: 1200px;
    margin: 120px auto 40px;
    padding: 20px;
}
h1 {
    text-align: center;
    color: #5a4b41;
}
.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}
.portfolio-item {
    background: #fffaf5;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    text-align: center;
}
.portfolio-item h3 {
    color: #5a4b41;
    margin-bottom: 10px;
}
.portfolio-item p {
    color: #6d5d52;
    margin-bottom: 10px;
}
.btn {
    display: inline-block;
    background: #5a4b41;
    color: white;
    padding: 10px 15px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
}
.btn:hover {
    background: #b08968;
}

/* Responsive Navbar */
@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
        padding: 15px 20px;
    }
    .navbar .nav-links {
        margin-right: 0;
        gap: 15px;
        margin-top: 10px;
    }
}
</style>
</head>
<body>
<div class="navbar">
    <a href="index.php" class="logo">YOU DO NOTES</a>
    <div class="nav-links">
        <a href="products.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </div>
</div>

<div class="container">
    <h1>Study Guides</h1>

    <!-- Admin Upload Form -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <div class="upload-form">
            <?php if($message != "") echo "<p class='message'>$message</p>"; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="description" placeholder="Description" rows="4" required></textarea>
                <input type="number" name="price" step="0.01" placeholder="Price (₱)" required>
                <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.png" required>
                <button type="submit" name="upload">Upload Study Guide</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="portfolio-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="portfolio-item">
                    <h3><?php echo $row['title']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <p><strong>₱<?php echo $row['price']; ?></strong></p>
                    <?php if($row['file_path'] != ""): ?>
                        <a href="<?php echo $row['file_path']; ?>" class="btn" target="_blank">Download</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">No study guides available yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
