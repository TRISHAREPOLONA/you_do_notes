<?php
include("../config.php");
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

// ✅ Fetch related/suggested products (limit 3)
$suggestions = mysqli_query($conn, "SELECT * FROM products WHERE id != $id ORDER BY RAND() LIMIT 3");
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

      .product-image iframe, 
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

      /* Suggestions Section */
      .suggestions {
         max-width: 900px;
         margin: 40px auto;
         background: #fffaf5;
         padding: 25px;
         border-radius: 15px;
         box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.08);
      }

      .suggestions h3 {
         margin-bottom: 20px;
         color: #5a4b41;
         text-align: center;
      }

      .suggestion-list {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 20px;
      }

      .suggestion-card {
         background: #fff;
         padding: 15px;
         border-radius: 10px;
         box-shadow: 0 2px 5px rgba(0,0,0,0.1);
         transition: transform 0.2s;
         text-align: center;
      }

      .suggestion-card:hover {
         transform: translateY(-5px);
      }

      .suggestion-card h4 {
         color: #444;
         font-size: 1.1rem;
         margin: 10px 0;
      }

      .suggestion-card p {
         color: #777;
         font-size: 0.9rem;
      }

      .back-btn {
         display: block;
         width: fit-content;
         margin: 40px auto;
         text-align: center;
         background: #5a4b41;
         color: #fff;
         padding: 12px 20px;
         border-radius: 8px;
         text-decoration: none;
         font-weight: bold;
         transition: background 0.3s;
      }

      .back-btn:hover {
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
            <div style="width:100%; height:400px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; border-radius:12px;">
               <i class="fa-solid fa-file" style="font-size: 50px; color: #666; filter: blur(3px);"></i>
            </div>
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

   <!-- You may also like -->
   <div class="suggestions">
      <h3>You May Also Like</h3>
      <div class="suggestion-list">
         <?php while ($row = mysqli_fetch_assoc($suggestions)): ?>
            <div class="suggestion-card">
               <h4><?php echo htmlspecialchars($row['title']); ?></h4>
               <p>₱<?php echo number_format($row['price'], 2); ?></p>
               <a href="product_details.php?id=<?php echo $row['id']; ?>" class="btn">View</a>
            </div>
         <?php endwhile; ?>
      </div>
   </div>

   <a href="products.php" class="back-btn"><i class="fa fa-arrow-left"></i> Back to Homepage</a>

</body>
</html>
