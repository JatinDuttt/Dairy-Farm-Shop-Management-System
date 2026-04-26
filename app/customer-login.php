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
        $email = strtolower(sanitize_input($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!validate_email($email) || empty($password)) {
            $error = "Email and password are required.";
        } else {
            $stmt = $con->prepare("SELECT id, FullName, Password FROM tblcustomers WHERE Email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (verify_password($password, $row['Password'])) {
                    session_regenerate_id(true);
                    $_SESSION['customer'] = $row['FullName'];
                    $_SESSION['customerid'] = $row['id'];
                    header("Location: customer-dashboard.php");
                    exit();
                }
            }

            $error = "Invalid email or password.";
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
<title>Customer Login - Dairy Farm</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body class="customer-auth-page">
<div class="customer-auth-layout">
    <section class="customer-auth-visual customer-auth-visual-login">
        <div>
            <p class="eyebrow">Welcome back</p>
            <h1>Fresh milk, paneer, butter, curd and more.</h1>
            <p>Login to continue shopping with the Dairy Farm customer portal.</p>
        </div>
    </section>
    <section class="login-container customer-auth-card">
        <div class="login-logo">
            <h1>Customer Login</h1>
            <p>Dairy Farm Shop</p>
        </div>
        <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="POST">
            <?php echo csrf_token_field(); ?>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Login</button>
        </form>
        <p class="login-hint">New customer? <a href="customer-register.php">Create account</a></p>
        <p class="login-hint"><a href="index.php">Admin login</a></p>
    </section>
</div>
</body>
</html>
