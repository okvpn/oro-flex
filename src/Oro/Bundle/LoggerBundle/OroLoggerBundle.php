<?php

namespace Oro\Bundle\LoggerBundle;

use Oro\Bundle\LoggerBundle\DependencyInjection\Compiler\ConfigurableLoggerPass;
use Oro\Bundle\LoggerBundle\DependencyInjection\Compiler\ErrorLogNotificationMailerHandlerPass;
use Oro\Bundle\LoggerBundle\DependencyInjection\Compiler\LoggerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The LoggerBundle bundle class.
 */
class OroLoggerBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new LoggerPass());
    }
}
