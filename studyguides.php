<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Notifications placeholder
$notification_count = 0;

// Fetch study guides
$query = "SELECT * FROM study_guides ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Study Guides - YOU DO NOTES</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f7f3ef;
}

/* Navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    background-color: #fffaf5;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}
.navbar-left h2 {
    margin: 0;
    color: #5a4b41;
    font-size: 2rem;
    letter-spacing: 1px;
}
.navbar-center {
    flex: 1;
    text-align: center;
}
.navbar-center input {
    width: 300px;
    max-width: 80%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #c2b9b0;
    font-size: 1rem;
}
.navbar-right {
    display: flex;
    align-items: center;
    gap: 35px;
}
.navbar-right a {
    color: #5a4b41;
    text-decoration: none;
    font-weight: bold;
    font-size: 1rem;
    position: relative;
    transition: color 0.2s;
}
.navbar-right a:hover {
    color: #b08968;
}
.navbar-right i {
    font-size: 1.4rem;
}

/* Notification badge */
.notification {
    position: relative;
}
.notification .badge {
    position: absolute;
    top: -8px;
    right: -10px;
    padding: 4px 7px;
    border-radius: 50%;
    background: red;
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
}

/* Study Guides Grid */
.container {
    width: 90%;
    max-width: 1200px;
    margin: 30px auto;
}
.catalog {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}
.product-card {
    background: #fffaf5;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 220px;
}
.product-card h3 {
    margin: 10px 0;
    color: #5a4b41;
}
.product-card p {
    color: #6d5d52;
}
.btn {
    background: #b08968;
    color: #fff;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 10px;
    transition: background 0.2s;
}
.btn:hover {
    background: #a0765b;
}

/* Responsive */
@media (max-width: 900px) {
    .navbar {
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
    }
    .navbar-center input {
        width: 90%;
        max-width: 300px;
    }
    .navbar-right {
        gap: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-left">
        <h2>YOU DO NOTES</h2>
    </div>
    <div class="navbar-center">
        <form method="GET" action="studyguides.php">
            <input type="text" name="search" placeholder="Search study guides...">
        </form>
    </div>
    <div class="navbar-right">
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="profile.php"><i class="fa-solid fa-user"></i></a>
    </div>
</div>

<div class="container">
    <h1 style="text-align:center; color:#5a4b41;">Study Guides</h1>
    <div class="catalog">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
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
