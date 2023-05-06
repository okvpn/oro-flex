<?php

namespace Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * A base class for compiler passes that add services to the list of persistent services.
 */
abstract class RegisterPersistentServicesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $persistentServices = $this->getPersistentServices($container);
        if (!empty($persistentServices)) {
            $this->processPersistentServices($container, $persistentServices);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string[]           $persistentServices
     */
    protected function processPersistentServices(ContainerBuilder $container, array $persistentServices)
    {
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string[]
     */
    abstract protected function getPersistentServices(ContainerBuilder $container);
}
