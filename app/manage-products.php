<?php
include('includes/config.php');
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (verify_csrf_token()) {
        $id = get_validated_int('delete', 0);

        if ($id > 0) {
            $stmt = $con->prepare("DELETE FROM tblproducts WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    log_event('INFO', 'Product deleted by ' . $_SESSION['admin'], ['product_id' => $id]);
                    header("Location: manage-products.php?msg=deleted");
                    exit();
                }
                $stmt->close();
            }
        }
    }
    $error = "Could not delete product. Please try again.";
}

$stmt = $con->prepare("SELECT id, ProductName, CategoryName, CompanyName, ProductPrice, PostingDate FROM tblproducts ORDER BY PostingDate DESC");
$stmt->execute();
$products = $stmt->get_result();
$flash = get_flash_message('Product');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Products - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">Products</h2>
        <a href="add-product.php" class="btn btn-primary">+ Add product</a>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($flash): ?><div class="alert alert-success"><?php echo e($flash); ?></div><?php endif; ?>
    <div class="table-container">
        <table class="data-table" id="products-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product name</th>
                    <th>Category</th>
                    <th>Company</th>
                    <th>Price (INR)</th>
                    <th>Date added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo e($row['ProductName']); ?></td>
                    <td><?php echo e($row['CategoryName']); ?></td>
                    <td><?php echo e($row['CompanyName']); ?></td>
                    <td>Rs. <?php echo number_format($row['ProductPrice'], 2); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['PostingDate'])); ?></td>
                    <td class="action-cell">
                        <a href="edit-product.php?id=<?php echo intval($row['id']); ?>" class="btn btn-sm btn-edit">Edit</a>
                        <form method="POST" class="inline-form" onsubmit="return confirm('Delete this product?')">
                            <?php echo csrf_token_field(); ?>
                            <input type="hidden" name="delete" value="<?php echo intval($row['id']); ?>">
                            <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
