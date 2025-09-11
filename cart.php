<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (!in_array($id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $id;
    }
    header("Location: cart.php");
    exit;
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    $_SESSION['cart'] = array_filter($_SESSION['cart'], fn($id) => $id !== $removeId);
    header("Location: cart.php");
    exit;
}

// Fetch products in cart
$cartItems = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', $_SESSION['cart']);
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
    while ($row = mysqli_fetch_assoc($result)) {
        $cartItems[] = $row;
        $total += $row['price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Cart - YOU DO NOTES</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f7f3ef;
}
.container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
}
h2 { color: #5a4b41; text-align: center; margin-bottom: 30px; }
.catalog {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}
.product-card {
    background: #fffaf5;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 220px;
}
.product-card h3 { margin: 10px 0; color: #5a4b41; }
.product-card p { color: #6d5d52; }
.btn {
    background: #b08968;
    color: #fff;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 10px;
    display: inline-block;
}
.btn:hover { background: #a0765b; }
.total {
    text-align: right;
    margin-top: 20px;
    font-weight: bold;
    font-size: 1.2rem;
}
.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    background: #5a4b41;
    color: #fff;
    padding: 8px 15px;
    border-radius: 8px;
    text-decoration: none;
}
.back-btn:hover { background: #b08968; }
</style>
</head>
<body>
<div class="container">
    <h2>My Cart</h2>
    <a href="products.php" class="back-btn">&larr; Continue Shopping</a>

    <?php if (!empty($cartItems)) { ?>
        <div class="catalog">
            <?php foreach ($cartItems as $item) { ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p><strong>₱<?php echo number_format($item['price'], 2); ?></strong></p>
                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn">Remove</a>
                </div>
            <?php } ?>
        </div>
        <div class="total">Total: ₱<?php echo number_format($total, 2); ?></div>
        <a href="checkout.php" class="btn">Proceed to Checkout</a>
    <?php } else { ?>
        <p>Your cart is empty.</p>
    <?php } ?>
</div>
</body>
</html>
