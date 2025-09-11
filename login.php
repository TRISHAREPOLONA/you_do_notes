<?php
include("config.php");
session_start();

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Fetch user from DB
    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($user = mysqli_fetch_assoc($result)) {
        // Check if password is hashed or plain text
        if (
            (isset($user['password']) && password_verify($password, $user['password'])) // hashed
            || $user['password'] === $password // plain text
        ) {
            // Store email in session
            $_SESSION['user'] = $user['email'];

            // Redirect to products page
            header("Location: products.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - YOU DO NOTES</title>
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; background: #f7f3ef; }
.container { max-width: 400px; margin: 100px auto; background: #fffaf5; padding: 30px; border-radius: 15px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 8px; border: 1px solid #c2b9b0; }
.btn { width: 100%; padding: 10px; background: #b08968; color: #fff; border: none; border-radius: 8px; cursor: pointer; }
.btn:hover { background: #a0765b; }
.error { color: red; margin-top: 10px; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" class="btn">Login</button>
    </form>
    <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
</div>
</body>
</html>
