<?php
include("config.php");
session_start();

// Only allow admins
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

if (isset($_POST['upload'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];

    $file_path = "";
    if (!empty($_FILES['file']['name'])) {
        $target_dir = "study_guides/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_path = $target_dir . basename($_FILES["file"]["name"]);
        move_uploaded_file($_FILES["file"]["tmp_name"], $file_path);
    }

    $sql = "INSERT INTO study_guides (title, description, price, file_path) 
            VALUES ('$title', '$description', '$price', '$file_path')";

    if (mysqli_query($conn, $sql)) {
        $message = "✅ Study guide uploaded successfully!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Upload Study Guide</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body { background: #f7f3ef; font-family: 'Segoe UI', Arial, sans-serif; margin:0; }
.navbar { display: flex; justify-content: space-between; padding: 15px 40px; background: #fffaf5; box-shadow: 0 4px 10px rgba(0,0,0,0.1);}
.navbar a { margin-left: 20px; text-decoration: none; color: #5a4b41; font-weight: bold; }
.navbar a:hover { color: #b08968; }

.container { max-width: 700px; margin: 40px auto; background:#fff; padding:30px; border-radius:15px; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#5a4b41; }
form { display:flex; flex-direction:column; gap:15px; }
input, textarea { padding:10px; border:1px solid #ccc; border-radius:10px; width:100%; }
button { background:#5a4b41; color:white; border:none; padding:12px; border-radius:10px; cursor:pointer; font-weight:bold; }
button:hover { background:#b08968; }
.message { text-align:center; margin-bottom:15px; color:green; }
</style>
</head>
<body>
<div class="navbar">
  <div><a href="index.php">YOU DO NOTES</a></div>
  <div>
    <a href="admin_studyguide_upload.php">Upload</a>
    <a href="studyguides.php">View Study Guides</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container">
<h2>Upload New Study Guide</h2>
<?php if ($message != "") echo "<p class='message'>$message</p>"; ?>
<form method="post" enctype="multipart/form-data">
  <input type="text" name="title" placeholder="Title" required>
  <textarea name="description" placeholder="Description" rows="4" required></textarea>
  <input type="number" name="price" step="0.01" placeholder="Price (₱)" required>
  <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.png" required>
  <button type="submit" name="upload">Upload</button>
</form>
</div>
</body>
</html>
