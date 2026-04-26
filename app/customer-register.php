<?php
include('includes/config.php');

if (isset($_SESSION['customer'])) {
    header("Location: customer-dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Security validation failed. Please try again.";
    } else {
        $name = sanitize_input($_POST['full_name'] ?? '');
        $email = strtolower(sanitize_input($_POST['email'] ?? ''));
        $mobile = sanitize_input($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!validate_string_length($name, 2, 150)) {
            $error = "Full name must be 2-150 characters.";
        } elseif (!validate_email($email)) {
            $error = "Enter a valid email address.";
        } elseif (!preg_match('/^[0-9]{7,15}$/', $mobile)) {
            $error = "Enter a valid mobile number.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $hash = hash_password($password);
            $stmt = $con->prepare("INSERT INTO tblcustomers (FullName, Email, MobileNumber, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $mobile, $hash);

            if ($stmt->execute()) {
                $_SESSION['customer'] = $name;
                $_SESSION['customerid'] = $stmt->insert_id;
                header("Location: customer-dashboard.php");
                exit();
            }

            $error = $con->errno === 1062 ? "An account with this email already exists." : "Could not create account. Please try again.";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Register - Dairy Farm</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body class="customer-auth-page">
<div class="customer-auth-layout">
    <section class="customer-auth-visual">
        <div>
            <p class="eyebrow">Fresh daily</p>
            <h1>Bring farm-fresh dairy home faster.</h1>
            <p>Create your customer account to browse products and generate invoices from the shop system.</p>
        </div>
    </section>
    <section class="login-container customer-auth-card">
        <div class="login-logo">
            <h1>Create Account</h1>
            <p>Join Dairy Farm Shop</p>
        </div>
        <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="POST">
            <?php echo csrf_token_field(); ?>
            <div class="form-group">
                <label>Full name</label>
                <input type="text" name="full_name" maxlength="150" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" maxlength="150" required>
            </div>
            <div class="form-group">
                <label>Mobile number</label>
                <input type="tel" name="mobile" maxlength="15" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <div class="form-group">
                <label>Confirm password</label>
                <input type="password" name="confirm_password" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Register</button>
        </form>
        <p class="login-hint">Already registered? <a href="customer-login.php">Login</a></p>
        <p class="login-hint"><a href="index.php">Admin login</a></p>
    </section>
</div>
</body>
</html>
