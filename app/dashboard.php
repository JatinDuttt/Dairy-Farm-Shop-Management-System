<?php
include('includes/config.php');
require_admin();

$stats = [
    'Products' => ['value' => 0, 'class' => 'stat-blue'],
    'Categories' => ['value' => 0, 'class' => 'stat-green'],
    'Orders' => ['value' => 0, 'class' => 'stat-amber'],
    'Companies' => ['value' => 0, 'class' => 'stat-purple'],
];

$queries = [
    'Products' => "SELECT COUNT(*) AS count FROM tblproducts",
    'Categories' => "SELECT COUNT(*) AS count FROM tblcategory",
    'Orders' => "SELECT COUNT(*) AS count FROM tblorders",
    'Companies' => "SELECT COUNT(*) AS count FROM tblcompany",
];

foreach ($queries as $label => $sql) {
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stats[$label]['value'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042606">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <h2 class="page-title">Dashboard</h2>
    <div class="stats-grid">
        <?php foreach ($stats as $label => $stat): ?>
        <div class="stat-card <?php echo e($stat['class']); ?>">
            <div class="stat-number"><?php echo e($stat['value']); ?></div>
            <div class="stat-label"><?php echo e($label); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="quick-links">
        <h3>Quick actions</h3>
        <div class="link-grid">
            <a href="add-product.php" class="quick-card">+ Add product</a>
            <a href="manage-categories.php" class="quick-card">Manage categories</a>
            <a href="manage-companies.php" class="quick-card">Manage companies</a>
            <a href="sales-report.php" class="quick-card">View orders</a>
            <a href="invoice.php" class="quick-card">New invoice</a>
        </div>
    </div>
</div>
</body>
</html>
