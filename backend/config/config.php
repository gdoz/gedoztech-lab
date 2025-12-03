<?php

/**
 * Simple .env loader:
 * - Supports KEY=VALUE
 * - Ignores empty lines and lines starting with # or ;
 * - Does not interpret special characters
 * - Optionally strips surrounding quotes ("..." or '...')
 */
function load_env(string $path): array
{
    if (!is_readable($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if ($line === '' || $line[0] === '#' || $line[0] === ';') {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue; // invalid line
        }

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));

        if ($val === '') {
            $env[$key] = '';
            continue;
        }

        // Strip surrounding quotes if present
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))
        ) {
            $val = substr($val, 1, -1);
        }

        $env[$key] = $val;
    }

    return $env;
}

function env_or_default(array $env, string $key, string $default = ''): string
{
    return isset($env[$key]) && $env[$key] !== '' ? (string) $env[$key] : $default;
}

// Load the .env file using the parser.
$env = load_env(__DIR__ . '/.env');

// Token: there is no default; if there is no VISIT_TOKEN in the .env file, it will be null.
$visitToken = $env['VISIT_TOKEN'] ?? null;

return [
    'db' => [
        'host'    => env_or_default($env, 'DB_HOST', 'localhost'),
        'name'    => env_or_default($env, 'DB_NAME', 'db_name'),
        'user'    => env_or_default($env, 'DB_USER', 'db_user'),
        'pass'    => env_or_default($env, 'DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    // Shared token between front and back.
    'visit_token'    => $visitToken,
    // Domain allowed for Origin/Referer check
    'allowed_origin' => env_or_default($env, 'ALLOWED_ORIGIN', 'http://this_is_a_wrong_domain'),
];
