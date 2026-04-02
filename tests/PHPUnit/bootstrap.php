<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

spl_autoload_register(function (string $classname): void {
    $prefix = 'Unit\\mocks\\';

    if (!str_starts_with($classname, $prefix)) {
        return;
    }

    $shortName = substr($classname, strlen($prefix));
    $path = dirname(__DIR__) . '/Unit/mocks/' . $shortName . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

spl_autoload_register(function (string $classname): void {
    $prefix = 'Tests\\PHPUnit\\Support\\';

    if (!str_starts_with($classname, $prefix)) {
        return;
    }

    $shortName = substr($classname, strlen($prefix));
    $path = __DIR__ . '/Support/' . str_replace('\\', '/', $shortName) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});
