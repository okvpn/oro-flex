<?php

namespace Oro\Bundle\ReminderBundle\Tests\Unit\Entity\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\ReminderBundle\Entity\Repository\ReminderRepository;
use Oro\Component\TestUtils\ORM\Mocks\EntityManagerMock;
use Oro\Component\TestUtils\ORM\OrmTestCase;

class ReminderRepositoryTest extends OrmTestCase
{
    /** @var EntityManagerMock */
    protected $em;

    protected function setUp(): void
    {
        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(
            new AnnotationReader(),
            'Oro\Bundle\ReminderBundle\Entity'
        ));
        $this->em->getConfiguration()->setEntityNamespaces([
            'OroReminderBundle' => 'Oro\Bundle\ReminderBundle\Entity'
        ]);
    }

    public function testGetTaskListByTimeIntervalQueryBuilder()
    {
        $entityClassName = 'Test\Entity';
        $entityIds       = [1, 2, 3];

        /** @var ReminderRepository $repo */
        $repo = $this->em->getRepository('OroReminderBundle:Reminder');
        $qb   = $repo->findRemindersByEntitiesQueryBuilder($entityClassName, $entityIds);

        $this->assertEquals(
            'SELECT reminder'
            . ' FROM Oro\Bundle\ReminderBundle\Entity\Reminder reminder'
            . ' WHERE reminder.relatedEntityClassName = :className AND reminder.relatedEntityId IN (:ids)',
            $qb->getDQL()
        );
        $this->assertEquals($entityClassName, $qb->getParameter('className')->getValue());
        $this->assertEquals($entityIds, $qb->getParameter('ids')->getValue());
    }
}
