<?php

namespace Oro\Bundle\SecurityBundle\Acl\Voter;

use Oro\Bundle\EntityBundle\Exception\NotManageableEntityException;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * The base class for security voters that checks whether an access is granted for an entity object.
 */
abstract class AbstractEntityVoter implements VoterInterface, CacheableVoterInterface
{
    /** @var string[] */
    protected $supportedAttributes = [];

    /** @var string */
    protected $className;

    protected DoctrineHelper $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Sets the class name of an entity this voter works with.
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, $this->supportedAttributes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsType(string $subjectType): bool
    {
        $class = ClassUtils::getRealClass($subjectType);

        return \in_array($class, [FieldVote::class, ObjectIdentityInterface::class, $this->className]);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool true if this Voter can process the class
     */
    protected function supportsClass($class)
    {
        if (!$this->className) {
            throw new \InvalidArgumentException('className was not provided');
        }

        return $class === $this->className;
    }

    /**
     * Check whether at least one of the the attributes is supported
     *
     * @param array $attributes
     *
     * @return bool
     */
    protected function supportsAttributes(array $attributes)
    {
        $supportsAttributes = false;
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                $supportsAttributes = true;
                break;
            }
        }

        return $supportsAttributes;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!\is_object($object)) {
            return self::ACCESS_ABSTAIN;
        }

        // both entity and identity objects are supported
        $class = $this->getEntityClass($object);

        try {
            $identifier = $this->getEntityIdentifier($object);
        } catch (NotManageableEntityException $e) {
            return self::ACCESS_ABSTAIN;
        }

        if (null === $identifier) {
            return self::ACCESS_ABSTAIN;
        }

        return $this->getPermission($class, $identifier, $attributes);
    }

    /**
     * @param string $class
     * @param int    $identifier
     * @param array  $attributes
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getPermission($class, $identifier, array $attributes)
    {
        // cheap performance check (no DB interaction)
        if (!$this->supportsAttributes($attributes)) {
            return self::ACCESS_ABSTAIN;
        }

        // expensive performance check (includes DB interaction)
        if (!$this->supportsClass($class)) {
            return self::ACCESS_ABSTAIN;
        }

        $result = self::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $permission = $this->getPermissionForAttribute($class, $identifier, $attribute);

            // if not abstain or changing from granted to denied
            if (($result === self::ACCESS_ABSTAIN && $permission !== self::ACCESS_ABSTAIN)
                || ($result === self::ACCESS_GRANTED && $permission === self::ACCESS_DENIED)
            ) {
                $result = $permission;
            }

            // if one of attributes is denied then access should be denied for all attributes
            if ($result === self::ACCESS_DENIED) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param string $class
     * @param int    $identifier
     * @param string $attribute
     *
     * @return int
     */
    abstract protected function getPermissionForAttribute($class, $identifier, $attribute);

    /**
     * @param object $object
     *
     * @return string
     */
    protected function getEntityClass($object)
    {
        return EntityClassResolverUtil::getEntityClass($object);
    }

    /**
     * @param object $object
     *
     * @return int|null
     */
    protected function getEntityIdentifier($object)
    {
        return EntityIdentifierResolverUtil::getEntityIdentifier($object, $this->doctrineHelper);
    }
}
