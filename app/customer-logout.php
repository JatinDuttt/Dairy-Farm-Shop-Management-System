<?php
session_start();
unset($_SESSION['customer'], $_SESSION['customerid']);
header("Location: customer-login.php");
exit();
?>
