<?php

use yii\db\Migration;

/**
 * Class m230127_195245_content_state
 */
class m230127_195245_content_state extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'content',
            'state',
            $this->tinyInteger()->defaultValue(1)->notNull()->after('visibility')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230127_195245_content_state cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230127_195245_content_state cannot be reverted.\n";

        return false;
    }
    */
}
