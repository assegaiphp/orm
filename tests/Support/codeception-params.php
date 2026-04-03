<?php

$root = dirname(__DIR__, 2);

$loadEnv = static function (string $path): array {
    if (!is_file($path)) {
        return [];
    }

    $params = parse_ini_file($path, false, INI_SCANNER_RAW);

    return is_array($params) ? $params : [];
};

return array_replace(
    $loadEnv($root . '/.env.example'),
    $loadEnv($root . '/.env'),
);
