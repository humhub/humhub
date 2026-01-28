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
        $this->addColumn('activity', 'grouping_key', $this->integer()->after('content_addon_record_id'));
        $this->update('activity', ['grouping_key' => new Expression('id')]);

        $this->safeCreateIndex('idx_activity_query', 'activity', ['grouping_key', 'contentcontainer_id', 'created_by', 'content_id']);

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
