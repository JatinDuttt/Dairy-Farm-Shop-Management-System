<?php
include('includes/config.php');
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_name'])) {
    if (!verify_csrf_token()) {
        $error = "Security validation failed. Please try again.";
    } else {
        $name = sanitize_input($_POST['company_name'] ?? '');

        if (!validate_string_length($name, 2, 150)) {
            $error = "Company name must be 2-150 characters.";
        } else {
            $stmt = $con->prepare("INSERT INTO tblcompany (CompanyName) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                header("Location: manage-companies.php?msg=added");
                exit();
            }
            $error = "Unable to add company. Please try again.";
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (verify_csrf_token()) {
        $id = get_validated_int('delete', 0);
        if ($id > 0) {
            $stmt = $con->prepare("DELETE FROM tblcompany WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            header("Location: manage-companies.php?msg=deleted");
            exit();
        }
    }
    $error = "Could not delete company. Please try again.";
}

$stmt = $con->prepare("SELECT id, CompanyName, PostingDate FROM tblcompany ORDER BY PostingDate DESC");
$stmt->execute();
$companies = $stmt->get_result();
$flash = get_flash_message('Company');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Companies - DFSMS</title>
<link rel="stylesheet" href="css/style.css?v=2026042606">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="main-content">
    <h2 class="page-title">Companies</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($flash): ?><div class="alert alert-success"><?php echo e($flash); ?></div><?php endif; ?>
    <div class="form-card form-card-wide">
        <h3>Add new company</h3>
        <form method="POST" class="inline-entry-form">
            <?php echo csrf_token_field(); ?>
            <div class="form-group">
                <label>Company name</label>
                <input type="text" name="company_name" placeholder="e.g. Amul" maxlength="150" required>
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>#</th><th>Company name</th><th>Date added</th><th>Actions</th></tr></thead>
            <tbody>
                <?php $i=1; while ($r = $companies->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo e($r['CompanyName']); ?></td>
                    <td><?php echo date('d M Y', strtotime($r['PostingDate'])); ?></td>
                    <td>
                        <form method="POST" class="inline-form" onsubmit="return confirm('Delete company?')">
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
