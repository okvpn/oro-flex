<?php

namespace Oro\Bundle\WorkflowBundle\EventListener;

use Oro\Bundle\EntityBundle\DBAL\Event\OroEntityEvent;
use Oro\Bundle\WorkflowBundle\Entity\ProcessJob;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Performs serialization and deserialization of ProcessJob data.
 */
class ProcessDataSerializeListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private string $format = 'json';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onPersist(OroEntityEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof ProcessJob || !$entity->getData()->isModified()) {
            return;
        }

        $this->serialize($entity);
    }

    /**
     * After ProcessJob loaded deserialize $serializedData
     */
    public function postLoad(ProcessJob $entity): void
    {
        $this->deserialize($entity);
    }

    /**
     * Serialize data of ProcessJob
     */
    private function serialize(ProcessJob $processJob): void
    {
        $processData = $processJob->getData();
        $serializedData = $this->getSerializer()->serialize(
            $processData,
            $this->format,
            ['processJob' => $processJob]
        );
        $processJob->setSerializedData($serializedData);
        $processData->setModified(false);
    }

    /**
     * Deserialize data of ProcessJob
     */
    private function deserialize(ProcessJob $processJob): void
    {
        // Pass serializer into ProcessJob to make lazy loading of entity item data.
        $processJob->setSerializer($this->getSerializer(), $this->format);
    }

    private function isSupported(object $entity): bool
    {
        return $entity instanceof ProcessJob;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_workflow.serializer.process.serializer' => SerializerInterface::class
        ];
    }

    private function getSerializer(): SerializerInterface
    {
        return $this->container->get('oro_workflow.serializer.process.serializer');
    }
}
