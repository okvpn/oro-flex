<?php

declare(strict_types=1);

namespace Oro\Bundle\ApiBundle\Routing;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;

class DirectoryRouteLoader extends Loader
{
    use RestLoaderTrait;
    private $fileLocator;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (isset($resource[0]) && '@' === $resource[0]) {
            $resource = $this->fileLocator->locate($resource);
        }

        if (!is_dir($resource)) {
            throw new \InvalidArgumentException(sprintf('Given resource of type "%s" is no directory.', $resource));
        }

        $collection = new RouteCollection();

        $finder = new Finder();

        foreach ($finder->in($resource)->name('*.php')->files() as $file) {
            $imported = $this->importResource($this, $this->findClassInFile($file), [], null, null, 'rest');
            $collection->addCollection($imported);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        if ('rest' !== $type || !is_string($resource)) {
            return false;
        }

        if (isset($resource[0]) && '@' === $resource[0]) {
            $resource = $this->fileLocator->locate($resource);
        }

        return is_dir($resource);
    }
}
