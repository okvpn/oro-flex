<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Model;

use Okvpn\Bundle\CronBundle\Model\LoggerAwareStamp;
use Okvpn\Bundle\CronBundle\Model\ScheduleEnvelope;
use Psr\Log\LoggerInterface;

class EnvelopeTools
{
    public static function getLogger(ScheduleEnvelope $envelope): ?LoggerInterface
    {
        return $envelope->get(LoggerAwareStamp::class)?->getLogger();
    }

    public static function info(ScheduleEnvelope $envelope, string $message): void
    {
        static::getLogger($envelope)?->info("[{$envelope->getCommand()}] $message");
    }

    public static function warning(ScheduleEnvelope $envelope, string $message): void
    {
        static::getLogger($envelope)?->warning("[{$envelope->getCommand()}] $message");
    }

    public static function error(ScheduleEnvelope $envelope, string $message): void
    {
        static::getLogger($envelope)?->error("[{$envelope->getCommand()}] $message");
    }
}
