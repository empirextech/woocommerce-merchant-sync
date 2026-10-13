<?php

declare(strict_types=1);

namespace EmpireX\MerchantSync;

final class Plugin
{
    public static function boot(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_post_empirex_merchant_sync_now', [self::class, 'run']);
    }

    public static function menu(): void
    {
        add_submenu_page(
            'woocommerce',
            'Merchant Sync',
            'Merchant Sync',
            'manage_woocommerce',
            'empirex-merchant-sync',
            [self::class, 'screen']
        );
    }

    public static function screen(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Forbidden', 'empirex-merchant-sync'));
        }
        $cursor = (string) get_option('empirex_merchant_sync_cursor', '1970-01-01 00:00:00');
        echo '<div class="wrap"><h1>EMPIREX Merchant Sync</h1>';
        echo '<p>Watermark: <code>' . esc_html($cursor) . '</code></p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('empirex_merchant_sync_now');
        echo '<input type="hidden" name="action" value="empirex_merchant_sync_now">';
        submit_button(__('Run incremental sync', 'empirex-merchant-sync'));
        echo '</form></div>';
    }

    public static function run(): void
    {
        if (!current_user_can('manage_woocommerce') || !wp_verify_nonce((string) ($_POST['_wpnonce'] ?? ''), 'empirex_merchant_sync_now')) {
            wp_die(esc_html__('Forbidden', 'empirex-merchant-sync'));
        }

        $lock = new Lock();
        if (!$lock->acquire('incremental')) {
            wp_safe_redirect(admin_url('admin.php?page=empirex-merchant-sync&busy=1'));
            exit;
        }

        try {
            $engine = new SyncEngine(new ProductMapper(), new CircuitBreaker());
            $result = $engine->incremental(self::catalogSnapshot(), (string) get_option('empirex_merchant_sync_cursor', '1970-01-01 00:00:00'));
            if ($result['watermark'] !== '') {
                update_option('empirex_merchant_sync_cursor', $result['watermark'], false);
            }
        } finally {
            $lock->release('incremental');
        }

        wp_safe_redirect(admin_url('admin.php?page=empirex-merchant-sync&done=1'));
        exit;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function catalogSnapshot(): array
    {
        if (!function_exists('wc_get_products')) {
            return [];
        }
        $products = wc_get_products(['limit' => 200, 'status' => 'publish']);
        $rows = [];
        foreach ($products as $product) {
            if (!is_object($product)) {
                continue;
            }
            $rows[] = [
                'sku' => method_exists($product, 'get_sku') ? (string) $product->get_sku() : '',
                'title' => method_exists($product, 'get_name') ? (string) $product->get_name() : '',
                'price' => method_exists($product, 'get_price') ? (string) $product->get_price() : '0',
                'stock' => method_exists($product, 'get_stock_quantity') ? (int) $product->get_stock_quantity() : 0,
                'updated' => method_exists($product, 'get_date_modified') && $product->get_date_modified()
                    ? $product->get_date_modified()->date('Y-m-d H:i:s')
                    : '',
            ];
        }

        return $rows;
    }
}
