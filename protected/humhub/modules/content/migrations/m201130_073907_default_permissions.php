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
        $this->addPrimaryKey('contentcontainer_default_permission_pk', 'contentcontainer_default_permission', ['permission_id', 'group_id', 'module_id', 'contentcontainer_class']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contentcontainer_default_permission');
    }
}
