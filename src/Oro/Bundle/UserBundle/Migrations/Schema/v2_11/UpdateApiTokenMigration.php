<?php

namespace Oro\Bundle\UserBundle\Migrations\Schema\v2_11;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateApiTokenMigration implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_user_api');
        $table->addColumn('allowed_routes', Types::JSON, ['notnull' => false]);
        $table->addColumn('allowed_routes_regex', Types::JSON, ['notnull' => false]);
    }
}
