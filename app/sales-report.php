<?php
include('includes/config.php');
require_admin();

$stmt = $con->prepare(
    "SELECT o.*, p.ProductName, p.ProductPrice, (o.Quantity * p.ProductPrice) AS Total
     FROM tblorders o
     JOIN tblproducts p ON o.ProductId = p.id
     ORDER BY o.InvoiceGenDate DESC"
);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Report - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">Sales report</h2>
        <a href="invoice.php" class="btn btn-primary">+ New invoice</a>
    </div>
    <div class="table-container">
        <table class="data-table" id="orders-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $orders->fetch_assoc()): ?>
                <tr>
                    <td><a href="view-invoice.php?inv=<?php echo intval($r['InvoiceNumber']); ?>">#<?php echo intval($r['InvoiceNumber']); ?></a></td>
                    <td><?php echo e($r['CustomerName']); ?></td>
                    <td><?php echo e($r['ProductName']); ?></td>
                    <td><?php echo intval($r['Quantity']); ?></td>
                    <td>Rs. <?php echo number_format($r['ProductPrice'], 2); ?></td>
                    <td>Rs. <?php echo number_format($r['Total'], 2); ?></td>
                    <td><?php echo e($r['PaymentMode']); ?></td>
                    <td><?php echo date('d M Y', strtotime($r['InvoiceGenDate'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
