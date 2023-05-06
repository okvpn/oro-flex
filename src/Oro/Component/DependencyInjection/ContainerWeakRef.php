<?php

declare(strict_types=1);

namespace Oro\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides access to the private services in the migrations and fixtures.
 * Must be used carefully and only for migration loading.
 * @replacement MigrationContainer
 */
class ContainerWeakRef implements ContainerInterface
{
    protected $privateServiceMap;

    public function __construct(
        protected string $privateMetadata,
        protected ContainerInterface $container
    ) {}

    /**
     * {@inheritdoc}
     */
    public function set(string $id, ?object $service): void
    {
        $this->container->set($id, $service);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->container->has($id) || $this->hasPrivateService($id);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, $invalidBehavior = /* self::EXCEPTION_ON_INVALID_REFERENCE */ 1): ?object
    {
        if ($this->container->has($id)) {
            return $this->container->get($id, $invalidBehavior);
        }

        if ($this->hasPrivateService($id)) {
            return $this->getPrivateService($id);
        }

        return $this->container->get($id, $invalidBehavior);
    }

    /**
     * {@inheritdoc}
     */
    public function initialized(string $id): bool
    {
        return $this->container->initialized($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $name): bool
    {
        return $this->container->hasParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter(string $name, $value)
    {
        return $this->container->setParameter($name, $value);
    }

    protected function hasPrivateService(string $name): bool
    {
        $this->init();

        $resolved = $this->resolveName($name);
        if ($resolved !== $name && $this->container->has($resolved)) {
            return true;
        }

        if (isset($this->privateServiceMap[$resolved])) {
            $refl = new \ReflectionObject($this->container);
            return $refl->hasMethod($this->privateServiceMap[$resolved]);
        }

        return false;
    }

    protected function resolveName(string $name): string
    {
        if (isset($this->privateServiceMap[$name]) && str_starts_with($this->privateServiceMap[$name], '__service.')) {
            return str_replace('__service.', '', $this->privateServiceMap[$name]);
        }

        return $name;
    }

    protected function getPrivateService(string $name)
    {
        $this->init();

        $resolved = $this->resolveName($name);
        if ($this->container->has($resolved)) {
            return $this->container->get($resolved);
        }

        $reflect = new \ReflectionObject($this->container);
        $private = $reflect->getProperty('privates');
        $private->setAccessible(true);
        $private = $private->getValue($this->container);

        if (isset($private[$resolved])) {
            return $private[$resolved];
        }

        $method = $reflect->getMethod($this->privateServiceMap[$resolved]);
        $method->setAccessible(true);

        return $method->invoke($this->container);
    }

    protected function init(): void
    {
        if (null === $this->privateServiceMap) {
            $this->privateServiceMap = require $this->privateMetadata;
        }
    }
}
