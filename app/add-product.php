<?php
include('includes/config.php');
require_admin();

$error = '';

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
            $stmt = $con->prepare("INSERT INTO tblproducts (ProductName, CategoryName, CompanyName, ProductPrice) VALUES (?, ?, ?, ?)");

            if ($stmt === false) {
                log_event('ERROR', 'Database prepare failed: ' . $con->error);
                $error = "An error occurred while saving. Please try again.";
            } else {
                $stmt->bind_param("sssd", $name, $cat, $comp, $price);

                if ($stmt->execute()) {
                    log_event('INFO', 'Product added by ' . $_SESSION['admin'], ['product' => $name, 'price' => $price]);
                    header("Location: manage-products.php?msg=added");
                    exit();
                }

                log_event('ERROR', 'Failed to insert product: ' . $stmt->error);
                $error = "Error saving product. Please try again.";
                $stmt->close();
            }
        }
    }
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
<title>Add Product - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042606">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <h2 class="page-title">Add product</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <div class="form-card">
        <form method="POST" id="add-product-form">
            <?php echo csrf_token_field(); ?>
            <div class="form-group">
                <label>Product name</label>
                <input type="text" name="product_name" id="product-name" placeholder="e.g. Full Cream Milk 1L" maxlength="150" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" id="category" required>
                    <option value="">-- Select category --</option>
                    <?php while ($r = $categories->fetch_assoc()): ?>
                        <option value="<?php echo e($r['CategoryName']); ?>"><?php echo e($r['CategoryName']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Company</label>
                <select name="company" id="company" required>
                    <option value="">-- Select company --</option>
                    <?php while ($r = $companies->fetch_assoc()): ?>
                        <option value="<?php echo e($r['CompanyName']); ?>"><?php echo e($r['CompanyName']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price (INR)</label>
                <input type="number" name="price" id="price" min="0.01" step="0.01" placeholder="0.00" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save product</button>
                <a href="manage-products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
