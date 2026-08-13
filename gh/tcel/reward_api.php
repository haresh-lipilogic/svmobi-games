<?php
// Reward postback "standard API" — the game platform (Umogames) is expected to call this
// after a winner is determined, with the winner's MSISDN + prize details. We validate the
// caller, dispatch to the matching telco charging backend (only telecel_gh -> Rancard exists
// today), store the request + result, and reply with a status.
//
// IMPORTANT: the request/response field names below are OUR OWN DESIGN. No spec has been
// shared by Umogames yet — expect to rename fields once they confirm their real payload.

error_reporting(0);

include __DIR__ . '/dbdetail.php';
include __DIR__ . '/rancard_config.php';
include __DIR__ . '/rancard_api.php';

header('Content-Type: application/json');

function reward_respond(int $httpCode, array $body): void
{
	http_response_code($httpCode);
	echo json_encode($body);
	exit;
}

function reward_store_transaction(mysqli $conn1, string $dblog, array $row): void
{
	$sql = "INSERT INTO {$dblog}.reward_transactions
		(accesstime, transaction_id, cp_transaction_id, op_transaction_id, msisdn, coins, prize_amount, operator, gzp_puid, request_payload, rancard_result, rancard_rejected_item, status, caller_ip)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

	$stmt = $conn1->prepare($sql);
	if (!$stmt) {
		return;
	}
	$stmt->bind_param(
		'ssssssssssssss',
		$row['timestamp'],
		$row['transaction_id'],
		$row['cp_transaction_id'],
		$row['op_transaction_id'],
		$row['msisdn'],
		$row['coins'],
		$row['prize_amount'],
		$row['operator'],
		$row['gzp_puid'],
		$row['request_payload'],
		$row['rancard_result'],
		$row['rancard_rejected_item'],
		$row['status'],
		$row['caller_ip']
	);
	$stmt->execute();
	$stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	reward_respond(405, ['status' => 'error', 'message' => 'POST required']);
}

// Shared-secret check — this endpoint can trigger a real charge, so it must not be callable
// by anyone who just finds the URL. Key lives in .env, to be handed to Umogames once they integrate.
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$expectedKey = getenv('REWARD_API_KEY') ?: '';
if ($expectedKey === '' || !hash_equals($expectedKey, $providedKey)) {
	reward_respond(401, ['status' => 'error', 'message' => 'Invalid or missing API key']);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
	reward_respond(400, ['status' => 'error', 'message' => 'Invalid JSON body']);
}

$msisdn      = isset($payload['msisdn']) ? (string)$payload['msisdn'] : '';
$coins       = isset($payload['coins']) ? (string)$payload['coins'] : '';
$prizeAmount = isset($payload['prize_amount']) ? (string)$payload['prize_amount'] : '';
$operator    = isset($payload['operator']) ? (string)$payload['operator'] : '';
$gzpPuid     = isset($payload['gzp_puid']) ? (string)$payload['gzp_puid'] : '';

$missing = [];
foreach (['msisdn' => $msisdn, 'prize_amount' => $prizeAmount, 'operator' => $operator] as $field => $value) {
	if ($value === '') {
		$missing[] = $field;
	}
}

if (!empty($missing)) {
	reward_respond(400, ['status' => 'error', 'message' => 'Missing required field(s): ' . implode(', ', $missing)]);
}

$callerIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

// transaction_id is NOT supplied by the caller — we mint it ourselves so every reward
// attempt has an identifier regardless of what (if anything) Umogames sends. Same
// clickid-style generator used elsewhere in this codebase (index.php, charge_test.php).
$mt            = microtime(true) * 1000;
$transactionId = 't' . ((string)($mt * 10)) . rand(1, 999);
$timestamp     = date('Y-m-d H:i:s');

// Dispatch by operator. Only telecel_gh is wired up today; add further cases here as more
// operators/countries get their own charging backend — no invented backends beyond this one.
if ($operator !== 'telecel_gh') {
	reward_store_transaction($conn1, $dblog, [
		'timestamp'             => $timestamp,
		'transaction_id'        => $transactionId,
		'cp_transaction_id'     => $transactionId,
		'op_transaction_id'     => '',
		'msisdn'                => $msisdn,
		'coins'                 => $coins,
		'prize_amount'          => $prizeAmount,
		'operator'              => $operator,
		'gzp_puid'              => $gzpPuid,
		'request_payload'       => $rawBody,
		'rancard_result'        => '',
		'rancard_rejected_item' => '',
		'status'                => 'error',
		'caller_ip'             => $callerIp,
	]);
	reward_respond(400, ['status' => 'error', 'transaction_id' => $transactionId, 'timestamp' => $timestamp, 'message' => "Unsupported operator: {$operator}"]);
}

// Reuse the same transactionId as Rancard's cp_transaction_id — no need for a second,
// functionally-identical generated id now that both are minted by us.
$cpTransactionId = $transactionId;

$result = rancard_send_request(
	$rancard_endpoint,
	$rancard_cp_id,
	$cpTransactionId,
	RANCARD_ACTION_CREDIT,
	$msisdn,
	$prizeAmount
);

$status = $result['parse_error'] ? 'error' : ($result['success'] ? 'success' : 'failed');
// echo '<br>';print_r($result);echo '<br>';

reward_store_transaction($conn1, $dblog, [
	'timestamp'             => $timestamp,
	'transaction_id'        => $transactionId,
	'cp_transaction_id'     => $cpTransactionId,
	'op_transaction_id'     => $result['op_transaction_id'] ?? '',
	'msisdn'                => $msisdn,
	'coins'                 => $coins,
	'prize_amount'          => $prizeAmount,
	'operator'              => $operator,
	'gzp_puid'              => $gzpPuid,
	'request_payload'       => $rawBody,
	'rancard_result'        => $result['result'] ?? '',
	'rancard_rejected_item' => $result['rejected_item'] ?? '',
	'status'                => $status,
	'caller_ip'             => $callerIp,
]);

$httpCode = $status === 'success' ? 200 : ($status === 'failed' ? 402 : 502);

reward_respond($httpCode, [
	'status'             => $status,
	'timestamp'          => $timestamp,
	'transaction_id'     => $transactionId,
	'cp_transaction_id'  => $cpTransactionId,
	'op_transaction_id'  => $result['op_transaction_id'] ?? '',
	'result_code'        => $result['result'] ?? null,
	'message'            => $status === 'success' ? 'Credited' : ($result['rejected_item'] ?: 'Charge failed'),
]);
