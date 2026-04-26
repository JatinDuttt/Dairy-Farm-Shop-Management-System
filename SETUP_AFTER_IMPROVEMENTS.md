# Quick Start: After Security Improvements

## What's Changed?

The DFSMS project has been updated with critical security improvements:
- ✅ Bcrypt password hashing (replacing MD5)
- ✅ Prepared statements (preventing SQL injection)
- ✅ CSRF token protection
- ✅ Input validation and sanitization
- ✅ Security event logging
- ✅ Environment variable support

See [SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md) for detailed documentation.

---

## Setup Instructions

### Step 1: Create Logs Directory

```bash
mkdir -p app/logs
```

On Windows (PowerShell):
```powershell
New-Item -ItemType Directory -Force -Path app/logs
```

### Step 2: Set Environment Variables (Optional but Recommended)

**Option A: Using .env file (Local Development)**

1. Copy `.env.example` to `.env`
```bash
cp .env.example .env
```

2. Edit `.env` with your database credentials:
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=dfsms
APP_ENV=development
```

**Option B: System Environment Variables (Production)**

```bash
# Linux/Mac
export DB_HOST="localhost"
export DB_USER="root"
export DB_PASS="your_password"
export DB_NAME="dfsms"
export APP_ENV="production"

# Windows PowerShell
$env:DB_HOST="localhost"
$env:DB_USER="root"
$env:DB_PASS="your_password"
$env:DB_NAME="dfsms"
$env:APP_ENV="production"
```

### Step 3: Recreate Database (Important!)

The admin password hash has changed from MD5 to bcrypt. You must reimport the database schema:

```bash
# In phpmyadmin:
1. Select dfsms database
2. Go to Import tab
3. Select database/schema.sql
4. Click Go

# Or via command line:
mysql -u root dfsms < database/schema.sql
```

**Default login credentials (unchanged):**
- Username: `admin`
- Password: `admin123`

### Step 4: Verify Installation

1. Open http://localhost/dfsms/
2. Login with admin / admin123
3. Check that dashboard loads
4. Check app/logs/ directory for new log files

---

## Important Changes for Developers

### 1. All Forms Now Require CSRF Tokens

```php
<form method="POST">
    <?php echo csrf_token_field(); ?>
    <!-- form fields -->
</form>
```

### 2. All Database Queries Use Prepared Statements

```php
// CORRECT - Use prepared statements
$stmt = $con->prepare("SELECT * FROM tblproducts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// INCORRECT - Don't do this anymore
$result = mysqli_query($con, "SELECT * FROM tblproducts WHERE id='$id'");
```

### 3. Always Sanitize Output

```php
// CORRECT
echo htmlspecialchars($user_input);

// INCORRECT - Don't do this
echo $user_input;
```

### 4. Use Security Helper Functions

```php
// For input validation
if (!validate_string_length($name, 2, 150)) {
    $error = "Invalid length";
}

// For logging
log_event('INFO', 'User action', ['user' => $user]);

// For password operations
$hash = hash_password($plaintext);
if (verify_password($plaintext, $hash)) {
    // Password matches
}
```

---

## Migrating Other Pages

All remaining pages (manage-categories.php, manage-companies.php, etc.) should be updated to follow the same pattern. See `add-product.php` as an example of the improved pattern.

### Template for updates:
```php
<?php
include('includes/config.php');
if (!isset($_SESSION['admin'])) { header("Location: index.php"); exit(); }

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !verify_csrf_token()) {
    $error = "Security validation failed.";
}

// Use prepared statements
$stmt = $con->prepare("SELECT * FROM table WHERE condition = ?");
$stmt->bind_param("type", $param);
$stmt->execute();
$result = $stmt->get_result();
?>
```

---

## Testing the Improvements

### 1. Test Password Hashing
```php
// Generate a test hash
$password_hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
echo $password_hash;

// Verify it works
if (password_verify('admin123', $password_hash)) {
    echo "✓ Password verification works";
}
```

### 2. Test CSRF Protection
Try submitting a form from a different domain - it should fail.

### 3. Test SQL Injection Prevention
Try logging in with:
- Username: `admin' OR '1'='1`
- Password: anything

Should get "Invalid username or password" error.

### 4. Check Logs
```bash
tail -f app/logs/php_errors.log
```

---

## Troubleshooting

### Issue: Login fails with "Invalid username or password"
- **Cause:** Database may not be using the new bcrypt hash
- **Fix:** Re-import schema.sql, then login again

### Issue: "Security validation failed" on form submission
- **Cause:** CSRF token expired or invalid
- **Fix:** Refresh page and try again (sessions last 24 minutes by default)

### Issue: Logs directory not writable
- **Cause:** Permission issue
- **Fix:** 
  ```bash
  chmod 755 app/logs
  ```

### Issue: Database errors not showing
- **Cause:** APP_ENV=production hides errors
- **Fix:** Set APP_ENV=development to see details

---

## Next Steps

1. Apply same security pattern to all remaining pages
2. Set up rate limiting for login attempts
3. Configure HTTPS in production
4. Update Cypress tests with CSRF token support
5. Add two-factor authentication (optional)

See [SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md) for comprehensive documentation.
