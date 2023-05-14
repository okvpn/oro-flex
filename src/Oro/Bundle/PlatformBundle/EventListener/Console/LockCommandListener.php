<?php

declare(strict_types=1);

namespace Oro\Bundle\PlatformBundle\EventListener\Console;

use Oro\Component\Sys\MutexProcess;
use Oro\Component\Sys\MutexProcessInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class LockCommandListener
{
    private ?MutexProcessInterface $mutex = null;

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        if ($lockId = getenv("LOCK_SEM_ID")) {
            $this->mutex = $mutex = $this->createMutex();
            if (!$mutex->lock($lockId)) {
                $event->disableCommand();
            }
        }
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $this->mutex?->unlock();
    }

    protected function createMutex(): MutexProcessInterface
    {
        return new MutexProcess();
    }
}
