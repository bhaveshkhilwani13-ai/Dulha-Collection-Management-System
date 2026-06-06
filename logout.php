<?php
// logout.php - User (Customer) Logout
session_start();
// Only destroy customer session keys
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
// If admin was also logged in via same session, leave them
// Otherwise just destroy
if (!isset($_SESSION['user_id'])) {
    session_destroy();
}
header("Location: index.php");
exit();
?>
