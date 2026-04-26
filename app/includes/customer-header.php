<nav class="navbar customer-navbar">
    <div class="nav-brand">Dairy Farm</div>
    <ul class="nav-links">
        <li><a href="customer-dashboard.php">Shop</a></li>
        <li><a href="cart.php" id="cart-link">Cart<span id="cart-count"><?php echo !empty($_SESSION['cart']) ? ' (' . array_sum($_SESSION['cart']) . ')' : ''; ?></span></a></li>
        <?php if (isset($_SESSION['customer'])): ?>
            <li><a href="customer-logout.php" class="nav-logout">Logout</a></li>
        <?php else: ?>
            <li><a href="customer-login.php">Login</a></li>
            <li><a href="customer-register.php" class="nav-logout">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
