<?php

namespace Oro\Bundle\ApiBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * RestRouteLoader REST-enabled controller router loader.
 */
class RestRouteLoader extends Loader
{
    use RestLoaderTrait;

    protected $container;
    protected $controllerReader;
    protected $defaultFormat;
    protected $locator;

    /**
     * Initializes loader.
     *
     * @param ContainerInterface   $container
     * @param FileLocatorInterface $locator
     * @param RestControllerReader $controllerReader
     * @param string               $defaultFormat
     */
    public function __construct(
        ContainerInterface $container,
        FileLocatorInterface $locator,
        RestControllerReader $controllerReader,
        $defaultFormat = 'json'
    ) {
        $this->container = $container;
        $this->locator = $locator;
        $this->controllerReader = $controllerReader;
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function load($controller, $type = null, array $parents = [], $routePrefix = null, $namePrefix = null)
    {
        $this->controllerReader->getActionReader()->setParents($parents);
        $this->controllerReader->getActionReader()->setRoutePrefix($routePrefix);
        $this->controllerReader->getActionReader()->setNamePrefix($namePrefix);

        list($prefix, $class) = $this->getControllerLocator($controller);

        $collection = $this->controllerReader->read(new \ReflectionClass($class));
        $collection->prependRouteControllersWithPrefix($prefix);
        $collection->setDefaultFormat($this->defaultFormat);

        $this->controllerReader->getActionReader()->setParents([]);
        $this->controllerReader->getActionReader()->setRoutePrefix();
        $this->controllerReader->getActionReader()->setNamePrefix();

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource)
            && 'rest' === $type
            && 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Returns controller locator by its id.
     *
     * @param string $controller
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function getControllerLocator($controller)
    {
        $class = null;
        $prefix = null;
        if (str_starts_with($controller, '@')) {
            $file = $this->locator->locate($controller);
            $controllerClass = $this->findClassInFile($file);

            if (false === $controllerClass) {
                throw new \InvalidArgumentException(sprintf('Can\'t find class for controller "%s"', $controller));
            }

            $controller = $controllerClass;
        }

        if ($this->container->has($controller)) {
            // service_id
            $prefix = $controller.'::';
            $class = get_class($this->container->get($controller));
        } elseif (class_exists($controller)) {
            // full class name
            $class = $controller;
            $prefix = $class.'::';
        }

        if (empty($class)) {
            throw new \InvalidArgumentException(sprintf(
                'Class could not be determined for Controller identified by "%s".', $controller
            ));
        }

        return [$prefix, $class];
    }
}
