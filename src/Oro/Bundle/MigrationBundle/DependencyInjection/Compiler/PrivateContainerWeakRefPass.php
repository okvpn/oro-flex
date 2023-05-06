<?php

declare(strict_types=1);

namespace Oro\Bundle\MigrationBundle\DependencyInjection\Compiler;

use Oro\Component\DependencyInjection\PhpDumper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects all private services and their aliases to build the service locator for the migration container.
 */
class PrivateContainerWeakRefPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = $container->getDefinitions();
        $aliases = $container->getAliases();

        $privateServices = [];
        foreach ($aliases as $id => $alias) {
            if ($id && '.' !== $id[0] && (!$alias->isPublic() || $alias->isPrivate())) {
                while (isset($aliases[$target = (string) $alias])) {
                    $alias = $aliases[$target];
                }
                if (isset($definitions[$target])
                    && !$definitions[$target]->getErrors()
                    && !$definitions[$target]->isAbstract()
                ) {
                    $privateServices[$id] = $target;
                }
            }
        }

        $privateServices = array_merge($privateServices, $this->getExtraServices());
        PhpDumper::setPrivateAliasMap($privateServices);
    }

    private function getExtraServices()
    {
        return [
            'oro_message_queue.client.message_producer' => 'oro_message_queue.message_producer'
        ];
    }
}
