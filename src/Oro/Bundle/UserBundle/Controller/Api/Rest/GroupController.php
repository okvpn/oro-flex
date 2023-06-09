<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\UserBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API CRUD controller for Group entity.
 */
class GroupController extends RestController
{
    /**
     * Get the list of groups
     *
     * @ApiDoc(
     *      description="Get the list of groups",
     *      resource=true
     * )
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @AclAncestor("oro_user_group_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Get group data
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Get group data",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_group_view")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Create new group
     *
     * @ApiDoc(
     *      description="Create new group",
     *      resource=true
     * )
     * @AclAncestor("oro_user_group_create")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Update existing group
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Update existing group",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_group_update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete group
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Delete group",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_group_delete",
     *      type="entity",
     *      class="OroUserBundle:Group",
     *      permission="DELETE"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'roles':
                $result = array();
                /** @var Role $role */
                foreach ($value as $index => $role) {
                    $result[$index] = array(
                        'id' => $role->getId(),
                        'role' => $role->getRole(),
                        'label' => $role->getLabel(),
                    );
                }
                $value = $result;
                break;
            case 'owner':
                if ($value) {
                    $value = array(
                        'id' => $value->getId(),
                        'name' => $value->getName()
                    );
                }
                break;
            default:
                parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result = parent::getPreparedItem($entity);

        unset($result['roles']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_user.group_manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_user.form.group.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_user.form.handler.group.api');
    }
}
