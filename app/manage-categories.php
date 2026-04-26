<?php
include('includes/config.php');
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_name'])) {
    if (!verify_csrf_token()) {
        $error = "Security validation failed. Please try again.";
    } else {
        $name = sanitize_input($_POST['cat_name'] ?? '');
        $code = strtoupper(sanitize_input($_POST['cat_code'] ?? ''));

        if (!validate_string_length($name, 2, 150)) {
            $error = "Category name must be 2-150 characters.";
        } elseif (!empty($code) && !validate_string_length($code, 2, 50)) {
            $error = "Category code must be 2-50 characters.";
        } else {
            $stmt = $con->prepare("INSERT INTO tblcategory (CategoryName, CategoryCode) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $code);
            if ($stmt->execute()) {
                header("Location: manage-categories.php?msg=added");
                exit();
            }
            $error = "Unable to add category. Please try again.";
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (verify_csrf_token()) {
        $id = get_validated_int('delete', 0);
        if ($id > 0) {
            $stmt = $con->prepare("DELETE FROM tblcategory WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            header("Location: manage-categories.php?msg=deleted");
            exit();
        }
    }
    $error = "Could not delete category. Please try again.";
}

$stmt = $con->prepare("SELECT id, CategoryName, CategoryCode, PostingDate FROM tblcategory ORDER BY PostingDate DESC");
$stmt->execute();
$cats = $stmt->get_result();
$flash = get_flash_message('Category');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042607">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <h2 class="page-title">Categories</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($flash): ?><div class="alert alert-success"><?php echo e($flash); ?></div><?php endif; ?>
    <div class="form-card form-card-wide">
        <h3>Add new category</h3>
        <form method="POST" class="inline-entry-form">
            <?php echo csrf_token_field(); ?>
            <div class="form-group">
                <label>Category name</label>
                <input type="text" name="cat_name" id="cat-name" placeholder="e.g. Milk Products" maxlength="150" required>
            </div>
            <div class="form-group">
                <label>Category code</label>
                <input type="text" name="cat_code" id="cat-code" placeholder="e.g. MILK" maxlength="50">
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>
    <div class="table-container">
        <table class="data-table" id="categories-table">
            <thead><tr><th>#</th><th>Category name</th><th>Code</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php $i=1; while ($r = $cats->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo e($r['CategoryName']); ?></td>
                    <td><?php echo e($r['CategoryCode']); ?></td>
                    <td><?php echo date('d M Y', strtotime($r['PostingDate'])); ?></td>
                    <td>
                        <form method="POST" class="inline-form" onsubmit="return confirm('Delete category?')">
                            <?php echo csrf_token_field(); ?>
                            <input type="hidden" name="delete" value="<?php echo intval($r['id']); ?>">
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
