<?php
include('includes/config.php');
require_admin();

$id = get_validated_int('id', 0, 1);
$error = '';

if ($id <= 0) {
    header("Location: manage-products.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Security validation failed. Please try again.";
    } else {
        $name  = sanitize_input($_POST['product_name'] ?? '');
        $cat   = sanitize_input($_POST['category'] ?? '');
        $comp  = sanitize_input($_POST['company'] ?? '');
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

        if (!validate_string_length($name, 2, 150)) {
            $error = "Product name must be 2-150 characters.";
        } elseif (!validate_string_length($cat, 2, 150)) {
            $error = "Category must be valid.";
        } elseif (!validate_string_length($comp, 2, 150)) {
            $error = "Company must be valid.";
        } elseif (!validate_number($price, 0.01)) {
            $error = "Price must be greater than 0.";
        } else {
            $stmt = $con->prepare("UPDATE tblproducts SET ProductName = ?, CategoryName = ?, CompanyName = ?, ProductPrice = ? WHERE id = ?");
            $stmt->bind_param("sssdi", $name, $cat, $comp, $price, $id);
            if ($stmt->execute()) {
                log_event('INFO', 'Product updated by ' . $_SESSION['admin'], ['product_id' => $id]);
                header("Location: manage-products.php?msg=updated");
                exit();
            }
            $error = "Unable to update product. Please try again.";
            $stmt->close();
        }
    }
}

$stmt = $con->prepare("SELECT id, ProductName, CategoryName, CompanyName, ProductPrice FROM tblproducts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: manage-products.php");
    exit();
}

$stmt_cat = $con->prepare("SELECT DISTINCT CategoryName FROM tblcategory ORDER BY CategoryName");
$stmt_cat->execute();
$categories = $stmt_cat->get_result();

$stmt_comp = $con->prepare("SELECT DISTINCT CompanyName FROM tblcompany ORDER BY CompanyName");
$stmt_comp->execute();
$companies = $stmt_comp->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042606">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <h2 class="page-title">Edit product</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <div class="form-card">
        <form method="POST" id="edit-product-form">
            <?php echo csrf_token_field(); ?>
            <div class="form-group">
                <label>Product name</label>
                <input type="text" name="product_name" value="<?php echo e($product['ProductName']); ?>" maxlength="150" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <?php while ($r = $categories->fetch_assoc()): ?>
                        <option value="<?php echo e($r['CategoryName']); ?>" <?php echo $r['CategoryName'] === $product['CategoryName'] ? 'selected' : ''; ?>>
                            <?php echo e($r['CategoryName']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Company</label>
                <select name="company" required>
                    <?php while ($r = $companies->fetch_assoc()): ?>
                        <option value="<?php echo e($r['CompanyName']); ?>" <?php echo $r['CompanyName'] === $product['CompanyName'] ? 'selected' : ''; ?>>
                            <?php echo e($r['CompanyName']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price (INR)</label>
                <input type="number" name="price" min="0.01" step="0.01" value="<?php echo e($product['ProductPrice']); ?>" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update product</button>
                <a href="manage-products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
