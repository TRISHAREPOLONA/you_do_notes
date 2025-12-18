<?php
include("../config.php");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

// ✅ If it's an array, extract the email
if (is_array($_SESSION['user'])) {
   $email = $_SESSION['user']['email'];
} else {
   $email = $_SESSION['user'];
}

// ✅ Fetch current user details
$user_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// ✅ Update payment details if submitted
if (isset($_POST['update_payment'])) {
   $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
   $account = mysqli_real_escape_string($conn, $_POST['account_number']);

   if ($method === 'GCash') {
      $update = "UPDATE users 
               SET payment_method='GCash', gcash_number='$account', paymaya_number=NULL 
               WHERE email='$email'";
   } else {
      $update = "UPDATE users 
               SET payment_method='PayMaya', paymaya_number='$account', gcash_number=NULL 
               WHERE email='$email'";
   }

   if (mysqli_query($conn, $update)) {
      header("Location: seller.php?success=1");
      exit;
   } else {
      echo "Error updating payment info: " . mysqli_error($conn);
   }
}

// ✅ Seller Stats (Balance / Earnings / Sales)
$total_sales = 0;
$total_earnings = 0;

$stats_query = "SELECT 
                  COUNT(id) as sales, 
                  SUM(seller_earnings) as earnings 
               FROM orders 
               WHERE seller_id = '{$user['id']}' AND status='Completed'";

$stats_result = mysqli_query($conn, $stats_query);

if ($stats_result && mysqli_num_rows($stats_result) > 0) {
   $stats = mysqli_fetch_assoc($stats_result);
   $total_sales = $stats['sales'] ?? 0;
   $total_earnings = $stats['earnings'] ?? 0;
}

