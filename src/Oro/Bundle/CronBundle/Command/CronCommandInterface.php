<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Command;

interface CronCommandInterface
{
    /**
     * Define default cron schedule definition for a command. If null command is not active
     * Example: "5 * * * *"
     *
     * @see    \Oro\Bundle\CronBundle\Entity\Schedule::setDefinition()
     * @return string|null If null - the command must be disabled
     */
    public static function getDefaultDefinition(): ?string;
}
