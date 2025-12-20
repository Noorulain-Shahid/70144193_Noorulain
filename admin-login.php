<?php
require('db_connect.php');
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['email'])) {
    header("Location: admin-dashboard.php");
    exit();
}

$error_message = "";

if (isset($_POST['email'])) {
    $email = stripslashes($_REQUEST['email']);
    $email = mysqli_real_escape_string($conn, $email);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($conn, $password);

    // Check user exists
    $query = "SELECT * FROM `users` WHERE email='$email'";
    $result = mysqli_query($conn, $query) or die(mysql_error());
    $rows = mysqli_num_rows($result);
    
    if ($rows == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            // Security: Regenerate Session ID to prevent Session Fixation
            session_regenerate_id(true);

            $_SESSION['email'] = $email;
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id']; // Store User ID
            
            // Security: Bind session to User Agent (Browser)
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['last_activity'] = time(); // Start timer

            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-message {
            color: #ff4444;
            margin-bottom: 15px;
            text-align: center;
        }
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
        .signup-link a {
            color: #3498db;
            text-decoration: none;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-shield-alt"></i>
                <h1>Admin Panel</h1>
                <p>E-Commerce Management System</p>
            </div>
            <form class="login-form" method="post" action="">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required placeholder="admin@ecommerce.com">
                </div>
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <?php if($error_message != "") { ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php } ?>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
                
                <div class="signup-link">
                    <p>Don't have an account? <a href="admin-signup.php">Sign Up</a></p>
                </div>
            </form>
            <div class="login-footer">
                <p class="demo-credentials">
                    <strong>Demo Credentials:</strong><br>
                    Email: admin@ecommerce.com<br>
                    Password: admin123
                </p>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
