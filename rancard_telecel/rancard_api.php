<?php
// Rancard - Telecel Charging Proxy API client.
// Implements the cp_request / cp_reply wire protocol from
// "Rancard - Telecel Charging Proxy API Document - Credit Operation v2" (v1.5), Annex C.
// Requires rancard_config.php to already be included (for the RANCARD_ACTION_* constants).

function rancard_build_request_xml(string $cpId, string $cpTransactionId, int $action, string $msisdn = '', $transactionPrice = '', int $currency = 0): string
{
	$cpId            = htmlspecialchars($cpId, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	$cpTransactionId = htmlspecialchars($cpTransactionId, ENT_XML1 | ENT_QUOTES, 'UTF-8');

	// Rancard's billing servlet appears to parse the body somewhat naively (a single-line
	// request with no line breaks between tags triggered a NullPointerException on their
	// side), so this mirrors the exact multi-line layout shown in the API doc's samples.
	if ($action === RANCARD_ACTION_BALANCE) {
		return "<?xml version=\"1.0\"?><!DOCTYPE cp_request SYSTEM \"cp_req_websvr.dtd\">\n"
			. "<cp_request>\n"
			. "<cp_id>{$cpId}</cp_id>\n"
			. "<cp_transaction_id>{$cpTransactionId}</cp_transaction_id>\n"
			. "<action>{$action}</action>\n"
			. "</cp_request>";
	}

	$msisdn           = htmlspecialchars($msisdn, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	$transactionPrice = htmlspecialchars((string)$transactionPrice, ENT_XML1 | ENT_QUOTES, 'UTF-8');

	return "<?xml version=\"1.0\"?><!DOCTYPE cp_request SYSTEM \"cp_req_websvr.dtd\">\n"
		. "<cp_request>\n"
		. "<cp_id>{$cpId}</cp_id>\n"
		. "<cp_transaction_id>{$cpTransactionId}</cp_transaction_id>\n"
		. "<op_transaction_id></op_transaction_id>\n"
		. "<application>1</application>\n"
		. "<action>{$action}</action>\n"
		. "<user_id type=\"MSISDN\">{$msisdn}</user_id>\n"
		. "<cp_timer>2</cp_timer>\n"
		. "<transaction_price>{$transactionPrice}</transaction_price>\n"
		. "<transaction_currency>{$currency}</transaction_currency>\n"
		. "</cp_request>";
}

function rancard_post_xml(string $endpoint, string $xml): array
{
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL            => $endpoint,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING       => '',
		CURLOPT_MAXREDIRS      => 5,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_CUSTOMREQUEST  => 'POST',
		CURLOPT_POSTFIELDS     => $xml,
		CURLOPT_HTTPHEADER     => ['Content-Type: application/xml'],
	]);

	$response = curl_exec($curl);
	$error    = curl_error($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	return [
		'body'      => $response === false ? '' : $response,
		'error'     => $error,
		'http_code' => $httpCode,
	];
}

// Parses a cp_reply document. Deliberately does not pass LIBXML_DTDLOAD/LIBXML_DTDVALID
// so the external "cp_reply.dtd" referenced in the DOCTYPE is never fetched (XXE/SSRF guard);
// LIBXML_NONET is added as defense in depth against any entity resolution.
function rancard_parse_reply(string $xml): array
{
	$parsed = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
	if ($parsed === false) {
		return [
			'cp_id'             => '',
			'cp_transaction_id' => '',
			'op_transaction_id' => '',
			'result'            => null,
			'rejected_item'     => '',
			'success'           => false,
			'parse_error'       => true,
		];
	}

	$result = isset($parsed->result) ? (string)$parsed->result : null;

	return [
		'cp_id'             => (string)($parsed->cp_id ?? ''),
		'cp_transaction_id' => (string)($parsed->cp_transaction_id ?? ''),
		'op_transaction_id' => (string)($parsed->op_transaction_id ?? ''),
		'result'            => $result,
		'rejected_item'     => (string)($parsed->rejected_item ?? ''),
		'success'           => $result === '0',
		'parse_error'       => false,
	];
}

function rancard_log_charge(string $logDir, string $requestXml, array $httpResult, array $parsedReply): void
{
	if (!is_dir($logDir)) {
		mkdir($logDir, 0755, true);
	}
	$logFile = $logDir . '/charges-' . date('Y-m-d') . '.txt';
	$line = implode(' | ', [
		date('Y-m-d H:i:s'),
		'request=' . $requestXml,
		'http_code=' . $httpResult['http_code'],
		'curl_error=' . $httpResult['error'],
		'response=' . $httpResult['body'],
		'result=' . ($parsedReply['result'] ?? ''),
		'op_transaction_id=' . ($parsedReply['op_transaction_id'] ?? ''),
		'rejected_item=' . $parsedReply['rejected_item'],
	]) . PHP_EOL;

	if ($fp = @fopen($logFile, 'a')) {
		if (flock($fp, LOCK_EX)) {
			fwrite($fp, $line);
			flock($fp, LOCK_UN);
		}
		fclose($fp);
	}
}

// Sends a credit (action=1), balance/exposure check (action=5), or data bundle (action=6)
// request and returns the parsed result. Every attempt is logged to $logDir regardless
// of outcome so failures can be diagnosed without a working DB log for this yet.
function rancard_send_request(string $endpoint, string $cpId, string $cpTransactionId, int $action, string $msisdn = '', $transactionPrice = '', int $currency = 0, string $logDir = __DIR__ . '/logs'): array
{
	$requestXml = rancard_build_request_xml($cpId, $cpTransactionId, $action, $msisdn, $transactionPrice, $currency);
	$httpResult = rancard_post_xml($endpoint, $requestXml);

	if ($httpResult['body'] === '') {
		$parsedReply = [
			'cp_id'             => '',
			'cp_transaction_id' => $cpTransactionId,
			'op_transaction_id' => '',
			'result'            => null,
			'rejected_item'     => '',
			'success'           => false,
			'parse_error'       => true,
		];
	} else {
		$parsedReply = rancard_parse_reply($httpResult['body']);
	}

	rancard_log_charge($logDir, $requestXml, $httpResult, $parsedReply);

	return array_merge($parsedReply, [
		'http_code'    => $httpResult['http_code'],
		'curl_error'   => $httpResult['error'],
		'raw_response' => $httpResult['body'],
		'request_xml'  => $requestXml,
	]);
}
