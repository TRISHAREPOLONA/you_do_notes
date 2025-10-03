<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - YOU DO NOTES</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f3ef; padding: 20px; }
        .container { background: #fff; padding: 20px; border-radius: 10px; }
        h1 { color: #5a4b41; }
        a { display: block; margin: 10px 0; text-decoration: none; color: #b08968; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, Admin!</h1>
        <p>Manage your site here:</p>
        <a href="upload_product.php">➕ Upload Study Notes</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</body>
</html>
