<?php
error_reporting(0);
date_default_timezone_set("Asia/Kolkata");

include "dbdetail.php";

$date  = date("Y-m-d H:i:s");
$date1 = date("Y-m-d");

$ip           = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$referrer     = $_SERVER['HTTP_REFERER'] ?? '';
$useragent    = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$xforwardwith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
$pageurl      = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Strip CR/LF so a crafted hashuser can't inject extra headers into the redirect
$hashuser      = preg_replace('/[\r\n]/', '', $_GET['hashuser'] ?? '');
$advertclickid = $hashuser;

// Unique click id
$mt      = microtime(true) * 1000; // microsecs
$clickid = "pk" . ((string)($mt * 10)) . rand(1, 999);

$msisdn = $operator = $pubid = '';
$advertiserid = 1;
$serviceid    = '10917';

// Date-wise file log (one .txt per day), kept independent of the DB call
// so a DB outage never blocks tracking.
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
	mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/' . $date1 . '.txt';
$logLine = implode(' | ', [$date, $ip, $hashuser, $clickid, $referrer, $useragent, $xforwardwith, $pageurl]) . PHP_EOL;
if ($fp = @fopen($logFile, 'a')) {
	if (flock($fp, LOCK_EX)) {
		fwrite($fp, $logLine);
		flock($fp, LOCK_UN);
	}
	fclose($fp);
}

// DB log (values escaped — referrer/useragent/hashuser are attacker-controlled)
$insert_userlog = sprintf(
	"CALL %s.insert_userlog('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
	$dblog,
	$conn1->real_escape_string($date),
	$conn1->real_escape_string($msisdn),
	$conn1->real_escape_string($operator),
	$conn1->real_escape_string($referrer),
	$conn1->real_escape_string($clickid),
	$conn1->real_escape_string($pubid),
	$conn1->real_escape_string($advertiserid),
	$conn1->real_escape_string($ip),
	$conn1->real_escape_string($advertclickid),
	$conn1->real_escape_string($useragent),
	$conn1->real_escape_string($xforwardwith),
	$conn1->real_escape_string($serviceid),
	$conn1->real_escape_string($pageurl)
);
$res_userlog = $conn1->query($insert_userlog);

header('Location: https://11070.enterprise.gamezop.com/?gzp-puid=' . urlencode($hashuser));
exit;
