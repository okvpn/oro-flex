<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Middleware;

use Okvpn\Bundle\CronBundle\Middleware\MiddlewareEngineInterface;
use Okvpn\Bundle\CronBundle\Middleware\StackInterface;
use Okvpn\Bundle\CronBundle\Model\ArgumentsStamp;
use Okvpn\Bundle\CronBundle\Model\ScheduleEnvelope;
use Oro\Bundle\CronBundle\Async\Topic\RunCommandTopic;
use Oro\Bundle\CronBundle\Model\ActiveCommandStamp;
use Oro\Bundle\CronBundle\Model\AsyncCronStamp;
use Oro\Bundle\CronBundle\Tools\CommandRunner;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\CronBundle\Model\EnvelopeTools as ET;

class AsyncCronMiddleware implements MiddlewareEngineInterface
{
    public function __construct(
        protected MessageProducerInterface $producer,
        protected string $environment,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ScheduleEnvelope $envelope, StackInterface $stack): ScheduleEnvelope
    {
        $synchronous = $envelope->get(ActiveCommandStamp::class)?->isSynchronous();

        $args = $envelope->get(ArgumentsStamp::class)?->getArguments() ?: [];
        if ($synchronous === true) {
            ET::info($envelope, "Running synchronous command");

            CommandRunner::runCommand(
                $envelope->getCommand(),
                array_merge($args, ['--env' => $this->environment])
            );
            return $stack->end()->handle($envelope, $stack);
        }

        if ($envelope->get(AsyncCronStamp::class)) {
            $this->producer->send(
                RunCommandTopic::getName(),
                [
                    'command' => $envelope->getCommand(),
                    'arguments' => $args
                ]
            );

            return $stack->end()->handle($envelope, $stack);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
