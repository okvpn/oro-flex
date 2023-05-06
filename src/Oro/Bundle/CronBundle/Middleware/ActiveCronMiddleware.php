<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Middleware;

use Okvpn\Bundle\CronBundle\Middleware\MiddlewareEngineInterface;
use Okvpn\Bundle\CronBundle\Middleware\StackInterface;
use Okvpn\Bundle\CronBundle\Model\ScheduleEnvelope;
use Oro\Bundle\CronBundle\Model\ActiveAwareCronInterface;
use Oro\Bundle\CronBundle\Model\ActiveCommandStamp;
use Oro\Bundle\CronBundle\Model\EnvelopeTools as ET;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

class ActiveCronMiddleware implements MiddlewareEngineInterface
{
    protected $application;

    public function __construct(
        protected KernelInterface $kernel,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ScheduleEnvelope $envelope, StackInterface $stack): ScheduleEnvelope
    {
        $stamp = $envelope->get(ActiveCommandStamp::class);
        if (!$stamp instanceof ActiveCommandStamp) {
            return $stack->next()->handle($envelope, $stack);
        }

        if (!$stamp->isEnabled()) {
            ET::info($envelope, "Command was disabled - skipped");
            return $stack->end()->handle($envelope, $stack);
        }

        if ($stamp->isActiveAware()) {
            $this->application ??= new Application($this->kernel);
            try {
                $command = $this->application->get($envelope->getCommand());
                if ($command instanceof ActiveAwareCronInterface && !$command->isActive()) {
                    ET::info($envelope, "Command is not active - skipped");
                    return $stack->end()->handle($envelope, $stack);
                }
            } catch (\Throwable $e) {
                ET::error($envelope, $e->getMessage());
                return $stack->end()->handle($envelope, $stack);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
