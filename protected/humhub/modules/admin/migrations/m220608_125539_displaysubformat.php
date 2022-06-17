<?php

use yii\db\Migration;

/**
 * Class m220608_125539_displaysubformat
 */
class m220608_125539_displaysubformat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->settings->set('displayNameSubFormat', 'title');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220608_125539_displaysubformat cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220608_125539_displaysubformat cannot be reverted.\n";

        return false;
    }
    */
}
