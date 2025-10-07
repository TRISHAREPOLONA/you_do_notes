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
   
   // Get seller email - handle both array and string formats
   if (is_array($_SESSION['user'])) {
      $seller_email = $_SESSION['user']['email'];
   } else {
      $seller_email = $_SESSION['user'];
   }

   $file_path = NULL;

   // === Handle upload type (file or link) ===
   if ($_POST['upload_type'] === "file") {
      // Handle file upload
      $note_file = $_FILES['note_file']['name'];
      $file_path = "uploads/notes/" . basename($note_file);

      // Make sure folder exists
      if (!is_dir("uploads/notes/")) {
         mkdir("uploads/notes/", 0777, true);
      }

      // Move the file
      if (!move_uploaded_file($_FILES['note_file']['tmp_name'], $file_path)) {
          echo "Failed to upload file.";
          exit;
      }

   } else {
      // Handle link upload - store link in file_path
      $file_path = mysqli_real_escape_string($conn, $_POST['note_link']);
   }

   // Insert into DB - always use file_path column
   $sql = "INSERT INTO products (title, description, price, file_path, seller_email) 
           VALUES ('$title', '$description', '$price', '$file_path', '$seller_email')";

   // Execute the SQL query
   if (mysqli_query($conn, $sql)) {
      header("Location: seller.php?success=upload");
      exit;
   } else {
      echo "Database error: " . mysqli_error($conn);
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta charset="UTF-8">
   <title>Upload Note - YOU DO NOTES</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
      body {
         font-family: 'Segoe UI', Arial, sans-serif;
         background: #f7f3ef;
         margin: 0;
         padding: 20px;
      }
      .container {
         max-width: 600px;
         margin: 0 auto;
         background: #fff;
         padding: 30px;
         border-radius: 10px;
         box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
      }
      h2 {
         text-align: center;
         color: #444;
         margin-bottom: 20px;
      }
      input, textarea, button {
         width: 100%;
         padding: 10px;
         margin: 8px 0;
         border-radius: 6px;
         border: 1px solid #ddd;
         font-size: 14px;
         box-sizing: border-box;
      }
      textarea {
         height: 80px;
         resize: none;
      }
      button {
         background: #c8a97e;
         color: white;
         border: none;
         cursor: pointer;
         font-weight: bold;
      }
      button:hover {
         background: #b08968;
      }
      .radio-group {
         margin: 15px 0;
      }
      .radio-option {
         margin: 10px 0;
         display: flex;
         align-items: center;
      }
      .radio-option input[type="radio"] {
         width: auto;
         margin-right: 8px;
      }
      .back-btn {
         display: inline-block;
         margin-bottom: 20px;
         color: #c8a97e;
         text-decoration: none;
      }
   </style>
</head>

<body>
   <div class="container">
      <a href="seller.php" class="back-btn">
         <i class="fas fa-arrow-left"></i> Back to Seller Dashboard
      </a>
      <h2>Upload New Note</h2>
      
      <form method="POST" enctype="multipart/form-data">
         <input type="text" name="title" placeholder="Note Title" required>
         <textarea name="description" placeholder="Description" required></textarea>
         <input type="number" name="price" placeholder="Price" step="0.01" required>

         <!-- Upload Type Choice -->
         <div class="radio-group">
            <label><strong>Choose Upload Type:</strong></label>
            <div class="radio-option">
               <input type="radio" id="file_type" name="upload_type" value="file" checked>
               <label for="file_type">Upload Note File</label>
            </div>
            <div class="radio-option">
               <input type="radio" id="link_type" name="upload_type" value="link">
               <label for="link_type">Provide a Link</label>
            </div>
         </div>

         <!-- File Upload -->
         <div id="file-upload">
            <label>Upload Note File (PDF/DOCX/PPTX):</label>
            <input type="file" name="note_file" accept=".pdf,.docx,.pptx" required>
         </div>

         <!-- Link Upload -->
         <div id="link-upload" style="display:none;">
            <label>Provide Note Link (Google Drive/OneDrive):</label>
            <input type="url" name="note_link" placeholder="https://...">
         </div>

         <button type="submit" name="upload">Upload Note</button>
      </form>
   </div>

   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const fileUpload = document.getElementById("file-upload");
         const linkUpload = document.getElementById("link-upload");
         const fileRadio = document.getElementById("file_type");
         const linkRadio = document.getElementById("link_type");
         const noteFileInput = document.querySelector('input[name="note_file"]');
         const noteLinkInput = document.querySelector('input[name="note_link"]');
         
         function toggleUploadFields() {
            if (fileRadio.checked) {
               fileUpload.style.display = "block";
               linkUpload.style.display = "none";
               noteFileInput.required = true;
               noteLinkInput.required = false;
               noteLinkInput.value = '';
            } else {
               fileUpload.style.display = "none";
               linkUpload.style.display = "block";
               noteFileInput.required = false;
               noteLinkInput.required = true;
               noteFileInput.value = '';
            }
         }
         
         fileRadio.addEventListener("change", toggleUploadFields);
         linkRadio.addEventListener("change", toggleUploadFields);
         toggleUploadFields();
      });
   </script>
</body>
</html>