# WooCommerce Merchant Sync

Incremental catalog push for Merchant Center-style channels.

**SC EMPIRE GLOBAL SOLUTIONS SRL** ships this pattern on large WooCommerce catalogs: enqueue in admin, hold an exclusive lock, walk only rows newer than a watermark, map to a channel payload, and stop if the remote API keeps failing.

Original EMPIREX Tech plugin. Not a client store, not Google OAuth credentials, not a 250k-SKU dump.

## Behaviour

```
Admin "Run incremental"
        │
     exclusive lock
        │
  products.updated > cursor  →  ProductMapper  →  in-memory channel
        │
   persist watermark
```

- Missing SKU/title rows are skipped.
- Circuit breaker stops the batch after repeated failures.
- Direct file access blocked; admin action is capability + nonce gated.

## Install

Copy to `wp-content/plugins/woocommerce-merchant-sync` and activate. WooCommerce → Merchant Sync.

```bash
composer install
composer test
```

## License

GPL-2.0-or-later (WordPress derivative). EMPIREX Tech / SC EMPIRE GLOBAL SOLUTIONS SRL.
