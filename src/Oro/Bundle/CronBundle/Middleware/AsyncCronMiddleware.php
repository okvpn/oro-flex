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
use Oro\Component\MessageQueue\Client\Message;
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
        /** @var AsyncCronStamp $async */
        $async = $envelope->get(AsyncCronStamp::class);

        $args = $envelope->get(ArgumentsStamp::class)?->getArguments() ?: [];
        if ($synchronous === true) {
            ET::info($envelope, "Running synchronous command");

            CommandRunner::runCommand(
                command: $envelope->getCommand(),
                params: array_merge($args, ['--env' => $this->environment]),
                lock: (bool)$async?->isLock()
            );
            return $stack->end()->handle($envelope, $stack);
        }

        if (null !== $async) {
            $message = new Message([
                'command' => $envelope->getCommand(),
                'arguments' => $args
            ]);
            if ($rand = $async->getRandomDelay()) {
                [$min, $max] = is_int($rand) ? [0, $rand] : $rand;
                $message->setDelay(random_int($min, $max));
            }

            $this->producer->send(RunCommandTopic::getName(), $message);

            return $stack->end()->handle($envelope, $stack);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
