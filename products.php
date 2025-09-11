<?php include("config.php"); session_start(); ?>
<?php
$result = mysqli_query($conn, "SELECT * FROM products");
?>
<h2>Products</h2>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
    <div>
        <h3><?php echo $row['title']; ?></h3>
        <p><?php echo $row['description']; ?></p>
        <p>₱<?php echo $row['price']; ?></p>
        <a href="cart.php?id=<?php echo $row['id']; ?>">Add to Cart</a>
    </div>
<?php } ?>
