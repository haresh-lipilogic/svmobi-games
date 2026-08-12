<?php
// Manual test entry point for the Rancard credit charge integration.
// Not linked from index.php / not wired to any real reward trigger yet -
// run this directly (browser or `php charge_test.php`) once rancard_config.php
// has a real cp_id and a UAT test MSISDN from Rancard/Telecel.

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

include __DIR__ . '/dbdetail.php';
include __DIR__ . '/rancard_config.php';
include __DIR__ . '/rancard_api.php';

if ($rancard_cp_id === '') {
	echo "rancard_config.php: \$rancard_cp_id is not set yet. Fill it in before testing.\n";
	exit;
}

// Same clickid-style transaction id generator used in index.php
$mt              = microtime(true) * 1000;
$cpTransactionId = 't' . ((string)($mt * 10)) . rand(1, 999);

$result = rancard_send_request(
	$rancard_endpoint,
	$rancard_cp_id,
	$cpTransactionId,
	RANCARD_ACTION_CREDIT,
	$rancard_test_msisdn,
	$rancard_test_transaction_price
);

if ($result['parse_error']) {
	echo "Request failed before a valid reply was parsed.\n";
	echo "HTTP code: {$result['http_code']}\n";
	echo "cURL error: {$result['curl_error']}\n";
} elseif ($result['success']) {
	echo "OK - credit request succeeded. op_transaction_id={$result['op_transaction_id']}\n";
} else {
	echo "Charge failed. result={$result['result']} rejected_item={$result['rejected_item']}\n";
}

echo "\n--- Request XML ---\n" . $result['request_xml'] . "\n";
echo "\n--- Raw response ---\n" . $result['raw_response'] . "\n";
