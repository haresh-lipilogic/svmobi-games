<?php
$conn1 = new mysqli('10.34.240.214','webserveruser','K&dN&r4a8N@du0');
$connf = new PDO("mysql:host=10.34.240.214;", 'webserveruser', 'K&dN&r4a8N@du0') or die(print_r($conn->error));

$dblog='svmobigames_Asaana_10885';
$advdb='advertiserdb';
//$mode='pit';
//$mode='staging';
$mode='production';
//$apikey='c540265bb10987d96870c7d2d1072051';
$apikey='d79a84811dde01a0e0221988ccc7fd95';
date_default_timezone_set("Asia/Kolkata");
if ($conn1->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}
?>