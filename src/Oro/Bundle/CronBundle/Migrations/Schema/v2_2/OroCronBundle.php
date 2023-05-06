<?php

namespace Oro\Bundle\CronBundle\Migrations\Schema\v2_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCronBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_cron_schedule');
        $table->addColumn('enabled', Types::BOOLEAN, ['default' => true]);
        $table->addColumn('overwrite_definition', Types::STRING, ['notnull' => false, 'length' => 100]);
        $table->addColumn('status', Types::STRING, ['notnull' => false, 'length' => 32]);

        $table->dropIndex('UQ_COMMAND');
    }
}
