<?php
include('includes/config.php');

if (!isset($_SESSION['customer'])) {
    header("Location: customer-login.php");
    exit();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error = '';
$is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

function get_cart_total($con) {
    if (empty($_SESSION['cart'])) {
        return 0;
    }

    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $con->prepare("SELECT id, ProductPrice FROM tblproducts WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += floatval($row['ProductPrice']) * intval($_SESSION['cart'][$row['id']] ?? 0);
    }

    $stmt->close();
    return $total;
}

function cart_json_response($con, $product_id) {
    $qty = intval($_SESSION['cart'][$product_id] ?? 0);
    $unit_price = 0;

    if ($product_id > 0) {
        $stmt = $con->prepare("SELECT ProductPrice FROM tblproducts WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $unit_price = $row ? floatval($row['ProductPrice']) : 0;
        $stmt->close();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'product_id' => $product_id,
        'quantity' => $qty,
        'cart_count' => array_sum($_SESSION['cart']),
        'line_total' => number_format($unit_price * $qty, 2),
        'cart_total' => number_format(get_cart_total($con), 2),
        'removed' => $qty <= 0,
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
            exit();
        }
        $error = "Security validation failed. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';
        $product_id = intval($_POST['product_id'] ?? 0);

        if ($action === 'add' && $product_id > 0) {
            $_SESSION['cart'][$product_id] = min(99, ($_SESSION['cart'][$product_id] ?? 0) + 1);
            if ($is_ajax) {
                cart_json_response($con, $product_id);
            }
            header("Location: customer-dashboard.php?msg=added#products");
            exit();
        }

        if ($action === 'increase' && $product_id > 0) {
            $_SESSION['cart'][$product_id] = min(99, ($_SESSION['cart'][$product_id] ?? 0) + 1);
            if ($is_ajax) {
                cart_json_response($con, $product_id);
            }
            header("Location: customer-dashboard.php#product-" . $product_id);
            exit();
        }

        if ($action === 'decrease' && $product_id > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]--;
                if ($_SESSION['cart'][$product_id] <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            if ($is_ajax) {
                cart_json_response($con, $product_id);
            }
            header("Location: customer-dashboard.php#product-" . $product_id);
            exit();
        }

        if ($action === 'remove' && $product_id > 0) {
            unset($_SESSION['cart'][$product_id]);
            if ($is_ajax) {
                cart_json_response($con, $product_id);
            }
            header("Location: cart.php");
            exit();
        }

        if ($action === 'place_order') {
            $payment = sanitize_input($_POST['payment_mode'] ?? '');
            $contact = sanitize_input($_POST['contact'] ?? '');
            $payment_modes = ['Cash', 'UPI', 'Card', 'Net Banking'];

            if (empty($_SESSION['cart'])) {
                $error = "Your cart is empty.";
            } elseif (!in_array($payment, $payment_modes, true)) {
                $error = "Choose a valid payment mode.";
            } elseif (!preg_match('/^[0-9]{7,15}$/', $contact)) {
                $error = "Enter a valid contact number.";
            } else {
                $invoice_no = random_int(10000, 99999);
                $customer = $_SESSION['customer'];
                $stmt = $con->prepare("INSERT INTO tblorders (ProductId, Quantity, InvoiceNumber, CustomerName, CustomerContactNo, PaymentMode) VALUES (?, ?, ?, ?, ?, ?)");

                foreach ($_SESSION['cart'] as $pid => $qty) {
                    $pid = intval($pid);
                    $qty = intval($qty);
                    if ($pid > 0 && $qty > 0) {
                        $stmt->bind_param("iiisss", $pid, $qty, $invoice_no, $customer, $contact, $payment);
                        $stmt->execute();
                    }
                }

                $stmt->close();
                $_SESSION['cart'] = [];
                header("Location: view-invoice.php?inv=" . $invoice_no . "&ordered=1");
                exit();
            }
        }
    }
}

$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $con->prepare("SELECT id, ProductName, CategoryName, CompanyName, ProductPrice FROM tblproducts WHERE id IN ($placeholders) ORDER BY ProductName");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['Quantity'] = intval($_SESSION['cart'][$row['id']] ?? 0);
        $row['LineTotal'] = $row['Quantity'] * $row['ProductPrice'];
        $total += $row['LineTotal'];
        $cart_items[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Cart - Dairy Farm</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body class="customer-shop-page">
<?php include('includes/customer-header.php'); ?>
<main class="main-content">
    <div class="page-header">
        <h2 class="page-title">Your cart</h2>
        <a href="customer-dashboard.php#products" class="btn btn-secondary">Continue shopping</a>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <h3>Your cart is empty</h3>
            <p>Add products from the shop. Your invoice will be generated after the order is placed.</p>
            <a href="customer-dashboard.php#products" class="btn btn-primary">Browse products</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-panel">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-product-id="<?php echo intval($item['id']); ?>">
                    <div>
                        <span class="product-category"><?php echo e($item['CategoryName']); ?></span>
                        <h3><?php echo e($item['ProductName']); ?></h3>
                        <p><?php echo e($item['CompanyName']); ?> - Rs. <?php echo number_format($item['ProductPrice'], 2); ?></p>
                    </div>
                    <div class="product-quantity-control cart-quantity-control" data-product-id="<?php echo intval($item['id']); ?>" aria-label="Cart quantity">
                        <form method="POST" action="cart.php" class="inline-form cart-action-form">
                            <?php echo csrf_token_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo intval($item['id']); ?>">
                            <input type="hidden" name="action" value="decrease">
                            <input type="hidden" name="ajax" value="1">
                            <button type="submit" class="qty-btn" aria-label="Decrease quantity">-</button>
                        </form>
                        <span class="quantity-value"><?php echo intval($item['Quantity']); ?></span>
                        <form method="POST" action="cart.php" class="inline-form cart-action-form">
                            <?php echo csrf_token_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo intval($item['id']); ?>">
                            <input type="hidden" name="action" value="increase">
                            <input type="hidden" name="ajax" value="1">
                            <button type="submit" class="qty-btn" aria-label="Increase quantity">+</button>
                        </form>
                    </div>
                    <strong class="cart-line-total">Rs. <?php echo number_format($item['LineTotal'], 2); ?></strong>
                    <form method="POST" action="cart.php" class="inline-form cart-action-form">
                        <?php echo csrf_token_field(); ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?php echo intval($item['id']); ?>">
                        <input type="hidden" name="ajax" value="1">
                        <button type="submit" class="btn btn-sm btn-delete">Remove</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form method="POST" class="form-card checkout-card">
            <?php echo csrf_token_field(); ?>
            <input type="hidden" name="action" value="place_order">
            <h3>Place order</h3>
            <div class="cart-total-row">
                <span>Total</span>
                <strong id="cart-total">Rs. <?php echo number_format($total, 2); ?></strong>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact number</label>
                    <input type="tel" name="contact" maxlength="15" required>
                </div>
                <div class="form-group">
                    <label>Payment mode</label>
                    <select name="payment_mode" required>
                        <option value="">-- Select --</option>
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI</option>
                        <option value="Card">Card</option>
                        <option value="Net Banking">Net Banking</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Place order</button>
        </form>
    <?php endif; ?>
</main>
<script>
document.addEventListener('submit', async (event) => {
    const form = event.target.closest('.cart-action-form');
    if (!form) {
        return;
    }

    event.preventDefault();
    const item = form.closest('.cart-item');
    const buttons = item ? item.querySelectorAll('button') : form.querySelectorAll('button');
    buttons.forEach((button) => button.disabled = true);

    try {
        const response = await fetch(form.getAttribute('action'), {
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

        const total = document.getElementById('cart-total');
        if (total) {
            total.textContent = 'Rs. ' + data.cart_total;
        }

        if (item) {
            if (data.removed) {
                item.remove();
            } else {
                item.querySelector('.quantity-value').textContent = data.quantity;
                item.querySelector('.cart-line-total').textContent = 'Rs. ' + data.line_total;
                buttons.forEach((button) => button.disabled = false);
            }
        }

        if (data.cart_count <= 0) {
            window.location.reload();
        }
    } catch (error) {
        window.location.reload();
    }
});
</script>
</body>
</html>
