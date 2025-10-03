<?php
include("config.php");
session_start();

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $pin = $_POST['pin'];

    // Fetch user from DB
    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($user = mysqli_fetch_assoc($result)) {
        if (
            password_verify($password, $user['password']) &&
            $user['pin'] === $pin
        ) {
            $_SESSION['user'] = $user['email'];
            header("Location: products.php");
            exit;
        } else {
            $error = "Incorrect password or PIN.";
        }
    } else {
        $error = "User not found.";
    }
}
?>




<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>
</head>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f7f3ef;
    }

    .container {
        max-width: 400px;
        margin: 100px auto;
        background: #fffaf5;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    input {
        width: 95%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 8px;
        border: 1px solid #c2b9b0;
    }

    .btn {
        width: 100%;
        padding: 10px;
        background: #b08968;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .btn:hover {
        background: #a0765b;
    }

    .error {
        color: red;
        margin-top: 10px;
        text-align: center;
    }
</style>

<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="BU Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="text" name="pin" pattern="\d{4,6}" maxlength="6" required>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    </div>
</body>

</html>