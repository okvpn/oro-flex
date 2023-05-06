<?php

namespace Oro\Bundle\MigrationBundle;

use Oro\Bundle\MigrationBundle\DependencyInjection\Compiler\MigrationExtensionPass;
use Oro\Bundle\MigrationBundle\DependencyInjection\Compiler\PrivateContainerWeakRefPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The MigrationBundle bundle class.
 */
class OroMigrationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MigrationExtensionPass());
        $container->addCompilerPass(new PrivateContainerWeakRefPass(), PassConfig::TYPE_BEFORE_REMOVING, -32);
    }
}
