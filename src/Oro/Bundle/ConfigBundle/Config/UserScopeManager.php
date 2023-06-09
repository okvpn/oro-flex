<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * User config scope
 */
class UserScopeManager extends AbstractScopeManager
{
    /** @var TokenStorageInterface */
    protected $securityContext;

    /** @var int */
    protected $scopeId;

    /**
     * Sets the security context
     */
    public function setSecurityContext(TokenStorageInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopedEntityName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeId()
    {
        $this->ensureScopeIdInitialized();

        return $this->scopeId;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopeId($scopeId)
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId;
    }

    /**
     * {@inheritdoc}
     */
    protected function isSupportedScopeEntity($entity)
    {
        return $entity instanceof User;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeEntityIdValue($entity)
    {
        if ($entity instanceof User) {
            return $entity->getId();
        }
        throw new \LogicException(sprintf('"%s" is not supported.', \get_class($entity)));
    }

    /**
     * Makes sure that the scope id is set
     */
    protected function ensureScopeIdInitialized()
    {
        if (!$this->scopeId) {
            $scopeId = 0;

            $token = $this->securityContext->getToken();
            if ($token) {
                $user = $token->getUser();
                if ($user instanceof User && $user->getId()) {
                    $scopeId = $user->getId();
                }
            }

            $this->scopeId = $scopeId;
        }
    }
}
