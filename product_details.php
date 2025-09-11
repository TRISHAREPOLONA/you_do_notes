<?php include("config.php"); session_start(); ?>
<?php
$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$product = mysqli_fetch_assoc($result);
?>
<h2><?php echo $product['title']; ?></h2>
<p><?php echo $product['description']; ?></p>
<p>₱<?php echo $product['price']; ?></p>
<a href="cart.php?id=<?php echo $product['id']; ?>">Add to Cart</a>
