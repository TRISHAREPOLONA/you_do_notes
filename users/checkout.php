<?php
include("../config.php");
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

    // Validate account number (11 digits only)
    if (!preg_match('/^[0-9]{11}$/', $accountNumber)) {
        echo "❌ Please enter a valid 11-digit account number.";
        exit;
    }

    // Validate account number starts with 09
    if (!str_starts_with($accountNumber, '09')) {
        echo "❌ Account number should start with 09.";
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
        .checkout-page {
            background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin: 30px 0;
            align-items: start;
        }

        .order-items {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .order-summary {
            background: linear-gradient(135deg, #b08968 0%, #a0765b 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 30px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background: #fafafa;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f0e6d6 0%, #e8d9c5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: #b08968;
        }

        .item-info {
            flex: 1;
        }

        .item-info h4 {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .item-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .item-price {
            color: #b08968;
            font-size: 1.2rem;
            font-weight: 700;
            margin-left: 15px;
        }

        .payment-section {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .payment-option {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: #b08968;
            transform: translateY(-2px);
        }

        .payment-option.selected {
            border-color: #b08968;
            background: #fffaf5;
        }

        .payment-option i {
            font-size: 2rem;
            color: #b08968;
            margin-bottom: 10px;
        }

        .payment-option span {
            display: block;
            color: #333;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-input:focus {
            outline: none;
            border-color: #b08968;
            box-shadow: 0 0 0 3px rgba(176, 137, 104, 0.1);
        }

        .input-hint {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }

        .summary-items {
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 1rem;
        }

        .summary-total {
            font-size: 1.4rem;
            font-weight: 700;
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 15px;
            margin-top: 15px;
        }

        .checkout-btn {
            width: 100%;
            background: white;
            color: #b08968;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .error-message {
            background: #ffe6e6;
            color: #d63031;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ffcccc;
            font-size: 0.9rem;
        }

        .security-notice {
            background: #e8f4fd;
            color: #0c5460;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid #b8daff;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkout-navigation {
            margin-bottom: 30px;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .checkout-header h2 {
            color: #5a4b41;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .checkout-header p {
            color: #8d7b68;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .order-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="checkout-page">
    <div class="container">
        <div class="checkout-navigation">
            <a href="cart.php" class="btn" style="background: #5a4b41; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>
        </div>

        <div class="checkout-header">
            <h2><i class="fas fa-credit-card"></i> Checkout</h2>
            <p>Complete your purchase securely</p>
        </div>

        <div class="checkout-container">
            <div>
                <div class="order-items">
                    <h3 class="section-title"><i class="fas fa-shopping-cart"></i> Order Items</h3>
                    <?php foreach ($cartItems as $item) { ?>
                        <div class="order-item">
                            <div class="item-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                            </div>
                            <div class="item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <div class="payment-section">
                    <h3 class="section-title"><i class="fas fa-lock"></i> Payment Details</h3>
                    
                    <form method="POST" onsubmit="return validateCheckoutForm()">
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <div class="payment-methods">
                                <div class="payment-option" onclick="selectPayment('GCash')">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>GCash</span>
                                    <input type="radio" name="payment" value="GCash" required style="display: none;">
                                </div>
                                <div class="payment-option" onclick="selectPayment('Maya')">
                                    <i class="fas fa-wallet"></i>
                                    <span>Maya</span>
                                    <input type="radio" name="payment" value="Maya" required style="display: none;">
                                </div>
                            </div>
                            <div id="payment-error" class="error-message" style="display: none;">
                                Please select a payment method
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pin">Security PIN</label>
                            <input type="password" name="pin" id="pin" class="form-input" placeholder="Enter your 4-6 digit PIN" pattern="[0-9]{4,6}" maxlength="6" required>
                            <div class="input-hint">Enter the same PIN you use for login</div>
                            <div id="pin-error" class="error-message" style="display: none;">
                                Please enter a valid 4-6 digit PIN
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="account_number">Account Number</label>
                            <input type="text" name="account_number" id="account_number" class="form-input" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" maxlength="11" required>
                            <div class="input-hint">Must be exactly 11 digits starting with 09</div>
                            <div id="account-error" class="error-message" style="display: none;">
                                Please enter a valid 11-digit account number starting with 09
                            </div>
                        </div>

                        <div class="security-notice">
                            <i class="fas fa-shield-alt"></i>
                            <span>Your payment information is secure and encrypted</span>
                        </div>

                        <button type="submit" name="checkout" class="checkout-btn">
                            <i class="fas fa-lock"></i> Complete Purchase - ₱<?php echo number_format($total, 2); ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="order-summary">
                <h3 class="summary-title">Order Summary</h3>
                <div class="summary-items">
                    <div class="summary-row">
                        <span>Items:</span>
                        <span><?php echo count($cartItems); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Platform Fee:</span>
                        <span>Free</span>
                    </div>
                </div>
                <div class="summary-row summary-total">
                    <span>Total Amount:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                

    <script>
        function selectPayment(method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Set the radio button value
            document.querySelector(`input[value="${method}"]`).checked = true;
            
            // Hide payment error
            document.getElementById('payment-error').style.display = 'none';
        }

        function validateCheckoutForm() {
            let isValid = true;
            
            // Validate payment method
            const paymentSelected = document.querySelector('input[name="payment"]:checked');
            const paymentError = document.getElementById('payment-error');
            if (!paymentSelected) {
                paymentError.style.display = 'block';
                isValid = false;
            } else {
                paymentError.style.display = 'none';
            }
            
            // Validate PIN
            const pin = document.getElementById('pin').value;
            const pinError = document.getElementById('pin-error');
            const pinRegex = /^[0-9]{4,6}$/;
            if (!pinRegex.test(pin)) {
                pinError.style.display = 'block';
                isValid = false;
            } else {
                pinError.style.display = 'none';
            }
            
            // Validate account number
            const accountNumber = document.getElementById('account_number').value;
            const accountError = document.getElementById('account-error');
            const accountRegex = /^09[0-9]{9}$/;
            if (!accountRegex.test(accountNumber)) {
                accountError.style.display = 'block';
                isValid = false;
            } else {
                accountError.style.display = 'none';
            }
            
            if (!isValid) {
                const firstError = document.querySelector('.error-message[style="display: block;"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            
            return true;
        }

        // Real-time validation
        document.addEventListener('DOMContentLoaded', function() {
            // PIN validation
            document.getElementById('pin').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
                const pinRegex = /^[0-9]{4,6}$/;
                if (pinRegex.test(this.value)) {
                    document.getElementById('pin-error').style.display = 'none';
                }
            });
            
            // Account number validation
            document.getElementById('account_number').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 11) {
                    this.value = this.value.slice(0, 11);
                }
                const accountRegex = /^09[0-9]{9}$/;
                if (accountRegex.test(this.value)) {
                    document.getElementById('account-error').style.display = 'none';
                }
            });

            // Prevent paste of non-numeric characters
            document.getElementById('pin').addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numbersOnly = pastedText.replace(/[^0-9]/g, '');
                this.value = numbersOnly.slice(0, 6);
            });

            document.getElementById('account_number').addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numbersOnly = pastedText.replace(/[^0-9]/g, '');
                this.value = numbersOnly.slice(0, 11);
            });
        });
    </script>
</body>
</html>