<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%space}}`.
 */
class m230419_102455_add_sort_order_column_to_space_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%space}}', 'sort_order', $this->integer()->defaultValue(100)->notNull()->after('status'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230419_102455_add_sort_order_column_to_space_table cannot be reverted.\n";
    }
}