// Get total notes count
$notes_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM products WHERE seller_email = '$email'"));
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta charset="UTF-8">
   <title>Seller Dashboard - YOU DO NOTES</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
   <style>
      .seller-dashboard {
         background: linear-gradient(135deg, #f7f3ef 0%, #f0e6d6 100%);
         min-height: 100vh;
         padding: 30px 20px;
      }

      .dashboard-container {
         max-width: 1200px;
         margin: 0 auto;
      }

      .dashboard-header {
         text-align: center;
         margin-bottom: 40px;
      }

      .dashboard-header h1 {
         color: #5a4b41;
         font-size: 2.8rem;
         margin-bottom: 10px;
         font-weight: 700;
      }

      .dashboard-header p {
         color: #8d7b68;
         font-size: 1.2rem;
      }

      .welcome-card {
         background: linear-gradient(135deg, #b08968 0%, #a0765b 100%);
         color: white;
         padding: 30px;
         border-radius: 20px;
         text-align: center;
         margin-bottom: 30px;
         box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      }

      .welcome-card h2 {
         margin: 0 0 10px 0;
         font-size: 1.8rem;
         font-weight: 600;
      }

      .welcome-card p {
         margin: 0;
         opacity: 0.9;
         font-size: 1.1rem;
      }

      .stats-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 25px;
         margin-bottom: 40px;
      }

      .stat-card {
         background: #ffffff;
         padding: 30px;
         border-radius: 15px;
         text-align: center;
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
         transition: transform 0.3s ease;
         border-left: 4px solid #b08968;
      }

      .stat-card:hover {
         transform: translateY(-5px);
      }

      .stat-card i {
         font-size: 2.5rem;
         color: #b08968;
         margin-bottom: 15px;
      }

      .stat-card h3 {
         font-size: 2.2rem;
         margin: 10px 0;
         color: #5a4b41;
         font-weight: 700;
      }

      .stat-card p {
         color: #666;
         font-size: 1rem;
         margin: 0;
         font-weight: 600;
      }

      .dashboard-sections {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 30px;
         margin-bottom: 40px;
      }

      .dashboard-card {
         background: #ffffff;
         border-radius: 20px;
         padding: 30px;
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      }

      .card-header {
         display: flex;
         align-items: center;
         gap: 12px;
         margin-bottom: 25px;
         padding-bottom: 15px;
         border-bottom: 2px solid #f0f0f0;
      }

      .card-header i {
         font-size: 1.5rem;
         color: #b08968;
      }

      .card-header h3 {
         margin: 0;
         color: #5a4b41;
         font-size: 1.4rem;
         font-weight: 700;
      }

      .form-group {
         margin-bottom: 20px;
      }

      .form-label {
         display: block;
         color: #333;
         font-weight: 600;
         margin-bottom: 8px;
         font-size: 0.95rem;
      }

      .form-input,
      .form-select,
      .form-textarea {
         width: 100%;
         padding: 14px;
         border: 2px solid #e9ecef;
         border-radius: 10px;
         font-size: 1rem;
         transition: all 0.3s ease;
         background: #fff;
      }

      .form-input:focus,
      .form-select:focus,
      .form-textarea:focus {
         outline: none;
         border-color: #b08968;
         box-shadow: 0 0 0 3px rgba(176, 137, 104, 0.1);
      }

      .form-textarea {
         resize: vertical;
         min-height: 100px;
         font-family: inherit;
      }

      .radio-group {
         margin: 15px 0;
      }

      .radio-options {
         display: flex;
         gap: 20px;
         margin-top: 10px;
      }

      .radio-option {
         flex: 1;
         background: #f8f9fa;
         border: 2px solid #e9ecef;
         border-radius: 10px;
         padding: 15px;
         text-align: center;
         cursor: pointer;
         transition: all 0.3s ease;
      }

      .radio-option:hover {
         border-color: #b08968;
      }

      .radio-option.selected {
         border-color: #b08968;
         background: #fffaf5;
      }

      .radio-option input {
         display: none;
      }

      .upload-toggle {
         margin: 20px 0;
      }

      .btn-primary {
         background: #b08968;
         color: white;
         border: none;
         padding: 15px 25px;
         border-radius: 10px;
         font-size: 1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         width: 100%;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 10px;
      }

      .btn-primary:hover {
         background: #a0765b;
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(176, 137, 104, 0.3);
      }

      .notes-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 25px;
         margin-top: 20px;
      }

      .note-card {
         background: #fff;
         border-radius: 15px;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
         transition: transform 0.3s ease;
      }

      .note-card:hover {
         transform: translateY(-5px);
      }

      .note-image {
         width: 100%;
         height: 160px;
         object-fit: cover;
      }

      .note-content {
         padding: 20px;
      }

      .note-title {
         font-size: 1.1rem;
         font-weight: 600;
         color: #333;
         margin-bottom: 8px;
         line-height: 1.3;
      }

      .note-price {
         color: #b08968;
         font-size: 1.3rem;
         font-weight: 700;
         margin-bottom: 15px;
      }

      .note-actions {
         display: flex;
         gap: 10px;
      }

      .btn-edit,
      .btn-delete {
         flex: 1;
         padding: 8px 12px;
         border-radius: 8px;
         text-decoration: none;
         font-size: 0.9rem;
         font-weight: 600;
         text-align: center;
         transition: all 0.3s ease;
      }

      .btn-edit {
         background: #b08968;
         color: white;
      }

      .btn-edit:hover {
         background: #a0765b;
      }

      .btn-delete {
         background: #e74c3c;
         color: white;
      }

      .btn-delete:hover {
         background: #c0392b;
      }

      .empty-state {
         text-align: center;
         padding: 40px;
         color: #666;
      }

      .empty-state i {
         font-size: 3rem;
         color: #ddd;
         margin-bottom: 15px;
      }

      .success-message {
         background: #d4edda;
         color: #155724;
         padding: 15px;
         border-radius: 10px;
         margin-bottom: 20px;
         border: 1px solid #c3e6cb;
         text-align: center;
      }

      @media (max-width: 768px) {
         .dashboard-sections {
            grid-template-columns: 1fr;
         }

         .stats-grid {
            grid-template-columns: 1fr;
         }

         .radio-options {
            flex-direction: column;
         }
      }
   </style>
</head>

