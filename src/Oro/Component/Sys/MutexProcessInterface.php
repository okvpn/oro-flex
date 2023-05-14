<?php

declare(strict_types=1);

namespace Oro\Component\Sys;

interface MutexProcessInterface
{
    /**
     * Lock mutex
     *
     * @param string|int $processName
     * @return bool
     */
    public function lock(string|int $processName): bool;

    /**
     * Unlock mutex
     *
     * @return void
     */
    public function unlock(): void;
}
