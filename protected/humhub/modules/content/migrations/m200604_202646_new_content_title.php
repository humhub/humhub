<?php

use yii\db\Migration;

/**
 * Class m200604_202646_new_content_title
 */
class m200604_202646_new_content_title extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('content', 'title', $this->string(100)->null()->after('object_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200604_202646_new_content_title cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200604_202646_new_content_title cannot be reverted.\n";

        return false;
    }
    */
}
