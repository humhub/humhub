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
 * Migrates content's object_model to table class_map
 *
 * @author Martin Rüegg
 */
class m230330_163647_migrate_content_table_to_class_map extends ClassMapReferenceMigrationHelper
{
    protected string $table = 'content';

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230330_163647_update_content_table.\n";

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
            'object_model',
            'object_class_id',
            null,
            null,
            null,
            ['index_object_model']
        );

        $this->safeCreateIndex(
            "ux-$this->table-class-id",
            $this->table,
            [
                'object_class_id',
                'object_id',
            ],
            true
        );
    }
}
