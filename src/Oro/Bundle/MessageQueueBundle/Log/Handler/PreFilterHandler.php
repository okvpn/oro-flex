<?php

declare(strict_types=1);

namespace Oro\Bundle\MessageQueueBundle\Log\Handler;

use Oro\Bundle\MessageQueueBundle\Log\Formatter\DmesgConsoleFormatter;
use Oro\Component\MessageQueue\Log\ConsumerState;

class PreFilterHandler extends ConsoleHandler
{
    protected $levelChannelMap = [
        'app' => 100,
        'doctrine' => 110,
        'security' => 110,
        'translation' => 350,
        'php' => 210,
        'event' => 110,
    ];

    public function __construct(ConsumerState $consumerState)
    {
        parent::__construct($consumerState);

        $this->setFormatter($this->getDefaultFormatter());
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        if (isset($this->levelChannelMap[$record['channel']])
            && $record['level'] < $this->levelChannelMap[$record['channel']]
        ) {
            return false;
        }

        return parent::handle($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter()
    {
        return new DmesgConsoleFormatter(
            [
                'extension' => ['extra', 'extension'],
                'message'   => ['extra', 'message_body'],
            ]
        );
    }
}
