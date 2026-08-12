<?php
require_once __DIR__ . '/env_loader.php';
load_env(__DIR__ . '/.env');

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: '';
$db_pass = getenv('DB_PASS') ?: '';
$db_port = (int)(getenv('DB_PORT') ?: 3306);

$conn1 = new mysqli($db_host, $db_user, $db_pass, null, $db_port);

$dblog  = getenv('DB_LOG_SCHEMA') ?: '';
$advdb  = getenv('ADV_DB') ?: 'advertiserdb';
$mode   = getenv('APP_MODE') ?: 'production';
$apikey = getenv('GAMEZOP_API_KEY') ?: '';

date_default_timezone_set("Asia/Kolkata");
if ($conn1->connect_errno) {
	printf("Connect failed: %s\n", $conn1->connect_error);
	exit();
}
