<?php
// Minimal dependency-free .env loader (no composer/vlucas-phpdotenv in this codebase).
// Parses simple KEY=VALUE lines; supports #comments, blank lines, and single/double-quoted values.

function load_env(string $path): array
{
	static $loaded = [];
	if (isset($loaded[$path])) {
		return $loaded[$path];
	}

	$vars = [];
	if (is_file($path)) {
		foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
			$line = trim($line);
			if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
				continue;
			}
			list($key, $value) = explode('=', $line, 2);
			$key   = trim($key);
			$value = trim($value);
			$len   = strlen($value);
			if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"') || ($value[0] === "'" && $value[$len - 1] === "'"))) {
				$value = substr($value, 1, -1);
			}
			$vars[$key] = $value;
			if (getenv($key) === false) {
				putenv($key . '=' . $value);
			}
		}
	}

	$loaded[$path] = $vars;
	return $vars;
}
