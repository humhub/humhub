<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */


use humhub\components\Migration;

/**
 * Optimize primary key
 */
class m231212_235100_update_contentcontainer_permission_primary_key extends Migration
{
    // protected properties
    protected string $table = 'contentcontainer_permission';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeDropPrimaryKey('PRIMARY', $this->table);

        $this->safeAddPrimaryKey("pk-$this->table", $this->table, [
            'contentcontainer_id',
            'group_id',
            'module_id',
            'permission_id'
        ]);
    }
}
