<?php

declare(strict_types=1);

namespace Oro\Bundle\EntityBundle\DBAL\Event;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;

class OroEntityEvent extends Event
{
    public const NAME = 'entityPersist';

    public function __construct(protected object $entity, protected EntityManagerInterface $manager)
    {
    }

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getManager(): ObjectManager|EntityManagerInterface
    {
        return $this->manager;
    }
}
