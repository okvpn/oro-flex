<?php

namespace Oro\Bundle\EmailBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestGetController;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\ChainParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\EmailAddressParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\EntityClassParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\StringToArrayParameterFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller to find entities associated with an email activity.
 */
class EmailActivitySearchController extends RestGetController
{
    /**
     * Searches entities associated with the email activity.
     *
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
     *      description="Number of items per page. Defaults to 10."
     * )
     * @QueryParam(
     *     name="search",
     *     requirements=".+",
     *     nullable=true,
     *     description="The search string."
     * )
     * @QueryParam(
     *      name="from",
     *      requirements=".+",
     *      nullable=true,
     *      description="The entity alias. One or several aliases separated by comma. Defaults to all entities."
     * )
     * @QueryParam(
     *      name="email",
     *      requirements=".+",
     *      nullable=true,
     *      description="An email address. One or several addresses separated by comma."
     * )
     *
     * @ApiDoc(
     *      description="Searches entities associated with the email activity.",
     *      resource=true
     * )
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        $filters = [
            'search' => $request->get('search')
        ];

        $from = $request->get('from', null);
        if ($from) {
            $filter          = new ChainParameterFilter(
                [
                    new StringToArrayParameterFilter(),
                    new EntityClassParameterFilter($this->get('oro_entity.entity_class_name_helper'))
                ]
            );
            $filters['from'] = $filter->filter($from, null);
        }

        $email = $request->get('email', null);
        if ($email) {
            $filter            = new ChainParameterFilter(
                [
                    new StringToArrayParameterFilter(),
                    new EmailAddressParameterFilter($this->container->get('oro_email.email.address.helper'))
                ]
            );
            $filters['emails'] = $filter->filter($email, null);
        }

        $data = $this->getManager()->getSearchResult($limit, $page, $filters);
        foreach ($data['result'] as &$item) {
            $metadata = $this->get('oro_entity_config.config_manager')->getEntityMetadata($item['entity']);
            if ($metadata && $metadata->hasRoute()) {
                $item['urlView'] = $this->get('router')->generate($metadata->getRoute(), ['id' => $item['id']]);
            } else {
                $item['urlView'] = '';
            }
        }

        return $this->buildResponse($data['result'], self::ACTION_LIST, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_email.manager.email_activity_search.api');
    }
}
