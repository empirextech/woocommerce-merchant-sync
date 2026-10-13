<?php

declare(strict_types=1);

namespace EmpireX\MerchantSync;

final class SyncEngine
{
    /** @var array<string, array<string, mixed>> */
    private array $channel = [];

    public function __construct(
        private readonly ProductMapper $mapper,
        private readonly CircuitBreaker $breaker,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $catalog
     * @return array{pushed:int,skipped:int,watermark:string}
     */
    public function incremental(array $catalog, string $cursor): array
    {
        $pushed = 0;
        $skipped = 0;
        $watermark = $cursor;

        foreach ($catalog as $product) {
            if (!$this->breaker->allow()) {
                break;
            }
            $updated = (string) ($product['updated'] ?? '');
            if ($updated !== '' && $updated <= $cursor) {
                $skipped++;
                continue;
            }
            $payload = $this->mapper->map($product);
            if ($payload === null) {
                $skipped++;
                continue;
            }
            $this->channel[$payload['offerId']] = $payload;
            $this->breaker->success();
            $pushed++;
            if ($updated > $watermark) {
                $watermark = $updated;
            }
        }

        return ['pushed' => $pushed, 'skipped' => $skipped, 'watermark' => $watermark];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function offer(string $sku): ?array
    {
        return $this->channel[$sku] ?? null;
    }
}
