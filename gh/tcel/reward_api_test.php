<?php
// Manual test harness for reward_api.php — exercises the auth/validation/dispatch path
// end-to-end over HTTP (not a direct include) since reward_api.php reads php://input and
// HTTP headers. Run via CLI: php reward_api_test.php [base_url]
// Default base_url assumes XAMPP's default htdocs mapping.

error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/dbdetail.php';
include __DIR__ . '/rancard_config.php';

$baseUrl = $argv[1] ?? 'http://localhost/svmobi_games/gh/tcel/reward_api.php';
$realKey = getenv('REWARD_API_KEY') ?: '';

function reward_test_post(string $url, string $apiKey, array $payload): array
{
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL            => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 15,
		CURLOPT_CUSTOMREQUEST  => 'POST',
		CURLOPT_POSTFIELDS     => json_encode($payload),
		CURLOPT_HTTPHEADER     => [
			'Content-Type: application/json',
			'X-Api-Key: ' . $apiKey,
		],
	]);
	$body = curl_exec($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$error = curl_error($curl);
	curl_close($curl);

	return ['http_code' => $httpCode, 'body' => $body, 'error' => $error];
}

function reward_test_report(string $label, array $result): void
{
	echo "--- {$label} ---\n";
	echo "HTTP {$result['http_code']}\n";
	if ($result['error'] !== '') {
		echo "curl error: {$result['error']}\n";
	}
	echo $result['body'] . "\n\n";
}

// transaction_id is intentionally NOT sent — reward_api.php mints it itself and returns
// it in the response.
$validPayload = [
	'msisdn'       => '233201792287',
	'coins'        => '100',
	'prize_amount' => '50000',
	'operator'     => 'telecel_gh',
	'gzp_puid'     => 'test-gzp-puid',
];

// reward_test_report('Wrong API key (expect 401)', reward_test_post($baseUrl, 'wrong-key', $validPayload));

// $missingField = $validPayload;
// unset($missingField['msisdn']);
// reward_test_report('Missing msisdn (expect 400)', reward_test_post($baseUrl, $realKey, $missingField));

// $unsupportedOperator = $validPayload;
// $unsupportedOperator['operator'] = 'some_other_operator';
// reward_test_report('Unsupported operator (expect 400)', reward_test_post($baseUrl, $realKey, $unsupportedOperator));

reward_test_report('Valid telecel_gh request (expect 200/402/502 depending on Rancard)', reward_test_post($baseUrl, $realKey, $validPayload));
