<?php

namespace Oro\Bundle\ApiBundle\Routing;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * RestYamlCollectionLoader YAML file collections loader.
 */
class RestYamlCollectionLoader extends YamlFileLoader
{
    use RestLoaderTrait;

    protected $collectionParents = [];
    private $includeFormat;
    private $formats;
    private $defaultFormat;

    /**
     * Initializes yaml loader.
     *
     * @param FileLocatorInterface $locator
     * @param bool                 $includeFormat
     * @param string[]             $formats
     * @param string               $defaultFormat
     */
    public function __construct(
        FileLocatorInterface $locator,
        $includeFormat = true,
        array $formats = [],
        $defaultFormat = null
    ) {
        parent::__construct($locator);

        $this->includeFormat = $includeFormat;
        $this->formats = $formats;
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $configs = Yaml::parse(file_get_contents($path));

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($configs as $name => $config) {
            if (isset($config['resource'])) {
                $resource = $config['resource'];
                $prefix = $config['prefix'] ?? null;
                $namePrefix = $config['name_prefix'] ?? null;
                $parent = $config['parent'] ?? null;
                $type = $config['type'] ?? null;
                $requirements = $config['requirements'] ?? [];
                $defaults = $config['defaults'] ?? [];
                $options = $config['options'] ?? [];
                $currentDir = dirname($path);

                $parents = [];
                if (!empty($parent)) {
                    if (!isset($this->collectionParents[$parent])) {
                        throw new \InvalidArgumentException(sprintf('Cannot find parent resource with name %s', $parent));
                    }

                    $parents = $this->collectionParents[$parent];
                }

                $imported = $this->importResource($this, $resource, $parents, $prefix, $namePrefix, $type, $currentDir);

                if ($imported instanceof RestRouteCollection) {
                    $parents[] = ($prefix ? $prefix.'/' : '').$imported->getSingularName();
                    $prefix = null;
                    $namePrefix = null;

                    $this->collectionParents[$name] = $parents;
                }

                $imported->addRequirements($requirements);
                $imported->addDefaults($defaults);
                $imported->addOptions($options);

                $imported->addPrefix((string)$prefix);

                // Add name prefix from parent config files
                $imported = $this->addParentNamePrefix($imported, $namePrefix);

                $collection->addCollection($imported);
            } elseif (isset($config['pattern']) || isset($config['path'])) {
                // the YamlFileLoader of the Routing component only checks for
                // the path option
                if (!isset($config['path'])) {
                    $config['path'] = $config['pattern'];
                }

                if ($this->includeFormat) {
                    // append format placeholder if not present
                    if (!str_contains($config['path'], '{_format}')) {
                        $config['path'] .= '.{_format}';
                    }

                    // set format requirement if configured globally
                    if (!isset($config['requirements']['_format']) && !empty($this->formats)) {
                        $config['requirements']['_format'] = implode('|', array_keys($this->formats));
                    }
                }

                // set the default format if configured
                if (null !== $this->defaultFormat) {
                    $config['defaults']['_format'] = $this->defaultFormat;
                }

                $this->parseRoute($collection, $name, $config, $path);
            } else {
                throw new \InvalidArgumentException(sprintf('Unable to parse the "%s" route.', $name));
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
            'yml' === pathinfo($resource, PATHINFO_EXTENSION) &&
            'rest' === $type;
    }

    /**
     * Adds a name prefix to the route name of all collection routes.
     *
     * @param RouteCollection $collection Route collection
     * @param string           $namePrefix NamePrefix to add in each route name of the route collection
     *
     * @return RouteCollection
     */
    protected function addParentNamePrefix(RouteCollection $collection, $namePrefix)
    {
        if (!isset($namePrefix) || ($namePrefix = trim($namePrefix)) === '') {
            return $collection;
        }

        $iterator = $collection->getIterator();

        foreach ($iterator as $key1 => $route1) {
            $collection->add($namePrefix.$key1, $route1);
            $collection->remove($key1);
        }

        return $collection;
    }
}
