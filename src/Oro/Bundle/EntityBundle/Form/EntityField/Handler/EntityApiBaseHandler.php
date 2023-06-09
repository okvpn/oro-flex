<?php

namespace Oro\Bundle\EntityBundle\Form\EntityField\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\EntityApiHandlerProcessor;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Used to pre-process entity and submit form data
 */
class EntityApiBaseHandler
{
    /** @var Registry */
    protected $registry;

    /** @var EntityApiHandlerProcessor */
    protected $processor;

    /** @var PropertyAccessorInterface*/
    protected $propertyAccessor;

    public function __construct(
        Registry $registry,
        EntityApiHandlerProcessor $processor,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->registry = $registry;
        $this->processor = $processor;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Process form
     *
     * @param $entity
     * @param FormInterface $form
     * @param array $data
     * @param string $method
     *
     * @return array changset
     */
    public function process($entity, FormInterface $form, $data, $method)
    {
        $this->processor->preProcess($entity);
        $form->setData($entity);

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $form->submit($data, 'PATCH' !== $method);

            if ($form->isValid()) {
                $this->processor->beforeProcess($entity);

                $this->onSuccess($entity);

                $changeSet = $this->initChangeSet($entity, $data);
                $changeSet = $this->updateChangeSet($changeSet, $this->processor->afterProcess($entity));

                return $changeSet;
            } else {
                $this->processor->invalidateProcess($entity);
            }
        }

        return [];
    }

    /**
     * @param $entity
     *
     * @return array
     */
    protected function initChangeSet($entity, $data)
    {
        $response = [
            'fields' => $data
        ];
        if ($this->propertyAccessor->isReadable($entity, 'updatedAt')) {
            $response['fields']['updatedAt'] = $this->propertyAccessor->getValue($entity, 'updatedAt');
        }

        return $response;
    }

    /**
     * @param $changeSet
     * @param $update
     *
     * @return array
     */
    protected function updateChangeSet($changeSet, $update)
    {
        if (is_array($update)) {
            $result = array_replace_recursive($changeSet, $update);
        } else {
            $result = $changeSet;
        }

        return $result;
    }

    /**
     * "Success" form handler
     */
    protected function onSuccess($entity)
    {
        $this->registry->getManager()->persist($entity);
        $this->registry->getManager()->flush();
    }
}
