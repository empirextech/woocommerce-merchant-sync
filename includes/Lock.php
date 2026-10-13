<?php

declare(strict_types=1);

namespace EmpireX\MerchantSync;

final class Lock
{
    /** @var array<string, int> */
    private array $memory = [];

    public function acquire(string $name, int $ttl = 300): bool
    {
        $now = time();
        if (isset($this->memory[$name]) && $this->memory[$name] > $now) {
            return false;
        }
        if (function_exists('get_transient')) {
            $key = 'empirex_ms_lock_' . $name;
            if (get_transient($key)) {
                return false;
            }
            return (bool) set_transient($key, $now + $ttl, $ttl);
        }
        $this->memory[$name] = $now + $ttl;

        return true;
    }

    public function release(string $name): void
    {
        unset($this->memory[$name]);
        if (function_exists('delete_transient')) {
            delete_transient('empirex_ms_lock_' . $name);
        }
    }
}
