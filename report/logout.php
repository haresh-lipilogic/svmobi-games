<?php
// 1. Start the session
session_start();

// 2. Unset all session variables (optional but recommended)
$_SESSION = array();

// If a specific session variable needs to be unset:
// unset($_SESSION['svmobiadmin']); 

// 3. Destroy the session
session_destroy();

// Optionally, redirect the user after destroying the session
header("Location: login.php");
exit();
?>