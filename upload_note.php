<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

if (isset($_POST['upload'])) {
   $title = mysqli_real_escape_string($conn, $_POST['title']);
   $description = mysqli_real_escape_string($conn, $_POST['description']);
   $price = mysqli_real_escape_string($conn, $_POST['price']);
   $note_link = mysqli_real_escape_string($conn, $_POST['note_link']);
   $seller_email = $_SESSION['user']; // seller identity

   // Handle image upload
   $image = $_FILES['image']['name'];
   $target = "images/" . basename($image);

   if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      $sql = "INSERT INTO products (title, description, price, image, seller_email) 
               VALUES ('$title', '$description', '$price', '$image', '$seller_email')";
      if (mysqli_query($conn, $sql)) {
         header("Location: seller.php?success=1");
         exit;
      } else {
         echo "Database error: " . mysqli_error($conn);
      }
   } else {
      echo "Failed to upload image.";
   }
}
