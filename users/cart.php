<?php
include("../config.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>My Cart - YOU DO NOTES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h2 {
            color: #5a4b41;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            color: #8d7b68;
            font-size: 1.1rem;
        }

        .cart-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            align-items: start;
        }

        .cart-items {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .cart-summary {
            background: linear-gradient(135deg, #b08968 0%, #a0765b 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 25px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: #fafafa;
            transform: translateX(5px);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f0e6d6 0%, #e8d9c5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: #b08968;
        }

        .item-details {
            flex: 1;
        }

        .item-details h3 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .item-details p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .item-price {
            color: #b08968;
            font-size: 1.3rem;
            font-weight: 700;
            margin-right: 20px;
        }

        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remove-btn:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }

        .summary-details {
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
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
            padding: 16px;
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

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }

        .continue-shopping {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #b08968;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .continue-shopping:hover {
            background: #a0765b;
            transform: translateY(-2px);
        }

        .cart-count {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
.navigation {
    margin-bottom: 30px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #5a4b41;
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.back-btn:hover {
    background: #b08968;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .cart-container {
        grid-template-columns: 1fr;
    }

            .cart-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .item-image {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Add this navigation section -->
    <div class="navigation">
        <a href="products.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="header">
        <h2><i class="fas fa-shopping-cart"></i> My Cart</h2>
        <p>Review your selected notes</p>
    </div>

        <?php if (!empty($cartItems)) { ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item) { ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                            </div>
                            <div class="item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                            <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-details">
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
                        <span>Total:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </a>
                </div>
            </div>
        <?php } else { ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Start shopping to add some amazing notes to your cart!</p>
                <a href="products.php" class="continue-shopping">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        <?php } ?>
    </div>
</body>
</html>