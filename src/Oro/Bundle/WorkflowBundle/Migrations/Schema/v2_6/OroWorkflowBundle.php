<?php

namespace Oro\Bundle\WorkflowBundle\Migrations\Schema\v2_6;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWorkflowBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_process_definition');
        $table->addColumn('flush_entity', Types::BOOLEAN, ['default' => true]);
    }
}
