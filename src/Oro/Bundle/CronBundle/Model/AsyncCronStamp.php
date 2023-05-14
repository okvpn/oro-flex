<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Model;

use Okvpn\Bundle\CronBundle\Model\CommandStamp;

class AsyncCronStamp implements CommandStamp
{
    public function __construct(private int|array|null $randomDelay = null, private bool $lock = false)
    {
    }

    public function getRandomDelay(): int|array|null
    {
        return $this->randomDelay;
    }

    public function isLock(): bool
    {
        return $this->lock;
    }
}
