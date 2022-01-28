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
        $table = 'contentcontainer_default_permission';
        $primaryKey = $table . '_pk';

        if ($this->tableExists($table)) {
            // Make sure the column has a correct length because in first version it had a wrong length 255 chars so PK couldn't be created
            $this->alterColumn($table, 'contentcontainer_class', $this->char(60)->notNull());
        } else {
            $this->createTable($table, [
                'permission_id' => $this->string(150)->notNull(),
                'contentcontainer_class' => $this->char(60)->notNull(),
                'group_id' => $this->string(50)->notNull(),
                'module_id' => $this->string(50)->notNull(),
                'class' => Schema::TYPE_STRING,
                'state' => Schema::TYPE_BOOLEAN,
            ]);
        }

        if ($this->primaryKeyExists($table)) {
            // Remove old(probably wrong) primary key
            $this->dropPrimaryKey($primaryKey, $table);
        }

        try {
            $this->addPrimaryKey($primaryKey, $table, ['permission_id', 'group_id', 'module_id', 'contentcontainer_class']);
        } catch (\Exception $ex) {
            Yii::error($ex->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contentcontainer_default_permission');
    }

    /**
     * Check if the table already exists
     *
     * @param string $table Table name
     * @return bool
     */
    protected function tableExists($table)
    {
        return Yii::$app->getDb()->getSchema()->getTableSchema($table) !== null;
    }

    /**
     * Check if the table has a primary key
     *
     * @param string $table Table name
     * @return bool
     */
    protected function primaryKeyExists($table)
    {
        return !empty(Yii::$app->getDb()->getSchema()->getTableSchema($table)->primaryKey);
    }
}
