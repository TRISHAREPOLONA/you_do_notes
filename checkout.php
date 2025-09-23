<?php
include("config.php");
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='products.php'>Go shopping</a></p>";
    exit;
}

$cartItems = [];
$total = 0;
$ids = implode(',', $_SESSION['cart']);
$result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
while ($row = mysqli_fetch_assoc($result)) {
    $cartItems[] = $row;
    $total += $row['price'];
}

// Handle checkout
if (isset($_POST['checkout'])) {
    $paymentMethod = $_POST['payment'];
    $accountNumber = $_POST['account_number'];

    foreach ($cartItems as $item) {
        $productId = $item['id'];
        $sellerEmail = $item['seller_email']; // must exist in products table
        $price = $item['price'];

        // Calculate commission
        $platformCommission = $price * 0.20;
        $sellerEarnings = $price * 0.80;

        // Save order in DB
        $userId = $_SESSION['user']['id'];
        $query = "INSERT INTO orders (user_id, product_id, seller_email, payment_method, account_number, total_price, seller_earnings, platform_fee, created_at) 
                VALUES ('$userId', '$productId', '$sellerEmail', '$paymentMethod', '$accountNumber', '$price', '$sellerEarnings', '$platformCommission', NOW())";
        mysqli_query($conn, $query);
    }

    $_SESSION['cart'] = []; // clear cart
    echo "<script>alert('Payment successful via $paymentMethod! Commission applied.'); window.location='products.php';</script>";
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
        /* === Checkout Page Styling === */
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f3ef;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: #fffaf5;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #5a4b41;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        .catalog {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .product-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            min-height: 180px;
        }

        .product-card h3 {
            margin: 10px 0;
            color: #5a4b41;
        }

        .product-card p {
            color: #6d5d52;
        }

        .total {
            text-align: right;
            font-weight: bold;
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #5a4b41;
        }

        /* === Payment Method Cards === */
        .payment-method {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .payment-card {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #fdfaf7;
            padding: 15px 20px;
            border-radius: 12px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            font-weight: 500;
            font-size: 1.1rem;
            color: #5a4b41;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.08);
        }

        .payment-card:hover {
            background: #f0ece7;
            transform: scale(1.03);
        }

        /* highlight selected */
        .payment-method input:checked+.payment-card {
            border-color: #b08968;
            background: #fff3e6;
            color: #a0522d;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* === Buttons === */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            background: linear-gradient(135deg, #ff6b6b, #c92a2a);
            color: #fff;
            padding: 12px 28px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            width: 100%;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            background: linear-gradient(135deg, #c92a2a, #ff6b6b);
            transform: scale(1.05);
        }
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
                <input type="radio" id="gcash" name="payment" value="GCash" required hidden>
                <label for="gcash" class="payment-card">
                    <i class="fa-solid fa-mobile-screen-button"></i> GCash
                </label>

                <input type="radio" id="maya" name="payment" value="Maya" required hidden>
                <label for="maya" class="payment-card">
                    <i class="fa-solid fa-mobile-screen-button"></i> Maya
                </label>
            </div>

            <!-- Customer’s GCash or Maya Number -->
            <div style="margin-bottom: 20px; text-align:center;">
                <label for="account_number"><b>Enter your GCash/Maya Number:</b></label><br>
                <input type="text" id="account_number" name="account_number" required
                    placeholder="09xxxxxxxxx" style="padding:10px; width:250px; border-radius:8px; border:1px solid #ccc; margin-top:8px;">
            </div>

            <button type="submit" name="checkout" class="btn">
                <i class="fa-solid fa-credit-card"></i> Confirm Payment
            </button>
        </form>

    </div>
</body>

</html>