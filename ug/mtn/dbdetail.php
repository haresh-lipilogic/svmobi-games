<?php
$dblog='svmobigames_ug_mtn_11167';
$advdb='advertiserdb';
//$mode='pit';
//$mode='staging';
$mode='production';
//$apikey='c540265bb10987d96870c7d2d1072051';
$apikey='d79a84811dde01a0e0221988ccc7fd95';
date_default_timezone_set("Asia/Kolkata");

$conn1 = new mysqli('127.0.0.1','webserveruser','K&dN&r4a8N@du0',$dblog,3307);
if ($conn1->connect_errno) {
    printf("Connect failed: %s\n", $conn1->connect_error);
    exit();
}

$connf = new PDO("mysql:host=127.0.0.1;port=3307;dbname=$dblog", 'webserveruser', 'K&dN&r4a8N@du0');

$enc_key = '9FgSGwgxniJiGwKT';
$enc_iv = '6160352252481856';

?>