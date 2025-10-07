<?php
include("config.php");
session_start();

if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pin = mysqli_real_escape_string($conn, $_POST['pin']);

    // ✅ BU email check
    if (!preg_match("/@bicol-u\.edu\.ph$/", $email)) {
        $error = "❌ Only BU (@bicol-u.edu.ph) emails are allowed.";
    } else {
        $sql = "INSERT INTO users (name, email, password, address, contact, role, pin) 
                VALUES ('$name', '$email', '$password', '$address', '$contact', 'user', '$pin')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?signup=success");
            exit;
        } else {
            $error = "❌ Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - YOU DO NOTES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f3ef;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 420px;
            margin: 60px auto;
            background: #fffaf5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #444;
            margin-bottom: 25px;
        }

        .input-container {
            position: relative;
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
            border-radius: 8px;
            border: 1px solid #d6c8b8;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #b08968;
        }

        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            font-size: 18px;
        }

        .toggle-eye:hover {
            color: #b08968;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #b08968;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn:hover {
            background: #a0765b;
        }

        .error {
            color: red;
            text-align: center;
            background: #ffe6e6;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ffcccc;
            margin-bottom: 10px;
        }

        .pin-hint {
            font-size: 12px;
            color: #666;
            margin-top: -5px;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
        }

        a {
            color: #b08968;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Create Account</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="post" novalidate>
            <div class="input-container">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="input-container">
                <input type="email" name="email" placeholder="BU Email" required>
            </div>

            <div class="input-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-eye toggle-eye" id="togglePassword"></i>
            </div>

            <div class="input-container">
                <input type="text" name="address" placeholder="Address (optional)">
            </div>

            <div class="input-container">
                <input type="text" name="contact" placeholder="Contact Number (optional)">
            </div>

            <div class="input-container">
                <input type="password" name="pin" id="pin" placeholder="4-6 Digit PIN" pattern="[0-9]{4,6}"
                    title="Please enter 4-6 digits only" maxlength="6" required>
                <i class="fa-solid fa-eye toggle-eye" id="togglePin"></i>
            </div>
            <div class="pin-hint">Must be 4-6 digits only</div>

            <button type="submit" name="signup" class="btn">Sign Up</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        // PIN validation — numbers only
        document.getElementById('pin').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });

        // Toggle visibility for password & PIN
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            icon.addEventListener("click", function() {
                const type = input.type === "password" ? "text" : "password";
                input.type = type;
                this.classList.toggle("fa-eye-slash");
            });
        }

        toggleVisibility("password", "togglePassword");
        toggleVisibility("pin", "togglePin");
    </script>
</body>

</html>
