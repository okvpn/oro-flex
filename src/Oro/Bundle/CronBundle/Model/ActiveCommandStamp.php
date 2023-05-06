<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Model;

use Okvpn\Bundle\CronBundle\Model\CommandStamp;
use Oro\Bundle\CronBundle\Command\SynchronousCommandInterface;

class ActiveCommandStamp implements CommandStamp
{
    public function __construct(
        protected bool $enabled,
        protected ?string $classFQN = null,
        protected ?bool $isSynchronous = null
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isActiveAware(): bool
    {
        return $this->classFQN && class_exists($this->classFQN) && is_subclass_of($this->classFQN, ActiveAwareCronInterface::class);
    }

    public function isSynchronous(): bool
    {
        if (null === $this->isSynchronous) {
            return $this->classFQN && class_exists($this->classFQN) && is_subclass_of($this->classFQN, SynchronousCommandInterface::class);
        }

        return $this->isSynchronous;
    }

    public function getClass(): ?string
    {
        return $this->classFQN;
    }
}
