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
h2 { 
    color: #5a4b41; 
    text-align: center; 
    margin-bottom: 30px; 
    font-size: 2rem;
}
.catalog {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}
.product-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0px 6px 12px rgba(0,0,0,0.1);
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 10px 20px rgba(0,0,0,0.15);
}
.product-card h3 { 
    margin: 10px 0; 
    color: #333; 
    font-size: 1.2rem;
}
.product-card p { 
    color: #555; 
    margin: 5px 0;
}
.btn {
    background: #b08968;
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 12px;
    display: inline-block;
    font-size: 0.9rem;
    transition: background 0.2s;
}
.btn:hover { background: #a0765b; }
.total {
    text-align: right;
    margin-top: 30px;
    font-weight: bold;
    font-size: 1.4rem;
    color: #5a4b41;
}
.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    background: #5a4b41;
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
}
.back-btn:hover { background: #b08968; }

/* Cart badge in navbar */
.cart-link {
    position: relative;
    margin-left: 20px;
    color: #5a4b41;
    text-decoration: none;
}
.cart-link .cart-badge {
    position: absolute;
    top: -8px;
    right: -12px;
    background: #d66a5e;
    color: #fff;
    font-size: 0.8rem;
    font-weight: bold;
    padding: 4px 7px;
    border-radius: 50%;
    box-shadow: 0px 2px 6px rgba(0,0,0,0.2);
}
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
