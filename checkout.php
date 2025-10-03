<?php
include("config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ensure cart exists and is an array
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='products.php'>Go shopping</a></p>";
    exit;
}

// Normalize cart into an array of integers
$cartIds = is_array($_SESSION['cart']) ? $_SESSION['cart'] : [$_SESSION['cart']];
$cartIds = array_map('intval', $cartIds);

// Fetch products in cart
$cartItems = [];
$total = 0;
if (!empty($cartIds)) {
    $ids = implode(',', $cartIds);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
    while ($r = mysqli_fetch_assoc($res)) {
        $cartItems[] = $r;
        $total += (float)$r['price'];
    }
}

// Helper: get numeric user id from session (handles email/string or saved array)
function getLoggedInUserId($conn)
{
    if (!isset($_SESSION['user'])) return 0;
    // If session contains array with id
    if (is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
        return (int) $_SESSION['user']['id'];
    }
    // If session contains numeric id
    if (is_numeric($_SESSION['user'])) {
        return (int) $_SESSION['user'];
    }
    // Otherwise assume it's an email, try to look up the user and update session
    $email = mysqli_real_escape_string($conn, $_SESSION['user']);
    $q = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
    if ($row = mysqli_fetch_assoc($q)) {
        // store full row for future convenience
        $_SESSION['user'] = $row;
        return (int)$row['id'];
    }
    return 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['payment'] ?? '');
    $accountNumber = mysqli_real_escape_string($conn, $_POST['account_number'] ?? '');
    $enteredPin    = $_POST['pin']; // 👈 customer enters their PIN again

    $userId = getLoggedInUserId($conn);
    if ($userId <= 0) {
        echo "User not found. Please log in again.";
        exit;
    }

    // 🔒 verify the PIN again before confirming payment
    $q = mysqli_query($conn, "SELECT pin FROM users WHERE id=$userId LIMIT 1");
    $row = mysqli_fetch_assoc($q);
    if (!$row || $enteredPin !== $row['pin']) {
        echo "❌ Invalid PIN. Payment not processed.";
        exit;
    }




    if (empty($cartItems)) {
        echo "Your cart is empty.";
        exit;
    }

    // generate order_ref to group multiple rows
    $orderRef = uniqid("ORD");

    // We'll keep track of first inserted order id (for convenience)
    $firstOrderId = null;

    foreach ($cartItems as $item) {
        $productId = (int)$item['id'];
        $price = (float)$item['price'];
        $quantity = 1; // assuming each item in cart is 1 unit; extend if you store qty
        $totalAmount = round($price * $quantity, 2);

        $platformCommission = round($totalAmount * 0.20, 2); // 20%
        $sellerEarnings = round($totalAmount - $platformCommission, 2);

        // Save to seller balance instead of direct payout
        if ($sellerId) {
            mysqli_query($conn, "UPDATE users SET balance = balance + $sellerEarnings WHERE id=$sellerId");
        }

        // prepare values properly (gcash may be NULL)
        $gcash_sql = $accountNumber !== '' ? "'" . mysqli_real_escape_string($conn, $accountNumber) . "'" : "NULL";
        $seller_sql = is_int($sellerId) ? $sellerId : "NULL";

        // make sure paymentMethod string is safe
        $paymentMethodSafe = mysqli_real_escape_string($conn, $paymentMethod);
        $paymentStatus = 'Paid'; // simulated payment success
        $status = 'Completed';

        $insert = "
            INSERT INTO orders 
            (user_id, product_id, status, created_at, quantity, total_amount, payment_method, payment_status, gcash_number, seller_id, platform_commission, seller_earnings, order_ref)
            VALUES
            ($userId, $productId, '" . mysqli_real_escape_string($conn, $status) . "', NOW(), $quantity, $totalAmount, '$paymentMethodSafe', '$paymentStatus', $gcash_sql, $seller_sql, $platformCommission, $sellerEarnings, '" . mysqli_real_escape_string($conn, $orderRef) . "')
        ";
        $ok = mysqli_query($conn, $insert);
        if (!$ok) {
            echo "Database error: " . mysqli_error($conn);
            exit;
        }
        if (!$firstOrderId) $firstOrderId = mysqli_insert_id($conn);
    }

    // save receipt data in session (useful if you want)
    $_SESSION['receipt'] = [
        'order_ref' => $orderRef,
        'payment' => $paymentMethod,
        'account_number' => $accountNumber,
        'total' => $total
    ];

    // clear cart
    $_SESSION['cart'] = [];

    // redirect to receipt using order_ref
    header("Location: receipt.php?order_ref=" . urlencode($orderRef));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta charset="UTF-8">
    <title>Checkout - YOU DO NOTES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (keep your existing styling here) ... */
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f3ef;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #fffaf5;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #5a4b41;
            text-align: center;
            margin-bottom: 30px;
        }

        .catalog {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .product-card {
            background: #fffaf5;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
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
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .payment-method {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fffaf5;
            padding: 10px 15px;
            border-radius: 10px;
            border: 1px solid #c2b9b0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .payment-method input {
            accent-color: #b08968;
        }

        .payment-method label:hover {
            background: #f0ece7;
        }

        .btn {
            background: #b08968;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 1rem;
        }

        .btn:hover {
            background: #a0765b;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            background: #5a4b41;
            color: #fff;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .back-btn:hover {
            background: #b08968;
        }

        .account-input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #c2b9b0;
            margin-bottom: 20px;
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
                <label>
                    <input type="radio" name="payment" value="GCash" required>
                    <i class="fa-solid fa-mobile-screen-button"></i> GCash
                </label>
                <label>
                    <input type="radio" name="payment" value="Maya" required>
                    <i class="fa-solid fa-mobile-screen-button"></i> Maya
                </label>
            </div>
            <input type="password" name="pin" placeholder="Enter your 4-6 digit PIN (from Login)" class="account-input" required pattern="\d{4,6}" maxlength="6" title="Please enter a 4 to 6 digit PIN">
            <input type="text" name="account_number" placeholder="Enter your GCash/Maya number" class="account-input" required>
            <button type="submit" name="checkout" class="btn"><i class="fa-solid fa-credit-card"></i> Confirm Payment</button>
        </form>
    </div>
</body>

</html>