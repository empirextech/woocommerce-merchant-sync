<?php
/**
 * Plugin Name: EMPIREX Merchant Sync
 * Description: WooCommerce catalog push with incremental watermark, exclusive lock, and circuit breaker. Original EMPIREX Tech plugin.
 * Version: 1.0.0
 * Requires PHP: 8.3
 * Author: EMPIREX Tech
 * Author URI: https://empirex.tech
 * License: GPL-2.0-or-later
 * Text Domain: empirex-merchant-sync
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('EMPIREX_MERCHANT_SYNC_DIR', plugin_dir_path(__FILE__));

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'EmpireX\\MerchantSync\\')) {
        return;
    }
    $path = EMPIREX_MERCHANT_SYNC_DIR . 'includes/' . str_replace('\\', '/', substr($class, strlen('EmpireX\\MerchantSync\\'))) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

add_action('plugins_loaded', static function (): void {
    if (!class_exists('WooCommerce')) {
        return;
    }
    \EmpireX\MerchantSync\Plugin::boot();
});
