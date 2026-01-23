<?php

use humhub\components\Migration;
use yii\db\Expression;

class m260122_075627_activity_grouping extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('activity', 'grouping_key', $this->string()->after('content_addon_record_id'));
        $this->createIndex('idx_activity_grouping_key', 'activity', ['grouping_key']);
        $this->update('activity', ['grouping_key' => new Expression('id')]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260122_075627_activity_grouping cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260122_075627_activity_grouping cannot be reverted.\n";

        return false;
    }
    */
}
