<?php
$host = "localhost";
$user = "root";  // default in XAMPP
$pass = "";
$db   = "you_do_notes_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
