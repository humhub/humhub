<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m201130_073907_default_permissions
 */
class m201130_073907_default_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contentcontainer_default_permission', [
            'permission_id' =>  $this->string(150)->notNull(),
            'contentcontainer_class' => $this->char(60)->notNull(),
            'group_id' =>  $this->string(50)->notNull(),
            'module_id' => $this->string(50)->notNull(),
            'class' => Schema::TYPE_STRING,
            'state' => Schema::TYPE_BOOLEAN,
        ]);
        // Limit PK length to 767 bytes (50 + 50 + 50 + 41) * 4 = 191 * 4 = 764
        $this->addPrimaryKey('contentcontainer_default_permission_pk', 'contentcontainer_default_permission', ['permission_id(50)', 'group_id', 'module_id', 'contentcontainer_class(41)']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contentcontainer_default_permission');
    }
}
