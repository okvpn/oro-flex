<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Tools;

use Oro\Bundle\CronBundle\Engine\CommandRunnerInterface;
use Oro\Bundle\CronBundle\Entity\Schedule;

class ScheduleExecutorFacade
{
    protected $commandRunner;

    public function __construct(CommandRunnerInterface $commandRunner)
    {
        $this->commandRunner = $commandRunner;
    }

    /**
     * @param Schedule $schedule
     */
    public function run(Schedule $schedule): void
    {
        $this->commandRunner->run(
            $schedule->getCommand(),
            $this->resolveOptions($schedule->getArguments())
        );
    }

    /**
     * Convert command arguments to options. It needed for correctly pass this arguments into ArrayInput:
     * new ArrayInput(['name' => 'foo', '--bar' => 'foobar']);
     *
     * @param array $commandOptions
     * @return array
     */
    protected function resolveOptions(array $commandOptions): array
    {
        $options = [];
        foreach ($commandOptions as $key => $option) {
            $params = explode('=', $option, 2);
            if (is_array($params) && count($params) === 2) {
                $options[$params[0]] = $params[1];
            } else {
                $options[$key] = $option;
            }
        }
        return $options;
    }
}
