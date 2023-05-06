<?php

namespace Oro\Bundle\ApiBundle\Routing;

use Symfony\Component\Config\Resource\FileResource;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Routing\Annotation\Route;

class RestControllerReader
{
    private $actionReader;
    private $annotationReader;

    /**
     * Initializes controller reader.
     *
     * @param RestActionReader $actionReader     action reader
     * @param Reader           $annotationReader annotation reader
     */
    public function __construct(RestActionReader $actionReader, Reader $annotationReader)
    {
        $this->actionReader = $actionReader;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Returns action reader.
     *
     * @return RestActionReader
     */
    public function getActionReader()
    {
        return $this->actionReader;
    }

    /**
     * Reads controller routes.
     *
     * @param \ReflectionClass $reflectionClass
     *
     * @return RestRouteCollection
     *
     * @throws \InvalidArgumentException
     */
    public function read(\ReflectionClass $reflectionClass)
    {
        $collection = new RestRouteCollection();
        $collection->addResource(new FileResource($reflectionClass->getFileName()));

        // read prefix annotation
        if ($annotation = $this->readClassAnnotation($reflectionClass, 'Prefix')) {
            $this->actionReader->setRoutePrefix($annotation->value);
        }

        // read name-prefix annotation
        /** @var Route $annotation */
        $annotation = $this->readClassAnnotation($reflectionClass, 'Route') ?:
            $this->annotationReader->getClassAnnotation($reflectionClass, Route::class);

        $resource = [];
        if (null !== $annotation) {
            if ($annotation->getName()) {
                $this->actionReader->setNamePrefix($annotation->getName());
            }

            if ($annotation->getPath() === null) {
                $resource = preg_split('/([A-Z][^A-Z]*)Controller/', $reflectionClass->getShortName(), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            } else {
                $resource = $annotation->getPath() ? explode('_', $annotation->getPath()) : [];
            }
        }

        // trim '/' at the start of the prefix
        if (str_starts_with($prefix = $this->actionReader->getRoutePrefix(), '/')) {
            $this->actionReader->setRoutePrefix(substr($prefix, 1));
        }

        // read action routes into collection
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->actionReader->read($collection, $method, $resource);
        }

        return $collection;
    }

    /**
     * Reads class annotations.
     *
     * @param \ReflectionClass $reflectionClass
     * @param string           $annotationName
     *
     * @return object|null
     */
    private function readClassAnnotation(\ReflectionClass $reflectionClass, $annotationName)
    {
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotation = $this->annotationReader->getClassAnnotation($reflectionClass, $annotationClass)) {
            return $annotation;
        }

        return null;
    }
}
