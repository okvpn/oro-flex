<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Model;

interface ActiveAwareCronInterface
{
    public function isActive(): bool;
}
