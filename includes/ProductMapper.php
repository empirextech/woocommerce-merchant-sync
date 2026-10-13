<?php

declare(strict_types=1);

namespace EmpireX\MerchantSync;

final class ProductMapper
{
    /**
     * @param array<string, mixed> $product
     * @return array<string, mixed>|null
     */
    public function map(array $product): ?array
    {
        $sku = strtoupper(trim((string) ($product['sku'] ?? '')));
        $title = trim((string) ($product['title'] ?? ''));
        if ($sku === '' || $title === '') {
            return null;
        }

        $price = number_format((float) ($product['price'] ?? 0), 2, '.', '');

        return [
            'offerId' => $sku,
            'title' => mb_substr($title, 0, 150),
            'price' => ['value' => $price, 'currency' => (string) ($product['currency'] ?? 'EUR')],
            'availability' => ((int) ($product['stock'] ?? 0)) > 0 ? 'in_stock' : 'out_of_stock',
            'updated' => (string) ($product['updated'] ?? ''),
        ];
    }
}
