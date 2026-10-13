<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'EmpireX\\MerchantSync\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = dirname(__DIR__) . '/includes/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});
