<?php


$db="svmobigamesreport";

$conn = new mysqli('127.0.0.1','webserveruser','K&dN&r4a8N@du0');
$conn1 = new mysqli('127.0.0.1','webserveruser','K&dN&r4a8N@du0');

if (version_compare(phpversion(), '5.4.0', '<')) {
    if (session_id() == '') {
	session_start();
    }
} else {
    if (session_status() == PHP_SESSION_NONE) {
	session_start();
    }
}
/*if (!isset($_SESSION['svmobiadmin'])) {
    header("location:login.php");
}*/
