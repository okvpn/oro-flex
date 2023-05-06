<?php

namespace Oro\Bundle\CronBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CronBundle\Entity\Schedule;

class ScheduleRepository extends EntityRepository
{
    /**
     * @return array|Schedule[]
     */
    public function overwrittenSchedule(): array
    {
        /** @var Schedule[] $schedules */
        $schedules = $this->createQueryBuilder('s')
            ->where('s.overwriteDefinition IS NOT NULL')
            ->orWhere('s.enabled = false')
            ->getQuery()->getResult();

        $map = [];
        foreach ($schedules as $schedule) {
            $map[$schedule->getArgumentsHash()] = $schedule;
        }

        return $map;
    }
}
