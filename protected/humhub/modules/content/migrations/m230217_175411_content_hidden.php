<?php

use yii\db\Migration;

/**
 * Class m230217_175411_content_hidden
 */
class m230217_175411_content_hidden extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('content', 'contentcontainer_id', $this->integer()->after('object_id'));

        $this->alterColumn('content', 'stream_sort_date', $this->datetime()->after('contentcontainer_id'));
        $this->alterColumn('content', 'stream_channel', $this->string(15)->defaultValue('default')->after('contentcontainer_id'));

        $this->alterColumn('content', 'visibility', $this->tinyInteger()->defaultValue(0)->notNull());
        $this->alterColumn('content', 'pinned', $this->boolean()->defaultValue(false)->notNull());
        $this->alterColumn('content', 'archived', $this->boolean()->defaultValue(false)->notNull());

        $this->update('content', ['locked_comments' => 0], 'locked_comments IS NULL');
        $this->alterColumn('content', 'locked_comments', $this->boolean()->defaultValue(false)->notNull()->after('archived'));

        $this->addColumn('content', 'hidden', $this->boolean()->after('archived')->defaultValue(false)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230217_175411_content_hidden cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230217_175411_content_hidden cannot be reverted.\n";

        return false;
    }
    */
}
