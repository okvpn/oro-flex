<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroEntityExtendExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader      = new Loader\YamlFileLoader($container, $fileLocator);
        $loader->load('services.yml');
        $loader->load('orm.yml');
        $loader->load('form_type.yml');
        $loader->load('commands.yml');
        $loader->load('controllers.yml');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.yml');
        }
    }
}
