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
   $seller_email = $_SESSION['user']; // seller identity

   // === Handle image upload ===
   $image = $_FILES['image']['name'];
   $target_image = "images/" . basename($image);

   // === Handle note file upload (PDF/DOCX/etc.) ===
   $note_file = $_FILES['note_file']['name'];
   $target_file = "uploads/notes/" . basename($note_file);

   // Make sure folder exists
   if (!is_dir("uploads/notes/")) {
      mkdir("uploads/notes/", 0777, true);
   }

   if (
      move_uploaded_file($_FILES['image']['tmp_name'], $target_image) &&
      move_uploaded_file($_FILES['note_file']['tmp_name'], $target_file)
   ) {

      // Insert into DB
      $sql = "INSERT INTO products (title, description, price, image, file_path, seller_email) 
            VALUES ('$title', '$description', '$price', '$image', '$target_file', '$seller_email')";

      if (mysqli_query($conn, $sql)) {
         header("Location: seller.php?success=1");
         exit;
      } else {
         echo "Database error: " . mysqli_error($conn);
      }
   } else {
      echo "Failed to upload files.";
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <meta charset="UTF-8">
   <title>Upload Note</title>
   <link rel="stylesheet" href="assets/style.css">
</head>

<body>
   <div class="container">
      <h2>Upload New Note</h2>
      <form method="POST" enctype="multipart/form-data">
         <input type="text" name="title" placeholder="Note Title" required><br>
         <textarea name="description" placeholder="Description" required></textarea><br>
         <input type="number" name="price" placeholder="Price" required><br>
         <label>Cover Image:</label><br>
         <input type="file" name="image" accept="image/*" required><br><br>
         <label>Note File (PDF/DOCX):</label><br>
         <input type="file" name="note_file" accept=".pdf,.docx,.pptx" required><br><br>
         <button type="submit" name="upload" class="btn">Upload Note</button>
      </form>
   </div>
</body>

</html>