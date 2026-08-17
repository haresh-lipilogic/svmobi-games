# SVMobi Reward API — Integration Guide

**Status: DRAFT.** This is our own request/response contract, not one dictated by an external spec. Field names may be renamed once requirements are confirmed with the integrating platform.

## Overview

This API lets a game platform notify SVMobi when a subscriber has won a prize, so SVMobi can credit the prize to the subscriber's mobile wallet and report back the outcome.

Call this API once per winner/prize event. SVMobi will:
1. Validate the request.
2. Charge the subscriber's account for the given `operator`.
3. Store the request and result.
4. Respond with the outcome.

## Endpoint

```
POST https://svmobi.games/gh/tcel/reward_api.php
Content-Type: application/json
```

Only `POST` is supported. Any other method returns `405`.

## Authentication

Every request must include a shared-secret API key in a header:

```
X-Api-Key: <your assigned key>
```

The key will be provided separately (not in this document). Requests with a missing or incorrect key are rejected with `401` before any other processing happens.

## Request

Send a JSON body with the following fields:

| Field | Type | Required | Description |
|---|---|---|---|
| `msisdn` | string | Yes | The winning subscriber's mobile number to be credited. |
| `prize_amount` | string/number | Yes | The prize value to credit. |
| `operator` | string | Yes | Identifies which telco/country to charge. Currently only `telecel_gh` is supported — see [Supported Operators](#supported-operators). |
| `coins` | string/number | No | Coins/points associated with the win, for reference/reporting only — does not affect the charge. |
| `gzp_puid` | string | No | Optional correlation id, if you want to tie this event back to your own click/session id. |

Note: `transaction_id` is **not** a request field. SVMobi generates its own transaction id per request and returns it in the response — do not send one.

### Example request

```json
{
  "msisdn": "233201792287",
  "prize_amount": "50000",
  "operator": "telecel_gh",
  "coins": "100",
  "gzp_puid": "your-correlation-id"
}
```

## Response

All responses are JSON.

| Field | Type | Description |
|---|---|---|
| `status` | string | `success`, `failed`, or `error` — see [Status values](#status-values). |
| `timestamp` | string | Server timestamp of the request, `YYYY-MM-DD HH:MM:SS`. |
| `transaction_id` | string | SVMobi-generated id for this reward event. |
| `cp_transaction_id` | string | SVMobi-generated id used for the underlying charge — present when `operator` was recognized. |
| `op_transaction_id` | string | Reference id from the underlying charging platform, when available. |
| `result_code` | string/null | Underlying charging platform's result code, when available. |
| `message` | string | Human-readable outcome description. |

### Status values

| `status` | Meaning |
|---|---|
| `success` | The charge was completed successfully. |
| `failed` | The charge was attempted but did not succeed. |
| `error` | The request could not be processed (validation failure, unsupported operator, or an error communicating with the charging platform). |

### HTTP status codes

| HTTP code | Meaning |
|---|---|
| `200` | Success — subscriber was credited. |
| `400` | Bad request — missing required field(s), or `operator` is not supported. |
| `401` | Missing or invalid `X-Api-Key`. |
| `402` | Charge attempted but failed. |
| `405` | Method not allowed — use `POST`. |
| `502` | Error communicating with the underlying charging platform. |

### Example: successful response

```json
{
  "status": "success",
  "timestamp": "2026-08-13 15:07:24",
  "transaction_id": "t17866138446049470",
  "cp_transaction_id": "t17866138446049470",
  "op_transaction_id": "9966b931-618c-4396-80a5-72190da3bbfa",
  "result_code": "0",
  "message": "Credited"
}
```

### Example: unsupported operator

```json
{
  "status": "error",
  "transaction_id": "t17866138446049470",
  "timestamp": "2026-08-13 15:07:24",
  "message": "Unsupported operator: some_other_operator"
}
```

## Supported Operators

| `operator` value | Country / Telco |
|---|---|
| `telecel_gh` | Ghana — Telecel |

More operators will be added here as they're onboarded. Sending any other value returns a `400` with an "Unsupported operator" message; no charge is attempted.

---
*This document describes a draft integration contract and is subject to change.*
