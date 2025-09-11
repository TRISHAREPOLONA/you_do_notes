<?php include("config.php"); ?>
<?php
if (isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name,email,password) VALUES ('$name','$email','$password')";
    if (mysqli_query($conn, $sql)) {
        echo "Signup successful! <a href='login.php'>Login now</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<form method="post">
    Name: <input type="text" name="name"><br>
    BU Email: <input type="email" name="email"><br>
    Password: <input type="password" name="password"><br>
    <button type="submit" name="signup">Sign Up</button>
</form>
