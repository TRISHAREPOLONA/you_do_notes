<?php
include("config.php");
session_start();

// ✅ Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
   die("Invalid product ID.");
}

$id = (int) $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id LIMIT 1");

if (!$result || mysqli_num_rows($result) === 0) {
   die("Product not found.");
}

$product = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <meta charset="UTF-8">
   <title><?php echo htmlspecialchars($product['title']); ?> - Product Details</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
      body {
         font-family: 'Poppins', sans-serif;
         background: #f9f6f1;
         margin: 0;
         padding: 0;
      }

      .container {
         max-width: 900px;
         margin: 40px auto;
         background: #fff;
         border-radius: 15px;
         padding: 25px;
         box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
         display: flex;
         gap: 30px;
      }

      .product-image {
         flex: 1;
      }

      .product-image img {
         width: 100%;
         max-height: 400px;
         object-fit: cover;
         border-radius: 12px;
      }

      .product-details {
         flex: 2;
         display: flex;
         flex-direction: column;
         justify-content: center;
      }

      h2 {
         margin-top: 0;
         color: #444;
      }

      p {
         color: #555;
         margin: 8px 0;
      }

      .price {
         font-size: 20px;
         font-weight: bold;
         color: #b08968;
         margin: 15px 0;
      }

      .btn {
         display: inline-block;
         padding: 12px 20px;
         margin-top: 15px;
         background: #c8a97e;
         color: white;
         text-decoration: none;
         border-radius: 8px;
         font-weight: bold;
         transition: background 0.3s ease;
      }

      .btn:hover {
         background: #b08968;
      }
   </style>
</head>

<body>
   <div class="container">
      <div class="product-image">
         <?php if (!empty($product['file_path'])): ?>
            <iframe src="<?php echo htmlspecialchars($product['file_path']); ?>#toolbar=0"
               style="width:100%; height:400px; filter: blur(5px); pointer-events:none; border-radius:12px;">
            </iframe>
            <div style="text-align:center; margin-top:5px; color:#a33; font-size:14px;">
               🔒 Preview Blurred – Purchase to Unlock
            </div>
         <?php else: ?>
            <img src="images/<?php echo htmlspecialchars($product['image']); ?>"
               alt="<?php echo htmlspecialchars($product['title']); ?>"
               style="filter: blur(5px);">
            <div style="text-align:center; margin-top:5px; color:#a33; font-size:14px;">
               🔒 Preview Blurred – Purchase to Unlock
            </div>
         <?php endif; ?>
      </div>


      <div class="product-details">
         <h2><?php echo htmlspecialchars($product['title']); ?></h2>
         <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
         <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>

         <a href="cart.php?id=<?php echo $product['id']; ?>" class="btn">
            <i class="fa fa-cart-plus"></i> Add to Cart
         </a>
      </div>
   </div>
</body>

</html>