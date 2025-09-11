<?php include("config.php"); session_start(); ?>
<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $row = mysqli_fetch_assoc($result);

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user'] = $row['id'];
        header("Location: products.php");
    } else {
        echo "Invalid email or password";
    }
}
?>
<form method="post">
    Email: <input type="email" name="email"><br>
    Password: <input type="password" name="password"><br>
    <button type="submit" name="login">Login</button>
</form>
