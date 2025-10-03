<?php
include("config.php");
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
// Check if user is logged in
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    echo "<p>You are not logged in. Please <a href='login.php'>login</a> first.</p>";
    exit;
}

$userEmail = $_SESSION['user']['email'];

// Fetch user info
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$userEmail' LIMIT 1");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    echo "<p>User not found in database. Please <a href='login.php'>login again</a>.</p>";
    exit;
}

// Handle form submission for updates
$updateMessage = "";
if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    // Only update if values have changed
    if ($name !== $user['name'] || $address !== $user['address'] || $contact !== $user['contact']) {
        $updateQuery = "UPDATE users SET name='$name', address='$address', contact='$contact' WHERE email='$userEmail'";
        if (mysqli_query($conn, $updateQuery)) {
            $updateMessage = "Information updated successfully.";
            $user['name'] = $name;
            $user['address'] = $address;
            $user['contact'] = $contact;
        } else {
            $updateMessage = "Error updating information.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta charset="UTF-8">
<title>My Profile - YOU DO NOTES</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f7f3ef; }
.container { max-width: 600px; margin: 50px auto; background: #fffaf5; padding: 30px; border-radius: 15px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
h2 { color: #5a4b41; text-align: center; margin-bottom: 20px; }
form { display: flex; flex-direction: column; gap: 15px; position: relative; }
label { font-weight: bold; color: #5a4b41; }
input[readonly], textarea[readonly] { background: #f0ece7; }
input, textarea { padding: 10px; border-radius: 8px; border: 1px solid #c2b9b0; font-size: 1rem; width: 100%; }
.edit-btn { 
    background: #5a4b41; 
    color: #fff; 
    border: none; 
    border-radius: 50%; 
    padding: 10px; 
    cursor: pointer; 
    position: absolute; 
    top: -10px; 
    right: -10px; 
    font-size: 1rem; 
    transition: background 0.2s;
}
.edit-btn:hover { background: #b08968; }
.update-btn { 
    background: #b08968; 
    color: #fff; 
    border: none; 
    border-radius: 8px; 
    padding: 10px; 
    cursor: pointer; 
    font-size: 1rem; 
    display: none; /* hidden by default */
}
.update-btn:hover { background: #a0765b; }
.logout, .back-btn { display: block; margin-top: 20px; text-align: center; text-decoration: none; color: #fff; padding: 10px; border-radius: 8px; transition: background 0.2s; }
.logout { background: #d9534f; }
.logout:hover { background: #c9302c; }
.back-btn { background: #5a4b41; }
.back-btn:hover { background: #b08968; }
.message { text-align: center; color: green; margin-top: 10px; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
    <h2>My Profile</h2>

    <form method="POST" action="" id="profileForm">
        <label>Full Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>

        <label>Address</label>
        <textarea name="address" rows="3" readonly><?php echo htmlspecialchars($user['address']); ?></textarea>

        <label>Contact Number</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" readonly>

        <!-- Edit Icon -->
        <button type="button" class="edit-btn" id="editBtn" title="Edit Info">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>

        <!-- Update Button -->
        <button type="submit" name="update" class="update-btn" id="updateBtn">Save Changes</button>
    </form>

    <?php if(!empty($updateMessage)) { echo "<div class='message'>$updateMessage</div>"; } ?>

    <a href="products.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Homepage</a>
    <a href="logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<script>
// Enable edit mode when edit icon is clicked
const editBtn = document.getElementById('editBtn');
const updateBtn = document.getElementById('updateBtn');
const inputs = document.querySelectorAll('input[name="name"], textarea[name="address"], input[name="contact"]');

editBtn.addEventListener('click', () => {
    inputs.forEach(input => input.removeAttribute('readonly'));
    updateBtn.style.display = 'block';
    editBtn.style.display = 'none';
});
</script>
</body>
</html>
