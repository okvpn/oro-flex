<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Loader;

use Okvpn\Bundle\CronBundle\Loader\ScheduleFactoryInterface;
use Okvpn\Bundle\CronBundle\Loader\ScheduleLoaderInterface;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\CronBundle\Command\CronCommandOptionsInterface;
use Oro\Bundle\CronBundle\Command\SynchronousCommandInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Service\ResetInterface;

class CommandCronLoader implements ScheduleLoaderInterface
{
    protected static $defaultGroups = ['command', 'command-async', 'command-synchronous'];

    public function __construct(
        protected ScheduleFactoryInterface $factory,
        protected KernelInterface $kernel,
        protected CacheInterface $cache
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedules(array $options = []): iterable
    {
        $groups = ($options['groups'] ?? null) ?: self::$defaultGroups;
        if (!array_intersect(self::$defaultGroups, $groups)) {
            return;
        }

        $commands = $this->cache->get('cron_commands', function (ItemInterface $item) use ($options) {
            $application = new Application($this->kernel);
            $cronCommands = $application->all('oro:cron');

            $all = [];
            foreach ($cronCommands as $name => $command) {
                if ($command instanceof CronCommandInterface) {
                    $all[$name] = get_class($command);
                }
            }

            try {
                $item->tag('cron');
            } catch (\LogicException $e) {}

            return $all;
        });

        if ($this->factory instanceof ResetInterface) {
            $this->factory->reset();
        }

        $isSyncGroup = array_intersect(['command', 'command-synchronous'], $groups);
        $isAsyncGroup = array_intersect(['command', 'command-async'], $groups);

        foreach ($commands as $name => $commandClass) {
            if (!class_exists($commandClass)) {
                continue;
            }

            $synchronous = is_subclass_of($commandClass, SynchronousCommandInterface::class);
            if (($synchronous && !$isSyncGroup) || (!$synchronous && !$isAsyncGroup)) {
                continue;
            }

            $task = [
                'command' => $name,
                'options' => $options,
                'cron' => $cron = $commandClass::getDefaultDefinition(),
                'classFQN' => $commandClass
            ];
            if (is_subclass_of($commandClass, CronCommandOptionsInterface::class)) {
                $task = array_merge_recursive($task, $commandClass::getLoaderOptions());
            }

            $task = array_filter($task) + [
                'disabled' => empty($cron)
            ];
            yield $this->factory->create($task);
        }
    }
}
