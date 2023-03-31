<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\ClassMapCache;
use humhub\libs\ClassMapReferenceMigrationHelper;

require_once __DIR__ . '/../../../libs/ClassMapReferenceMigrationHelper.php';

/**
 * Migrates notification's class and source_class fields to table class_map
 *
 * @author Martin Rüegg
 */
class m230331_120747_migrate_notification_table_to_class_map extends ClassMapReferenceMigrationHelper
{
    protected string $table = 'notification';

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230331_120747_migrate_notification_table_to_class_map.\n";

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        ClassMapCache::invalidate();

        $this->migrateReference(
            $this->table,
            'class',
            null,
            null,
            null,
            null,
            ['index_groupuser']
        );

        $this->migrateReference(
            $this->table,
            'source_class'
        );

        $this->safeCreateIndex(
            "ix-$this->table-class_id-group_key",
            'notification',
            [
                'class_id',
                'group_key',
            ]
        );
    }
}
