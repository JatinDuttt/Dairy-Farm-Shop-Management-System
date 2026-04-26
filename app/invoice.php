<?php
include('includes/config.php');
require_admin();

$error = '';
$invoice_no = random_int(10000, 99999);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Security validation failed. Please try again.";
    } else {
        $customer = isset($_SESSION['customer']) ? $_SESSION['customer'] : sanitize_input($_POST['customer_name'] ?? '');
        $contact = sanitize_input($_POST['contact'] ?? '');
        $payment = sanitize_input($_POST['payment_mode'] ?? '');
        $inv_no = intval($_POST['invoice_no'] ?? 0);
        $prod_ids = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $payment_modes = ['Cash', 'UPI', 'Card', 'Net Banking'];
        $saved_items = 0;

        if (!validate_string_length($customer, 2, 150)) {
            $error = "Customer name must be 2-150 characters.";
        } elseif (!preg_match('/^[0-9]{7,15}$/', $contact)) {
            $error = "Enter a valid contact number.";
        } elseif (!in_array($payment, $payment_modes, true)) {
            $error = "Choose a valid payment mode.";
        } elseif ($inv_no < 10000) {
            $error = "Invalid invoice number.";
        } else {
            $stmt = $con->prepare("INSERT INTO tblorders (ProductId, Quantity, InvoiceNumber, CustomerName, CustomerContactNo, PaymentMode) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($prod_ids as $i => $pid) {
                $pid = intval($pid);
                $qty = intval($quantities[$i] ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $stmt->bind_param("iiisss", $pid, $qty, $inv_no, $customer, $contact, $payment);
                    if ($stmt->execute()) {
                        $saved_items++;
                    }
                }
            }
            $stmt->close();

            if ($saved_items > 0) {
                header("Location: view-invoice.php?inv=" . $inv_no);
                exit();
            }

            $error = "Add at least one product with quantity.";
        }
    }
}

$stmt = $con->prepare("SELECT id, ProductName, ProductPrice FROM tblproducts ORDER BY ProductName");
$stmt->execute();
$products = $stmt->get_result();
$product_options = [];
while ($p = $products->fetch_assoc()) {
    $product_options[] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Invoice - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042606">
</head>
<body>
<?php include(isset($_SESSION['admin']) ? 'includes/header.php' : 'includes/customer-header.php'); ?>
<div class="main-content">
    <h2 class="page-title">New invoice</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <div class="form-card">
        <form method="POST" id="invoice-form">
            <?php echo csrf_token_field(); ?>
            <input type="hidden" name="invoice_no" value="<?php echo intval($invoice_no); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Customer name</label>
                    <input type="text" name="customer_name" id="customer-name" maxlength="150" value="<?php echo e($_SESSION['customer'] ?? ''); ?>" <?php echo isset($_SESSION['customer']) ? 'readonly' : ''; ?> required>
                </div>
                <div class="form-group">
                    <label>Contact number</label>
                    <input type="tel" name="contact" id="contact" maxlength="15" required>
                </div>
                <div class="form-group">
                    <label>Payment mode</label>
                    <select name="payment_mode" id="payment-mode" required>
                        <option value="">-- Select --</option>
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI</option>
                        <option value="Card">Card</option>
                        <option value="Net Banking">Net Banking</option>
                    </select>
                </div>
            </div>
            <h3>Items</h3>
            <div id="cart-items">
                <div class="cart-row">
                    <select name="product_id[]" class="product-select" required>
                        <option value="">-- Select product --</option>
                        <?php foreach ($product_options as $p): ?>
                        <option value="<?php echo intval($p['id']); ?>" data-price="<?php echo e($p['ProductPrice']); ?>">
                            <?php echo e($p['ProductName']); ?> (Rs. <?php echo number_format($p['ProductPrice'], 2); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity[]" placeholder="Qty" min="1" value="1" required>
                    <button type="button" class="btn btn-sm btn-delete remove-row" onclick="removeRow(this)">Remove</button>
                </div>
            </div>
            <button type="button" onclick="addRow()" class="btn btn-secondary add-item-btn">+ Add item</button>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="generate-invoice">Generate invoice</button>
            </div>
        </form>
    </div>
</div>
<script>
function addRow() {
    const container = document.getElementById('cart-items');
    const first = container.querySelector('.cart-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(input => input.value = '1');
    clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    container.appendChild(clone);
}

function removeRow(button) {
    const rows = document.querySelectorAll('.cart-row');
    if (rows.length > 1) {
        button.closest('.cart-row').remove();
    }
}
</script>
</body>
</html>
