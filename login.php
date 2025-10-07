<?php
include("config.php");
session_start();

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $pin = $_POST['pin'];

    // Fetch user from DB
    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($user = mysqli_fetch_assoc($result)) {
        if (
            password_verify($password, $user['password']) &&
            $user['pin'] === $pin
        ) {
            $_SESSION['user'] = $user;
            header("Location: products.php");
            exit;
        } else {
            $error = "Incorrect password or PIN.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YOU DO NOTES</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f3ef;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 100px auto;
            background: #fffaf5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
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
            border: 1px solid #c2b9b0;
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
            margin-top: 10px;
            text-align: center;
            background: #ffe6e6;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ffcccc;
        }

        .pin-hint {
            font-size: 12px;
            color: #666;
            margin-top: -5px;
            margin-bottom: 10px;
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
        }

        .signup-link a {
            color: #b08968;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Login to YOU DO NOTES</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" onsubmit="return validateLoginForm()">
            
            <div class="input-container">
                <input type="email" name="email" placeholder="BU Email" required>
            </div>

            <div class="input-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-eye toggle-eye" id="togglePassword"></i>
            </div>

            <div class="input-container">
                <input type="password" name="pin" id="pin" placeholder="PIN (4-6 digits)"
                    pattern="[0-9]{4,6}" title="Please enter 4-6 digits only" maxlength="6" required>
                <i class="fa-solid fa-eye toggle-eye" id="togglePin"></i>
            </div>
            <div class="pin-hint">Must be 4-6 digits only</div>

            <button type="submit" name="login" class="btn">Login</button>
        </form>

        <div class="signup-link">
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        </div>
    </div>

    <script>
        function validateLoginForm() {
            const pin = document.getElementById('pin').value;
            const pinRegex = /^[0-9]{4,6}$/;
            if (!pinRegex.test(pin)) {
                alert('Please enter 4-6 digits for the PIN');
                return false;
            }
            return true;
        }

        // PIN input validation
        document.getElementById('pin').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });

        // Show/hide password or PIN
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
