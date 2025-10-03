<?php
include("config.php");
session_start();

if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pin = mysqli_real_escape_string($conn, $_POST['pin']);

   // ✅ BU email check
  if (!preg_match("/@bicol-u\.edu\.ph$/", $email)) {
    $error = "❌ Only BU (@bicol-u.edu.ph) emails are allowed.";
  } else {
    $sql = "INSERT INTO users (name, email, password, address, contact, role, pin) 
            VALUES ('$name', '$email', '$password', '$address', '$contact', 'user', '$pin')";
    if (mysqli_query($conn, $sql)) {
      header("Location: login.php?signup=success");
      exit;
    } else {
      $error = "❌ Error: " . mysqli_error($conn);
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Sign Up</title>
  <link rel="stylesheet" href="assets/style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* small local styles so form looks OK if stylesheet missing */
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f7f3ef; }
    .container { max-width:420px; margin:60px auto; background:#fffaf5; padding:24px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.08); }
    input { width:100%; padding:10px; margin:8px 0; border-radius:8px; border:1px solid #d6c8b8; }
    .btn { width:100%; padding:10px; background:#b08968; color:#fff; border:none; border-radius:8px; cursor:pointer; }
    .btn:hover{ background:#a0765b }
    .error{ color:#b00020; text-align:center; margin-top:8px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Create Account</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" novalidate>
      <input type="text" name="name" placeholder="Full name" required>
      <input type="email" name="email" placeholder="BU Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="text" name="address" placeholder="Address (optional)">
      <input type="text" name="contact" placeholder="Contact Number (optional)">
      <input type="number" name="pin" placeholder="4-6 digit PIN" min="0" max="999999" required>
      <button type="submit" name="signup" class="btn">Sign Up</button>
    </form>
    <p style="text-align:center;margin-top:12px;">Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
