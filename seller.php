<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

$email = $_SESSION['user']; // only email is stored
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <title>Seller Dashboard - YOU DO NOTES</title>
   <link rel="stylesheet" href="assets/style.css">
</head>

<body>
   <div class="container">
      <h2>Seller Dashboard</h2>
      <p>Welcome, <strong><?php echo $email; ?></strong></p>

      <!-- Upload Form -->
      <h3>Upload New Note</h3>
      <form method="POST" enctype="multipart/form-data" action="upload_note.php">
         <input type="text" name="title" placeholder="Note Title" required><br>
         <textarea name="description" placeholder="Description" required></textarea><br>
         <input type="number" name="price" placeholder="Price" required><br>
         <input type="file" name="image" accept="image/*" required><br>
         <input type="url" name="note_link" placeholder="https://..."><br><br>
         <button type="submit" name="upload" class="btn">Upload Note</button>
      </form>

      <hr>

      <!-- Seller's Notes -->
      <h3>Your Notes</h3>
      <div class="notes-grid">
         <?php
         // Show notes uploaded by this seller (based on email)
         $query = "SELECT * FROM products WHERE seller_email = '$email'";
         $result = mysqli_query($conn, $query);

         if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) { ?>
               <div class="note-card">
                  <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
                  <h3><?php echo $row['title']; ?></h3>
                  <p><?php echo $row['description']; ?></p>
                  <p><strong>₱<?php echo $row['price']; ?></strong></p>
                  <a href="edit_note.php?id=<?php echo $row['id']; ?>" class="btn">Edit</a>
                  <a href="delete_note.php?id=<?php echo $row['id']; ?>" class="btn">Delete</a>
               </div>
         <?php }
         } else {
            echo "<p>No notes uploaded yet.</p>";
         }
         ?>
      </div>
   </div>
</body>
</html>
