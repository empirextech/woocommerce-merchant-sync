<?php

declare(strict_types=1);

namespace EmpireX\MerchantSync\Tests;

use EmpireX\MerchantSync\CircuitBreaker;
use EmpireX\MerchantSync\ProductMapper;
use EmpireX\MerchantSync\SyncEngine;
use PHPUnit\Framework\TestCase;

final class SyncEngineTest extends TestCase
{
    public function testSkipsUnchangedAndPushesNewer(): void
    {
        $engine = new SyncEngine(new ProductMapper(), new CircuitBreaker());
        $result = $engine->incremental([
            ['sku' => 'A1', 'title' => 'Old', 'price' => '10', 'stock' => 1, 'updated' => '2026-01-01 00:00:00'],
            ['sku' => 'B2', 'title' => 'New', 'price' => '12.5', 'stock' => 4, 'updated' => '2026-10-01 12:00:00'],
        ], '2026-06-01 00:00:00');

        self::assertSame(1, $result['pushed']);
        self::assertSame(1, $result['skipped']);
        self::assertSame('2026-10-01 12:00:00', $result['watermark']);
        self::assertSame('12.50', $engine->offer('B2')['price']['value']);
    }
}
