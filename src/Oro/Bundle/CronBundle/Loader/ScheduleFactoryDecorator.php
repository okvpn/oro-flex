<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Loader;

use Doctrine\Persistence\ManagerRegistry;
use Okvpn\Bundle\CronBundle\Loader\ScheduleFactoryInterface;
use Okvpn\Bundle\CronBundle\Model\ScheduleEnvelope;
use Oro\Bundle\CronBundle\Entity\Schedule;
use Oro\Bundle\CronBundle\Model\ActiveCommandStamp;
use Oro\Bundle\CronBundle\Model\AsyncCronStamp;
use Oro\Bundle\CronBundle\Model\OverwriteCronStamp;
use Symfony\Contracts\Service\ResetInterface;

class ScheduleFactoryDecorator implements ScheduleFactoryInterface, ResetInterface
{
    /** @var Schedule[]  */
    protected $cachedSchedule = [];

    public function __construct(
        protected ScheduleFactoryInterface $inner,
        protected ManagerRegistry $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $config): ScheduleEnvelope
    {
        $stamps[] = new AsyncCronStamp();
        $this->cachedSchedule ??= $this->registry->getRepository(Schedule::class)->overwrittenSchedule();

        $hash = Schedule::calculateHash($config['command'] ?? null, $config['arguments'] ?? []);
        if ($overwrite = ($this->cachedSchedule[$hash] ?? null)) {
            if (!$overwrite->isEnabled()) {
                $config['disabled'] = true;
            }
            if ($overwrite->getOverwriteDefinition()) {
                $stamps[] = new OverwriteCronStamp($config['cron'] ?? null);
                $config['cron'] = $overwrite->getOverwriteDefinition();
            }
        }

        if (isset($config['cron'])) {
            $config['cron'] = str_replace('*/0', '*', $config['cron']);
        }

        if (isset($config['disabled']) || isset($config['classFQN']) || isset($config['synchronous'])) {
            $stamps[] = new ActiveCommandStamp(!($config['disabled'] ?? false), $config['classFQN'] ?? null, $config['synchronous'] ?? null);
        }

        $envelope = $this->inner->create($config);
        return $envelope->with(...$stamps);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->registry->getManager()->clear(Schedule::class);
        $this->cachedSchedule = null;
    }
}
