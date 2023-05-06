<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Loader;

use Doctrine\Persistence\ManagerRegistry;
use Okvpn\Bundle\CronBundle\Loader\ScheduleLoaderInterface;
use Okvpn\Bundle\CronBundle\Model\ArgumentsStamp;
use Okvpn\Bundle\CronBundle\Model\ScheduleStamp;
use Oro\Bundle\CronBundle\Entity\Schedule;
use Oro\Bundle\CronBundle\Model\OverwriteCronStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ScheduleTableLoader
{
    public function __construct(
        protected ManagerRegistry $registry,
        protected ScheduleLoaderInterface $loader,
        protected CacheInterface $cache,
    ) {
    }

    public function refreshTable(array $options = [], LoggerInterface $logger = null): void
    {
        if ($this->cache instanceof TagAwareAdapterInterface) {
            $this->cache->invalidateTags(['cron']);
        }

        $schedules = $this->registry->getRepository(Schedule::class)->findAll();
        $scheduleMap = [];
        foreach ($schedules as $schedule) {
            $scheduleMap[$schedule->getArgumentsHash()] = $schedule;
        }

        $exists = [];
        $em = $this->registry->getManager();
        foreach ($this->loader->getSchedules($options) as $envelope) {
            $cron = $envelope->get(OverwriteCronStamp::class)?->cronExpression() ?:
                $envelope->get(ScheduleStamp::class)?->cronExpression();

            $args = $envelope->get(ArgumentsStamp::class)?->getArguments() ?: [];
            $hash = Schedule::calculateHash($envelope->getCommand(), $args);

            if (!isset($scheduleMap[$hash])) {
                $scheduleMap[$hash] = $schedule = new Schedule();
                $schedule->setDefinition($cron)
                    ->setArguments($args)
                    ->setCommand($envelope->getCommand());
                $em->persist($schedule);
                $this->notify($logger, 'created', $schedule);
            } else {
                $schedule = $scheduleMap[$hash];
                if ($cron !== $schedule->getDefinition()) {
                    $schedule->setDefinition($cron);
                    $this->notify($logger, 'updated', $schedule);
                }
            }

            $status = $cron ? 'active' : 'inactive';
            $exists[$hash] = $schedule;
            if ($status !== $schedule->getStatus() && $schedule->getStatus()) {
                $this->notify($logger, $status, $schedule);
            }

            $schedule->setStatus($status);
        }

        foreach ($scheduleMap as $hash => $schedule) {
            if (!isset($exists[$hash]) && $schedule->getStatus() !== 'removed') {
                $schedule->setStatus('removed');
                $this->notify($logger, 'removed', $schedule);
            }
        }

        $em->flush();
    }

    protected function notify(?LoggerInterface $logger, $action, Schedule $schedule)
    {
        $logger?->info(sprintf(
            '>>> cron schedule [%s] %s %s - %s',
            $schedule->getDefinition(),
            $schedule->getCommand(),
            implode(' ', $schedule->getArguments()),
            $action
        ));
    }
}
