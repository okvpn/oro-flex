<?php

declare(strict_types=1);

namespace Oro\Bundle\EntityBundle\DBAL;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\DBAL\Event\OroEntityEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DBALEntityPersister
{
    /**
     * @var DoctrineEntityPersister[]
     */
    protected $entityPersister = [];

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $scheduledEntities = [];

    /**
     * @param ManagerRegistry $registry
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $eventDispatcher)
    {
        $this->registry = $registry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param object $entity
     */
    public function persist(object $entity): void
    {
        $class = ClassUtils::getClass($entity);

        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($class);
        if (!isset($this->entityPersister[$class])) {
            $persister = new DoctrineEntityPersister($em, $em->getClassMetadata($class));
            $this->entityPersister[$class] = $persister;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($class);
        $this->eventDispatcher->dispatch(new OroEntityEvent($entity, $em), OroEntityEvent::NAME);
        $persister = $this->entityPersister[$class];
        $persister->addInsert($entity);
        $this->scheduledEntities[$class] = $class;
    }

    public function flush(): void
    {
        foreach ($this->scheduledEntities as $class) {
            $this->entityPersister[$class]->executeInserts();
        }

        $this->scheduledEntities = [];
    }
}
