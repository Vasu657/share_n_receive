<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'share1');
define('DB_USER', 'vasu@123');
define('DB_PASS', 'Vasu@123');

$error = '';
$showLoginForm = isset($_GET['action']) && $_GET['action'] === 'signin';
$showSignupForm = !$showLoginForm;
$signupSuccess = false; // Initialize signup success flag

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Signup logic
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_email']) && isset($_POST['signup_password'])) {
        $email = filter_input(INPUT_POST, 'signup_email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'signup_password', FILTER_SANITIZE_STRING);

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if the email is already registered
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Email already exists. Please try again with a different email.";
        } else {
            // Insert user into the database
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->execute();

            // Set signup success flag
            $signupSuccess = true;

            // Redirect to the login page after successful signup
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?action=signin");
            exit;
        }
    }

    // Login logic
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_email']) && isset($_POST['login_password'])) {
        $email = filter_input(INPUT_POST, 'login_email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'login_password', FILTER_SANITIZE_STRING);

        // Retrieve user from the database based on the email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            // Redirect to the main page (index.php) after successful login
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
} catch (PDOException $e) {
    $error = "Database connection failed: " . $e->getMessage();
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Your CSS styles here */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-container {
            display: flex;
            width: 800px;
        }

        .form-container .form {
            flex: 1;
            padding: 40px;
            display: <?php echo $showLoginForm ? 'block' : 'none'; ?>;
        }

        .form-container .form.signup-form {
            display: <?php echo $showSignupForm ? 'block' : 'none'; ?>;
        }

        .form h2 {
            font-weight: 600;
            margin-bottom: 20px;
        }

        .input-field {
            position: relative;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease forwards;
            opacity: 0;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .input-field i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #999;
        }

        .input-field input {
            padding: 15px 15px 15px 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
            outline: none;
            width: 100%;
            transition: border-color 0.3s ease;
        }

        .input-field input:focus {
            border-color: #ff4d4d;
        }

        .btn {
            padding: 15px;
            border: none;
            border-radius: 5px;
            background-color: #ff4d4d;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background-color: #e63636;
            transform: translateY(-2px);
        }


        
        .signup-link {
            text-align: center;
            margin-top: 20px;
        }

        .signup-link a {
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #333;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }

        .signup-link a:hover {
            border-color: #ff4d4d;
        }

        /* CSS for success/error message animations */
        .success-message,
        .error-message {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .success-message.show,
        .error-message.show {
            opacity: 1;
        }

        /* CSS for Password Strength Meter */
        .password-strength-container {
            position: relative;
            margin-bottom: 20px;
        }

        #password-strength-meter {
            height: 10px;
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        #password-strength-bar {
            height: 100%;
            width: 0;
            background-color: red;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        #password-strength-text {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 10px;
            font-size: 0.8em;
        }

        /* Shake Animation for Error Message */
        .error-message.show {
            animation: shake 0.3s ease forwards;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }




    </style>
</head>
<body>



    <div class="container">
        <div class="form-container">
            <!-- Sign In Form -->
            <div class="form signin-form">
                <h2>Sign In</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="login_email" placeholder="Email" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="login_password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn">Sign In</button>
                    <p class="error-message"><?php echo $error; ?></p>
                </form>
                <div class="signup-link">
                    <p>Don't remember your password? <a href="?action=forgot">Forgot Password</a></p>
                </div>
                <div class="signup-link">
                    <p>Don't have an account? <a href="?action=signup">Sign Up</a></p>
                </div>
            </div>

            <!-- Sign Up Form -->
            <div class="form signup-form">
                <h2>Sign Up</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return validateForm(this)">
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="signup_email" placeholder="Email" required class="validate">
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="signup_password" id="signup_password" placeholder="Password" required class="validate" onkeyup="checkPasswordStrength(this.value)">
                        <div id="password-strength-meter"></div>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="signup_confirm_password" placeholder="Confirm Password" required class="validate" onkeyup="checkPasswordMatch()">
                        <div id="password-match-message"></div>
                    </div>
                    <button type="submit" class="btn btn-signup">Sign Up</button>
                    <p class="error-message"><?php echo $error; ?></p>
                </form>
                <div class="signup-link">
                    <p>Already have an account? <a href="?action=signin">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>











    <!-- JavaScript for Form Validation -->
    <script>
        function validateEmail(email) {
            var re = /\S+@\S+\.\S+/;
            return re.test(email);
        }

        function validatePassword(password) {
            // Your password strength validation logic here
            // You can use libraries like zxcvbn.js for password strength estimation
            return password.length >= 8;
        }

        function validateForm(form) {
            var email = form.querySelector('input[name="signup_email"]').value;
            var password = form.querySelector('input[name="signup_password"]').value;

            if (!validateEmail(email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            if (!validatePassword(password)) {
                alert("Password must be at least 8 characters long.");
                return false;
            }

            return true;
        }

        
    </script>

    <!-- JavaScript for Success/Error Message Animation -->
    <script>
        function showSuccessMessage(message) {
            var successMessage = document.querySelector('.success-message');
            successMessage.textContent = message;
            successMessage.classList.add('show');
            setTimeout(function() {
                successMessage.classList.remove('show');
            }, 3000);
        }

        function showErrorMessage(message) {
            var errorMessage = document.querySelector('.error-message');
            errorMessage.textContent = message;
            errorMessage.classList.add('show');
            setTimeout(function() {
                errorMessage.classList.remove('show');
            }, 3000);
        }
    </script>







<!-- JavaScript for Password Strength Meter and Confirm Password Match -->
<script>
    function checkPasswordStrength(password) {
        var strengthMeter = document.getElementById('password-strength-meter');
        var strengthText = document.getElementById('password-strength-text');
        var strength = 0;

        if (password.match(/[a-z]+/)) {
            strength += 1;
        }
        if (password.match(/[A-Z]+/)) {
            strength += 1;
        }
        if (password.match(/[0-9]+/)) {
            strength += 1;
        }
        if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) {
            strength += 1;
        }

        switch (strength) {
            case 0:
            case 1:
                strengthMeter.style.width = '25%';
                strengthMeter.style.backgroundColor = 'red';
                strengthText.textContent = 'Weak';
                break;
            case 2:
                strengthMeter.style.width = '50%';
                strengthMeter.style.backgroundColor = 'orange';
                strengthText.textContent = 'Moderate';
                break;
            case 3:
                strengthMeter.style.width = '75%';
                strengthMeter.style.backgroundColor = 'yellow';
                strengthText.textContent = 'Good';
                break;
            case 4:
                strengthMeter.style.width = '100%';
                strengthMeter.style.backgroundColor = 'green';
                strengthText.textContent = 'Strong';
                break;
        }
    }

    function checkPasswordMatch() {
        var password = document.getElementById('signup_password').value;
        var confirmPassword = document.querySelector('input[name="signup_confirm_password"]').value;
        var matchMessage = document.getElementById('password-match-message');

        if (password === confirmPassword) {
            matchMessage.textContent = 'Passwords match';
            matchMessage.style.color = 'green';
        } else {
            matchMessage.textContent = 'Passwords do not match';
            matchMessage.style.color = 'red';
        }
    }
</script>

<!-- JavaScript for Email Validation -->
<script>
    function validateEmail(email) {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }

    function validateForm(form) {
        var email = form.querySelector('input[name="signup_email"]').value;
        var password = form.querySelector('input[name="signup_password"]').value;
        var confirmPassword = form.querySelector('input[name="signup_confirm_password"]').value;

        if (!validateEmail(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        if (password.length < 8) {
            alert("Password must be at least 8 characters long.");
            return false;
        }

        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }

        return true;
    }
</script>

    <!-- PHP code to display success/error messages -->
    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($signupSuccess) {
                echo '<script>showSuccessMessage("Sign up successful!");</script>';
            } else {
                echo '<script>showErrorMessage("Sign up failed. Please try again.");</script>';
            }
        }
    ?>
</body>
</html>
