<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Doctrine\ORM\Query;
use Oro\Bundle\DashboardBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Add owner condition expression to query when a widget has configured an owner.
 */
class WidgetOwnerListener
{
    /** @var OwnerHelper */
    protected $ownerHelper;

    /** @var WidgetConfigs */
    protected $widgetConfigs;

    /** @var string */
    protected $ownerField;

    /**
     * @param OwnerHelper   $ownerHelper
     * @param WidgetConfigs $widgetConfigs
     * @param string        $ownerField
     */
    public function __construct(OwnerHelper $ownerHelper, WidgetConfigs $widgetConfigs, $ownerField)
    {
        $this->ownerHelper   = $ownerHelper;
        $this->widgetConfigs = $widgetConfigs;
        $this->ownerField    = $ownerField;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function onResultBefore(OrmResultBefore $event): void
    {
        $params        = $event->getDatagrid()->getParameters()->get('_parameters', null);
        $widgetOptions = $this->widgetConfigs->getWidgetOptions($params['_widgetId'] ?? null);
        $ids           = $this->ownerHelper->getOwnerIds($widgetOptions);
        if ($ids) {
            /** @var OrmDatasource $dataSource */
            $dataSource  = $event->getDatagrid()->getDatasource();
            $qb          = $dataSource->getQueryBuilder();
            $rootAliases = $qb->getRootAliases();
            $field       = sprintf('%s.%s', reset($rootAliases), $this->ownerField);
            QueryBuilderUtil::applyOptimizedIn($qb, $field, $ids);

            /** @var Query $query */
            $query = $event->getQuery();
            $query->setDQL($dataSource->getQueryBuilder()->getQuery()->getDQL());
            $queryParameters = $query->getParameters();
            foreach ($qb->getParameters() as $parameter) {
                $queryParameters->add($parameter);
            }
        }
    }
}
