<?php

declare(strict_types=1);

namespace EmpireX\MerchantSync;

final class CircuitBreaker
{
    private int $failures = 0;

    public function __construct(
        private readonly int $threshold = 5,
    ) {
    }

    public function allow(): bool
    {
        return $this->failures < $this->threshold;
    }

    public function success(): void
    {
        $this->failures = 0;
    }

    public function failure(): void
    {
        $this->failures++;
    }
}
