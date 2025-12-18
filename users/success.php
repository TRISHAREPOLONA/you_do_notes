<?php session_start(); ?>
<h2>Payment Successful 🎉</h2>
<p>Thank you for your purchase.</p>
<a href="downloads.php">Go to My Downloads</a> | <a href="products.php">Back to Shop</a>
<?php $_SESSION['cart'] = []; ?>
