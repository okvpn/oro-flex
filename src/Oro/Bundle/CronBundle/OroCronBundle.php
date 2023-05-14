<?php

namespace Oro\Bundle\CronBundle;

use Oro\Bundle\CronBundle\Tools\CommandRunner;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The CronBundle bundle class.
 */
class OroCronBundle extends Bundle
{
    public function __construct(KernelInterface $kernel)
    {
        CommandRunner::setConsoleBin($kernel->getProjectDir() . '/bin/console');
    }
}
