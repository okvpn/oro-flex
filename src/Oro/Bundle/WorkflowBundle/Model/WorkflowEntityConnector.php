<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\Exception\NotManageableEntityException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Provide information about entity applicability for workflow.
 * Only entities with single identifiers of (big|small)int, decimal and string are supported.
 */
class WorkflowEntityConnector
{
    const WORKFLOW_APPLICABLE_ENTITIES_CACHE_KEY_PREFIX = 'workflow_applicable_entity:';

    /** @var ManagerRegistry */
    protected $registry;

    /** @var array */
    protected $supportedIdentifierTypes = [
        Types::BIGINT,
        Types::DECIMAL,
        Types::INTEGER,
        Types::SMALLINT,
        Types::STRING
    ];

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param Cache $cache
     */
    public function __construct(ManagerRegistry $managerRegistry, Cache $cache = null)
    {
        $this->registry = $managerRegistry;
        $this->cache = $cache ?: DoctrineProvider::wrap(new ArrayAdapter(0, false));
    }

    /**
     * @param object|string $entity Entity object or its class name
     * @return bool
     */
    public function isApplicableEntity($entity)
    {
        $entityClass = is_object($entity) ? ClassUtils::getClass($entity) : ClassUtils::getRealClass($entity);

        $cacheKey = self::WORKFLOW_APPLICABLE_ENTITIES_CACHE_KEY_PREFIX . $entityClass;

        if (false === $this->cache->contains($cacheKey)) {
            $data = $this->isSupportedIdentifierType($entityClass);

            $this->cache->save($cacheKey, $data);
            //to reduce amounts of calls in cache->fetch returns immediately
            return $data;
        }

        return $this->cache->fetch($cacheKey);
    }

    /**
     * Not supports composed Primary Keys (more than one identifier field)
     * Supports only list of specified in $supportedIdentifierTypes
     *
     * @param string $class
     * @return bool
     */
    protected function isSupportedIdentifierType($class)
    {
        $manager = $this->registry->getManagerForClass($class);

        if (null === $manager) {
            throw new NotManageableEntityException($class);
        }

        $metadata = $manager->getClassMetadata($class);

        $identifier = $metadata->getIdentifierFieldNames();

        $type = $metadata->getTypeOfField($identifier[0]);

        return count($identifier) === 1 &&
            in_array($type instanceof Type ? $type->getName() : (string)$type, $this->supportedIdentifierTypes, true);
    }
}