<body class="seller-dashboard">
   <div class="dashboard-container">
      <div class="dashboard-header">
         <h1><i class="fas fa-store"></i> Seller Dashboard</h1>
         <p>Manage your notes and track your earnings</p>
      </div>

      <div class="welcome-card">
         <h2>Welcome back, <?php echo htmlspecialchars($email); ?>! 👋</h2>
         <p>Ready to share your knowledge with the community?</p>
      </div>

      <?php if (isset($_GET['success'])): ?>
         <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php if ($_GET['success'] == 'upload'): ?>
               Note uploaded successfully! It's now available in the store.
            <?php else: ?>
               Payment information updated successfully!
            <?php endif; ?>
         </div>
      <?php endif; ?>

      <div class="stats-grid">
         <div class="stat-card">
            <i class="fas fa-chart-line"></i>
            <h3><?php echo $total_sales; ?></h3>
            <p>Total Sales</p>
         </div>
         <div class="stat-card">
            <i class="fas fa-money-bill-wave"></i>
            <h3>₱<?php echo number_format($total_earnings, 2); ?></h3>
            <p>Total Earnings</p>
         </div>
         <div class="stat-card">
            <i class="fas fa-file-alt"></i>
            <h3><?php echo $notes_count; ?></h3>
            <p>Active Notes</p>
         </div>
         <div class="stat-card">
            <i class="fas fa-wallet"></i>
            <h3><?php echo htmlspecialchars($user['payment_method'] ?? 'Not Set'); ?></h3>
            <p>Payment Method</p>
         </div>
      </div>

      <div class="dashboard-sections">
         <!-- Payment Information -->
         <div class="dashboard-card">
            <div class="card-header">
               <i class="fas fa-credit-card"></i>
               <h3>Payment Information</h3>
            </div>
            <form method="POST" action="" onsubmit="return validatePaymentForm()">
               <div class="form-group">
                  <label class="form-label">Preferred Payment Method</label>
                  <select name="payment_method" class="form-select" required>
                     <option value="GCash" <?php if (($user['payment_method'] ?? '') == 'GCash') echo 'selected'; ?>>GCash</option>
                     <option value="PayMaya" <?php if (($user['payment_method'] ?? '') == 'PayMaya') echo 'selected'; ?>>PayMaya</option>
                  </select>
               </div>

               <div class="form-group">
                  <label class="form-label">Account Number</label>
                  <input type="text"
                     name="account_number"
                     id="account_number"
                     class="form-input"
                     value="<?php echo htmlspecialchars($user['gcash_number'] ?: $user['paymaya_number'] ?: ''); ?>"
                     placeholder="09XXXXXXXXX"
                     pattern="[0-9]{11}"
                     maxlength="11"
                     required>
               </div>

               <button type="submit" name="update_payment" class="btn-primary">
                  <i class="fas fa-save"></i> Update Payment Info
               </button>
            </form>
         </div>

         <!-- Upload New Note -->
         <div class="dashboard-card">
            <div class="card-header">
               <i class="fas fa-upload"></i>
               <h3>Upload New Note</h3>
            </div>
            <form method="POST" enctype="multipart/form-data" action="upload_note.php">
               <div class="form-group">
                  <input type="text" name="title" class="form-input" placeholder="Note Title" required>
               </div>

               <div class="form-group">
                  <textarea name="description" class="form-textarea" placeholder="Description" required></textarea>
               </div>

               <div class="form-group">
                  <input type="number" name="price" class="form-input" placeholder="Price" step="0.01" required>
               </div>

      

               <div class="upload-toggle">
                  <label class="form-label">Upload Type</label>
                  <div class="radio-options">
                     <div class="radio-option selected" onclick="selectUploadType('file')">
                        <i class="fas fa-file-upload"></i>
                        <div>Upload File</div>
                        <input type="radio" name="upload_type" value="file" checked>
                     </div>
                     <div class="radio-option" onclick="selectUploadType('link')">
                        <i class="fas fa-link"></i>
                        <div>Provide Link</div>
                        <input type="radio" name="upload_type" value="link">
                     </div>
                  </div>
               </div>

               <div id="file-upload">
                  <div class="form-group">
                     <label class="form-label">Note File (PDF/DOCX/PPTX)</label>
                     <input type="file" name="note_file" class="form-input" accept=".pdf,.docx,.pptx">
                  </div>
               </div>

               <div id="link-upload" style="display:none;">
                  <div class="form-group">
                     <label class="form-label">Note Link (Google Drive/OneDrive)</label>
                     <input type="url" name="note_link" class="form-input" placeholder="https://...">
                  </div>
               </div>

               <button type="submit" name="upload" class="btn-primary">
                  <i class="fas fa-cloud-upload-alt"></i> Upload Note
               </button>
            </form>
         </div>
      </div>

      <!-- Your Notes -->
      <div class="dashboard-card">
         <div class="card-header">
            <i class="fas fa-folder-open"></i>
            <h3>Your Notes (<?php echo $notes_count; ?>)</h3>
         </div>

         <div class="notes-grid">
            <?php
            $check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'seller_email'");
            if (mysqli_num_rows($check) > 0) {
               $query = "SELECT * FROM products WHERE seller_email = '$email' ORDER BY id DESC";
            } else {
               $query = "SELECT * FROM products ORDER BY id DESC";
            }

            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
               while ($row = mysqli_fetch_assoc($result)) { ?>
                  <div class="note-card">
                     <?php if (!empty($row['file_path'])): ?>
   <iframe src="<?php echo htmlspecialchars($row['file_path']); ?>#toolbar=0"
      style="width:100%; height:200px; filter: blur(4px); pointer-events:none; border-radius:12px;">
   </iframe>
   <div style="text-align:center; margin-top:5px; color:#a33; font-size:13px;">
      🔒 Preview Blurred – Buyers Only
   </div>
<?php else: ?>
   <div style="width:100%; height:200px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; border-radius:12px;">
      <i class="fa-solid fa-file" style="font-size:40px; color:#666; filter: blur(2px);"></i>
   </div>
   <div style="text-align:center; margin-top:5px; color:#a33; font-size:13px;">
      🔒 Preview Blurred – Buyers Only
   </div>
<?php endif; ?>

                     <div class="note-content">
                        <div class="note-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="note-price">₱<?php echo number_format($row['price'], 2); ?></div>
                        <div class="note-actions">
                           <a href="edit_note.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                              <i class="fas fa-edit"></i> Edit
                           </a>
                           <a href="delete_note.php?id=<?php echo $row['id']; ?>" class="btn-delete">
                              <i class="fas fa-trash"></i> Delete
                           </a>
                        </div>
                     </div>
                  </div>
               <?php }
            } else { ?>
               <div class="empty-state" style="grid-column: 1 / -1;">
                  <i class="fas fa-file-alt"></i>
                  <h3>No Notes Yet</h3>
                  <p>Start by uploading your first note to begin earning!</p>
               </div>
            <?php } ?>
         </div>
      </div>
   </div>

   <script>
      function selectUploadType(type) {
         const fileOption = document.querySelector('.radio-option:first-child');
         const linkOption = document.querySelector('.radio-option:last-child');
         const fileUpload = document.getElementById('file-upload');
         const linkUpload = document.getElementById('link-upload');

         if (type === 'file') {
            fileOption.classList.add('selected');
            linkOption.classList.remove('selected');
            fileUpload.style.display = 'block';
            linkUpload.style.display = 'none';
            document.querySelector('input[value="file"]').checked = true;
         } else {
            linkOption.classList.add('selected');
            fileOption.classList.remove('selected');
            fileUpload.style.display = 'none';
            linkUpload.style.display = 'block';
            document.querySelector('input[value="link"]').checked = true;
         }
      }

      function validatePaymentForm() {
         const accountNumber = document.getElementById('account_number').value;
         const phoneRegex = /^[0-9]{11}$/;

         if (!phoneRegex.test(accountNumber)) {
            alert('Please enter exactly 11 digits for the account number (09XXXXXXXXX)');
            return false;
         }

         if (!accountNumber.startsWith('09')) {
            alert('Account number should start with 09');
            return false;
         }

         return true;
      }

      // Real-time validation
      document.getElementById('account_number').addEventListener('input', function(e) {
         this.value = this.value.replace(/[^0-9]/g, '');
         if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
         }
      });
   </script>
</body>

</html>
