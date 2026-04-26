<?php
include('includes/config.php');

if (!isset($_SESSION['customer'])) {
    header("Location: customer-login.php");
    exit();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$stmt = $con->prepare("SELECT id, ProductName, CategoryName, CompanyName, ProductPrice FROM tblproducts ORDER BY ProductName LIMIT 12");
$stmt->execute();
$products = $stmt->get_result();
$message = $_GET['msg'] ?? '';

$category_images = [
    'Milk Products' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=900&q=80',
    'Cheese & Paneer' => 'https://images.unsplash.com/photo-1452195100486-9cc805987862?auto=format&fit=crop&w=900&q=80',
    'Butter & Ghee' => 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=900&q=80',
    'Yogurt & Curd' => 'https://images.unsplash.com/photo-1571212515416-fef01fc43637?auto=format&fit=crop&w=900&q=80',
    'Ice Cream' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=900&q=80',
];

function product_image($category, $images) {
    return $images[$category] ?? 'https://images.unsplash.com/photo-1528750997573-59b89d56f4f7?auto=format&fit=crop&w=900&q=80';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Shop - Dairy Farm</title>
<link rel="stylesheet" href="css/style.css?v=2026042606">
</head>
<body class="customer-shop-page">
<?php include('includes/customer-header.php'); ?>

<section class="shop-hero">
    <div class="shop-hero-content">
        <p class="eyebrow">Fresh dairy store</p>
        <h1>Welcome, <?php echo e($_SESSION['customer']); ?></h1>
        <p>Browse fresh milk, paneer, butter, curd, cheese and ice cream from trusted dairy suppliers.</p>
        <div class="shop-hero-actions">
            <a href="#products" class="btn btn-primary">Start shopping</a>
            <a href="#products" class="btn btn-secondary">View products</a>
        </div>
    </div>
</section>

<main class="main-content customer-main">
    <section class="customer-feature-grid">
        <div class="customer-feature">
            <strong>Daily Fresh</strong>
            <span>Products managed directly from the shop inventory.</span>
        </div>
        <div class="customer-feature">
            <strong>Quick Billing</strong>
            <span>Create an invoice with multiple dairy items in one flow.</span>
        </div>
        <div class="customer-feature">
            <strong>Trusted Brands</strong>
            <span>Amul, Mother Dairy, Nandini and other suppliers.</span>
        </div>
    </section>

    <section id="products" class="customer-section">
        <div class="page-header">
            <h2 class="page-title">Available products</h2>
            <a href="cart.php" class="btn btn-primary">View cart</a>
        </div>
        <?php if ($message === 'added'): ?>
            <div class="alert alert-success">Product added to cart.</div>
        <?php endif; ?>
        <div class="product-grid">
            <?php while ($p = $products->fetch_assoc()): ?>
            <article class="product-card" id="product-<?php echo intval($p['id']); ?>">
                <img src="<?php echo e(product_image($p['CategoryName'], $category_images)); ?>" alt="<?php echo e($p['ProductName']); ?>">
                <div class="product-card-body">
                    <span class="product-category"><?php echo e($p['CategoryName']); ?></span>
                    <h3><?php echo e($p['ProductName']); ?></h3>
                    <p><?php echo e($p['CompanyName']); ?></p>
                    <div class="product-card-footer">
                        <strong>Rs. <?php echo number_format($p['ProductPrice'], 2); ?></strong>
                        <?php $cart_qty = intval($_SESSION['cart'][$p['id']] ?? 0); ?>
                        <?php if ($cart_qty > 0): ?>
                            <div class="product-quantity-control" data-product-id="<?php echo intval($p['id']); ?>" aria-label="Cart quantity">
                                <form method="POST" action="cart.php" class="inline-form cart-action-form">
                                    <?php echo csrf_token_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo intval($p['id']); ?>">
                                    <input type="hidden" name="action" value="decrease">
                                    <input type="hidden" name="ajax" value="1">
                                    <button type="submit" class="qty-btn" aria-label="Decrease quantity">-</button>
                                </form>
                                <span class="quantity-value"><?php echo $cart_qty; ?></span>
                                <form method="POST" action="cart.php" class="inline-form cart-action-form">
                                    <?php echo csrf_token_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo intval($p['id']); ?>">
                                    <input type="hidden" name="action" value="increase">
                                    <input type="hidden" name="ajax" value="1">
                                    <button type="submit" class="qty-btn" aria-label="Increase quantity">+</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="cart.php" class="inline-form cart-action-form">
                                <?php echo csrf_token_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo intval($p['id']); ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="ajax" value="1">
                                <button type="submit" class="btn btn-sm btn-primary">Add to cart</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </section>
</main>
<script>
const csrfToken = '<?php echo e(get_csrf_token()); ?>';

function quantityControl(productId, quantity) {
    return `
        <div class="product-quantity-control" data-product-id="${productId}" aria-label="Cart quantity">
            <form method="POST" action="cart.php" class="inline-form cart-action-form">
                <input type="hidden" name="csrf_token" value="${csrfToken}">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="action" value="decrease">
                <input type="hidden" name="ajax" value="1">
                <button type="submit" class="qty-btn" aria-label="Decrease quantity">-</button>
            </form>
            <span class="quantity-value">${quantity}</span>
            <form method="POST" action="cart.php" class="inline-form cart-action-form">
                <input type="hidden" name="csrf_token" value="${csrfToken}">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="action" value="increase">
                <input type="hidden" name="ajax" value="1">
                <button type="submit" class="qty-btn" aria-label="Increase quantity">+</button>
            </form>
        </div>
    `;
}

function addButton(productId) {
    return `
        <form method="POST" action="cart.php" class="inline-form cart-action-form">
            <input type="hidden" name="csrf_token" value="${csrfToken}">
            <input type="hidden" name="product_id" value="${productId}">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="ajax" value="1">
            <button type="submit" class="btn btn-sm btn-primary">Add to cart</button>
        </form>
    `;
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('.cart-action-form');
    if (!form) {
        return;
    }

    event.preventDefault();
    const footer = form.closest('.product-card-footer');
    const control = form.closest('.product-quantity-control');
    const buttons = footer.querySelectorAll('button');
    buttons.forEach((button) => button.disabled = true);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (!data.success) {
            window.location.reload();
            return;
        }

        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.textContent = data.cart_count > 0 ? ' (' + data.cart_count + ')' : '';
        }

        if (data.removed) {
            control.outerHTML = addButton(data.product_id);
        } else if (control) {
            control.querySelector('.quantity-value').textContent = data.quantity;
            buttons.forEach((button) => button.disabled = false);
        } else {
            form.outerHTML = quantityControl(data.product_id, data.quantity);
        }
    } catch (error) {
        window.location.reload();
    }
});
</script>
</body>
</html>
