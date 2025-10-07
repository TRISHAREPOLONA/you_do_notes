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

// Initialize discount variables
$discount_code = '';
$discount_amount = 0;
$final_total = $total;
$discount_success = '';
$discount_error = '';

// Apply discount if submitted
if (isset($_POST['apply_discount'])) {
    $discount_code = mysqli_real_escape_string($conn, $_POST['discount_code']);
    $discount_query = "SELECT * FROM discounts WHERE code = '$discount_code' 
                      AND (expires_at IS NULL OR expires_at >= CURDATE()) 
                      AND used_count < usage_limit";
    $discount_result = mysqli_query($conn, $discount_query);
    
    if ($discount = mysqli_fetch_assoc($discount_result)) {
        if ($total >= $discount['min_purchase']) {
            if ($discount['discount_type'] == 'percentage') {
                $discount_amount = $total * ($discount['discount_value'] / 100);
            } else {
                $discount_amount = $discount['discount_value'];
            }
            $final_total = $total - $discount_amount;
            $discount_success = "Discount applied successfully! You saved ₱" . number_format($discount_amount, 2);
            
            // Store discount in session for later use
            $_SESSION['applied_discount'] = $discount;
        } else {
            $discount_error = "Minimum purchase of ₱" . number_format($discount['min_purchase'], 2) . " required for this discount.";
        }
    } else {
        $discount_error = "Invalid or expired discount code.";
    }
}

// Remove discount if requested
if (isset($_POST['remove_discount'])) {
    $discount_code = '';
    $discount_amount = 0;
    $final_total = $total;
    unset($_SESSION['applied_discount']);
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
            (user_id, product_id, status, created_at, quantity, total_amount, payment_method, payment_status, gcash_number, seller_id, platform_commission, seller_earnings, order_ref, discount_amount, final_amount)
            VALUES
            ($userId, $productId, '" . mysqli_real_escape_string($conn, $status) . "', NOW(), $quantity, $totalAmount, '$paymentMethodSafe', '$paymentStatus', $gcash_sql, $seller_sql, $platformCommission, $sellerEarnings, '" . mysqli_real_escape_string($conn, $orderRef) . "', $discount_amount, $final_total)
        ";
        $ok = mysqli_query($conn, $insert);
        if (!$ok) {
            echo "Database error: " . mysqli_error($conn);
            exit;
        }
        
        // Update discount usage count if discount was applied
        if (isset($_SESSION['applied_discount'])) {
            $discount_id = $_SESSION['applied_discount']['id'];
            mysqli_query($conn, "UPDATE discounts SET used_count = used_count + 1 WHERE id = $discount_id");
            mysqli_query($conn, "INSERT INTO user_discounts (user_id, discount_id) VALUES ($userId, $discount_id)");
        }
        
        if (!$firstOrderId) $firstOrderId = mysqli_insert_id($conn);
    }

    // Add loyalty points (10% of final total)
    $loyaltyPointsEarned = floor($final_total * 0.10);
    mysqli_query($conn, "UPDATE users SET loyalty_points = loyalty_points + $loyaltyPointsEarned, total_spent = total_spent + $final_total WHERE id = $userId");

    // Update member tier based on total spent
    $user_update = mysqli_query($conn, "SELECT total_spent FROM users WHERE id = $userId");
    $user_data = mysqli_fetch_assoc($user_update);
    $total_spent = $user_data['total_spent'] + $final_total;
    
    $tier = 'Bronze';
    if ($total_spent >= 5000) $tier = 'Platinum';
    elseif ($total_spent >= 2000) $tier = 'Gold';
    elseif ($total_spent >= 500) $tier = 'Silver';
    
    mysqli_query($conn, "UPDATE users SET member_tier = '$tier' WHERE id = $userId");

    // save receipt data in session (useful if you want)
    $_SESSION['receipt'] = [
        'order_ref' => $orderRef,
        'payment' => $paymentMethod,
        'account_number' => $accountNumber,
        'total' => $total,
        'discount_amount' => $discount_amount,
        'final_total' => $final_total
    ];

    // clear cart and discount
    $_SESSION['cart'] = [];
    unset($_SESSION['applied_discount']);

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
        /* Your existing styles remain the same, just adding discount styles */
        
        .discount-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .discount-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }
        
        .discount-input-group {
            flex: 1;
        }
        
        .discount-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #c3e6cb;
        }
        
        .applied-discount {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #ffeaa7;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .remove-discount-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .discount-breakdown {
            background: #e7f3ff;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
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

                <!-- Discount Section -->
                <div class="discount-section">
                    <h3><i class="fas fa-tag"></i> Apply Discount Code</h3>
                    
                    <?php if ($discount_amount > 0): ?>
                        <div class="applied-discount">
                            <div>
                                <strong>Discount Applied!</strong>
                                <p>Code: <?php echo $discount_code; ?> - Saved ₱<?php echo number_format($discount_amount, 2); ?></p>
                            </div>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="remove_discount" class="remove-discount-btn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="discount-form">
                            <div class="discount-input-group">
                                <label class="form-label">Enter Discount Code</label>
                                <input type="text" name="discount_code" value="<?php echo $discount_code; ?>" class="form-input" placeholder="e.g., STUDENT10">
                            </div>
                            <button type="submit" name="apply_discount" class="btn" style="width: auto; padding: 15px 20px;">
                                Apply Code
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($discount_success): ?>
                        <div class="discount-success">
                            <i class="fas fa-check-circle"></i> <?php echo $discount_success; ?>
                        </div>
                    <?php elseif ($discount_error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $discount_error; ?>
                        </div>
                    <?php endif; ?>
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
                            <i class="fas fa-lock"></i> Complete Purchase - ₱<?php echo number_format($final_total, 2); ?>
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
                    
                    <?php if ($discount_amount > 0): ?>
                    <div class="summary-row" style="color: #27ae60;">
                        <span>Discount:</span>
                        <span>-₱<?php echo number_format($discount_amount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Platform Fee:</span>
                        <span>Free</span>
                    </div>
                </div>
                <div class="summary-row summary-total">
                    <span>Total Amount:</span>
                    <span>₱<?php echo number_format($final_total, 2); ?></span>
                </div>
                
                <?php if ($discount_amount > 0): ?>
                <div class="discount-breakdown">
                    <small><i class="fas fa-piggy-bank"></i> You saved ₱<?php echo number_format($discount_amount, 2); ?> with discount code!</small>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 10px; text-align: center;">
                    <i class="fas fa-download" style="margin-bottom: 10px; display: block; font-size: 1.5rem;"></i>
                    <small>Instant digital access after payment</small>
                </div>
            </div>
        </div>
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
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
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