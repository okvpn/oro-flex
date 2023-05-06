<?php

namespace Oro\Bundle\CronBundle\Command;

interface CronCommandInterface
{
    /**
     * Define default cron schedule definition for a command. If null command is not active
     * Example: "5 * * * *"
     *
     * @see    \Oro\Bundle\CronBundle\Entity\Schedule::setDefinition()
     * @return string|null
     */
    public static function getDefaultDefinition(): ?string;
}
