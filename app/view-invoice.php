<?php
include('includes/config.php');
require_admin_or_customer();

$inv_no = get_validated_int('inv', 0, 1);
if ($inv_no <= 0) {
    header("Location: sales-report.php");
    exit();
}

$stmt = $con->prepare(
    "SELECT o.*, p.ProductName, p.ProductPrice
     FROM tblorders o
     JOIN tblproducts p ON o.ProductId = p.id
     WHERE o.InvoiceNumber = ?
     ORDER BY o.id ASC"
);
$stmt->bind_param("i", $inv_no);
$stmt->execute();
$orders = $stmt->get_result();

$total = 0;
$items = [];
while ($r = $orders->fetch_assoc()) {
    $r['LineTotal'] = $r['ProductPrice'] * $r['Quantity'];
    $total += $r['LineTotal'];
    $items[] = $r;
}
$stmt->close();

if (empty($items)) {
    header("Location: sales-report.php");
    exit();
}

if (isset($_SESSION['customer']) && $items[0]['CustomerName'] !== $_SESSION['customer']) {
    header("Location: customer-dashboard.php");
    exit();
}

$order_success = isset($_GET['ordered']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice #<?php echo intval($inv_no); ?> - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body>
<?php include(isset($_SESSION['admin']) ? 'includes/header.php' : 'includes/customer-header.php'); ?>
<div class="main-content">
    <?php if ($order_success): ?>
        <div class="alert alert-success">Order placed successfully. Your invoice is ready.</div>
    <?php endif; ?>
    <div class="invoice-box" id="invoice-print">
        <div class="invoice-header">
            <h2>Dairy Farm Shop</h2>
            <p>Tax Invoice</p>
        </div>
        <div class="invoice-meta">
            <div><strong>Invoice #:</strong> <?php echo intval($inv_no); ?></div>
            <div><strong>Customer:</strong> <?php echo e($items[0]['CustomerName']); ?></div>
            <div><strong>Date:</strong> <?php echo date('d M Y', strtotime($items[0]['InvoiceGenDate'])); ?></div>
            <div><strong>Payment:</strong> <?php echo e($items[0]['PaymentMode']); ?></div>
        </div>
        <table class="data-table">
            <thead><tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($item['ProductName']); ?></td>
                    <td>Rs. <?php echo number_format($item['ProductPrice'], 2); ?></td>
                    <td><?php echo intval($item['Quantity']); ?></td>
                    <td>Rs. <?php echo number_format($item['LineTotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4"><strong>Grand Total</strong></td><td><strong>Rs. <?php echo number_format($total, 2); ?></strong></td></tr>
            </tfoot>
        </table>
        <p class="invoice-footer">Thank you for your purchase!</p>
    </div>
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">Print invoice</button>
        <?php if (isset($_SESSION['admin'])): ?>
            <a href="invoice.php" class="btn btn-secondary">New invoice</a>
        <?php else: ?>
            <a href="customer-dashboard.php#products" class="btn btn-secondary">Back to shop</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
