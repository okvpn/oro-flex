<?php

namespace Oro\Component\Config;

use Oro\Component\Config\Loader\CumulativeResourceLoader;
use Oro\Component\Config\Loader\CumulativeResourceLoaderCollection;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

/**
 * Represents a resource which can be located in any bundle
 * and does not required any special registration in a bundle.
 */
class CumulativeResource implements ResourceInterface, SelfCheckingResourceInterface
{
    /** @var string */
    private $resource;

    /** @var CumulativeResourceLoaderCollection */
    private $resourceLoaders;

    /**
     * The list of found the resource
     *
     * @var array [bundle class => [resource path => TRUE, ...], ...]
     */
    private $found = [];

    /** @var int not serializable */
    private $isFreshTimestamp;

    /** @var bool not serializable */
    private $isFresh;

    /**
     * @param string                             $resource        The unique name of a configuration resource
     * @param CumulativeResourceLoaderCollection $resourceLoaders The resource loaders
     */
    public function __construct(string $resource, CumulativeResourceLoaderCollection $resourceLoaders)
    {
        $this->resource = $resource;
        $this->resourceLoaders = $resourceLoaders;
    }

    /**
     * Gets the unique name of a configuration resource.
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($timestamp)
    {
        if ($this->isFreshTimestamp !== $timestamp) {
            $this->isFreshTimestamp = $timestamp;
            $this->isFresh = true;

            $manager = CumulativeResourceManager::getInstance();
            $bundles = $manager->getBundles();
            foreach ($bundles as $bundleName => $bundleClass) {
                $bundleDir = $manager->getBundleDir($bundleClass);
                $bundleAppDir = $manager->getBundleAppDir($bundleName);
                /** @var CumulativeResourceLoader $loader */
                foreach ($this->resourceLoaders as $loader) {
                    if (!$loader->isResourceFresh($bundleClass, $bundleDir, $bundleAppDir, $this, $timestamp)) {
                        $this->isFresh = false;
                        break;
                    }
                }
                if (!$this->isFresh) {
                    break;
                }
            }
        }

        return $this->isFresh;
    }

    /**
     * Registers a resource as found one
     *
     * @param string $bundleClass The full name of bundle class
     * @param string $path        The full path to the resource
     */
    public function addFound(string $bundleClass, string $path): void
    {
        $this->found[$bundleClass][$path] = true;
    }

    /**
     * Checks if a resource was registered as found one
     *
     * @param string $bundleClass The full name of bundle class
     * @param string $path        The full path to the resource
     *
     * @return bool
     */
    public function isFound(string $bundleClass, string $path): bool
    {
        return isset($this->found[$bundleClass][$path]);
    }

    /**
     * Gets all found resources for the given bundle
     *
     * @param string $bundleClass The full name of bundle class
     *
     * @return string[] A list of resources' full paths
     */
    public function getFound(string $bundleClass): array
    {
        return isset($this->found[$bundleClass])
            ? array_keys($this->found[$bundleClass])
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string)$this->resource;
    }

    public function __serialize(): array
    {
        return [$this->resource, $this->found, $this->resourceLoaders];
    }

    public function __unserialize(array $serialized): void
    {
        [$this->resource, $this->found, $this->resourceLoaders] = $serialized;
    }
}
