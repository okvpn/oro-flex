<?php

namespace Oro\Bundle\ApiBundle\Processor;

/**
 * The base execution context for processors for actions work with a list of entities,
 * such as "get_list" and "delete_list".
 */
class ListContext extends Context
{
    /** a callback that can be used to calculate the total number of records in a list of entities */
    private const TOTAL_COUNT_CALLBACK = 'totalCountCallback';

    /**
     * Gets a callback that can be used to calculate the total number of records in a list of entities
     *
     * @return callable|null
     */
    public function getTotalCountCallback()
    {
        return $this->get(self::TOTAL_COUNT_CALLBACK);
    }

    /**
     * Sets a callback that can be used to calculate the total number of records in a list of entities
     *
     * @param callable|null $totalCount
     */
    public function setTotalCountCallback($totalCount)
    {
        $this->set(self::TOTAL_COUNT_CALLBACK, $totalCount);
    }
}
