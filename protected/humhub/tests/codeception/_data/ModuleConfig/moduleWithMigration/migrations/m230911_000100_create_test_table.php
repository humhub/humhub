<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

use humhub\components\Migration;

class m230911_000100_create_test_table extends Migration
{
    // protected properties
    protected string $table = 'test_module_with_migration';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable($this->table, [
            'id'         => $this->primaryKey(),
            'created_by' => $this->integerReferenceKey(),
            'created_at' => $this->timestampWithoutAutoUpdate()
                                 ->notNull(),
        ]);

        // add foreign key for table `user`
        $this->safeAddForeignKeyCreatedBy();
    }
}
