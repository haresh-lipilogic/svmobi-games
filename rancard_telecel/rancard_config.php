<?php
// Rancard - Telecel Charging Proxy API config
// Ref: "Rancard - Telecel Charging Proxy API Document - Credit Operation v2" (v1.5)
// Requires $mode to already be defined (set in dbdetail.php).

require_once __DIR__ . '/env_loader.php';
load_env(__DIR__ . '/.env');

// Falls back to APP_MODE directly so this file doesn't depend on dbdetail.php
// having run first to define $mode.
$mode = $mode ?? (getenv('APP_MODE') ?: 'production');

// Provided by Rancard to the top-up vendor. Case sensitive.
$rancard_cp_id = getenv('RANCARD_CP_ID') ?: '';

// Trailing "?" is required, not decorative: it matches the doc's literal URL and the
// verified-working Postman request. Rancard's servlet threw a NullPointerException in
// BillingProxy.processRequest when called without it — consistent with Java's
// HttpServletRequest.getQueryString() returning null (vs "") when the URL has no "?".
$rancard_endpoint_prod = 'https://topup.rancard.com/charge.rms/billingproxy?';
$rancard_endpoint_test = 'http://192.168.1.249:18888/charge.rms/billingproxy?';
$rancard_endpoint = ($mode === 'production') ? $rancard_endpoint_prod : $rancard_endpoint_test;

// Actions per the API doc
const RANCARD_ACTION_CREDIT = 1;
const RANCARD_ACTION_BALANCE = 5;
const RANCARD_ACTION_DATA_BUNDLE = 6;

// Static placeholder MSISDN for testing the API call in isolation (confirmed working via Postman).
// Real subscriber MSISDN capture is not wired up yet — replace before going live.
$rancard_test_msisdn = getenv('RANCARD_TEST_MSISDN') ?: '';

// Static placeholder amount (transaction_price / bundle_id) for testing.
$rancard_test_transaction_price = getenv('RANCARD_TEST_TRANSACTION_PRICE') ?: '';
