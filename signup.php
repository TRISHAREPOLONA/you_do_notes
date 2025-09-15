<?php
include("config.php");
session_start();

if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$password','user')";
    if (mysqli_query($conn, $sql)) {
        // Redirect to login after successful signup
        header("Location: login.php?signup=success");
        exit;
    } else {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Sign Up</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <h2>Create Account</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
      <input type="text" name="name" placeholder="Name" required><br>
      <input type="email" name="email" placeholder="BU Email" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <button type="submit" name="signup">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
