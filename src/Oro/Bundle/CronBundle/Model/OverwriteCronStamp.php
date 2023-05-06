<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Model;

use Okvpn\Bundle\CronBundle\Model\CommandStamp;

class OverwriteCronStamp implements CommandStamp
{
    public function __construct(protected ?string $cronExpression)
    {
    }

    /**
     * Get original cron expression.
     *
     * @return string|null
     */
    public function cronExpression(): ?string
    {
        return $this->cronExpression;
    }
}
