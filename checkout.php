<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// If cart is empty, redirect back
if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='products.php'>Go shopping</a></p>";
    exit;
}

// Fetch products in cart
$cartItems = [];
$total = 0;
$ids = implode(',', $_SESSION['cart']);
$result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
while ($row = mysqli_fetch_assoc($result)) {
    $cartItems[] = $row;
    $total += $row['price'];
}

// Handle checkout form submission
if (isset($_POST['checkout'])) {
    $paymentMethod = $_POST['payment'];
    // Here you can add database insertion for orders if needed
    // For now, we just clear the cart and show confirmation
    $_SESSION['cart'] = [];
    echo "<script>alert('Payment successful via $paymentMethod!'); window.location='products.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - YOU DO NOTES</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f7f3ef; }
.container { max-width: 900px; margin: 50px auto; padding: 20px; background: #fffaf5; border-radius: 15px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
h2 { color: #5a4b41; text-align: center; margin-bottom: 30px; }
.catalog { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
.product-card { background: #fffaf5; padding: 20px; border-radius: 15px; box-shadow: 0px 4px 8px rgba(0,0,0,0.1); text-align: center; display: flex; flex-direction: column; justify-content: space-between; min-height: 200px; }
.product-card h3 { margin: 10px 0; color: #5a4b41; }
.product-card p { color: #6d5d52; }
.total { text-align: right; font-weight: bold; font-size: 1.2rem; margin-bottom: 20px; }
.payment-method { display: flex; gap: 20px; margin-bottom: 20px; }
.payment-method label { display: flex; align-items: center; gap: 10px; background: #fffaf5; padding: 10px 15px; border-radius: 10px; border: 1px solid #c2b9b0; cursor: pointer; transition: background 0.2s; }
.payment-method input { accent-color: #b08968; }
.payment-method label:hover { background: #f0ece7; }
.btn { background: #b08968; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; border: none; cursor: pointer; transition: background 0.2s; font-size: 1rem; }
.btn:hover { background: #a0765b; }
.back-btn { display: inline-block; margin-bottom: 20px; background: #5a4b41; color: #fff; padding: 8px 15px; border-radius: 8px; text-decoration: none; transition: background 0.2s; }
.back-btn:hover { background: #b08968; }
</style>
</head>
<body>
<div class="container">
    <h2>Checkout</h2>
    <a href="cart.php" class="back-btn">&larr; Back to Cart</a>

    <div class="catalog">
        <?php foreach ($cartItems as $item) { ?>
        <div class="product-card">
            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
            <p><?php echo htmlspecialchars($item['description']); ?></p>
            <p><strong>₱<?php echo number_format($item['price'], 2); ?></strong></p>
        </div>
        <?php } ?>
    </div>

    <div class="total">Total: ₱<?php echo number_format($total, 2); ?></div>

    <form method="POST">
        <div class="payment-method">
            <label>
                <input type="radio" name="payment" value="GCash" required>
                <i class="fa-solid fa-mobile-screen-button"></i> GCash
            </label>
            <label>
                <input type="radio" name="payment" value="Maya" required>
                <i class="fa-solid fa-mobile-screen-button"></i> Maya
            </label>
        </div>
        <button type="submit" name="checkout" class="btn"><i class="fa-solid fa-credit-card"></i> Confirm Payment</button>
    </form>
</div>
</body>
</html>
