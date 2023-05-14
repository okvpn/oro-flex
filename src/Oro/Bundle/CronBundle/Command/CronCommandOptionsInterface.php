<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Command;

interface CronCommandOptionsInterface extends CronCommandInterface
{
    /**
     * @return array
     */
    public static function getLoaderOptions(): array;
}
