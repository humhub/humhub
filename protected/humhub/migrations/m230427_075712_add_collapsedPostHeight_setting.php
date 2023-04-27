<?php

use humhub\models\Setting;
use yii\db\Migration;

/**
 * Class m230427_075712_add_collapsedPostHeight_setting
 */
class m230427_075712_add_collapsedPostHeight_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $setting = Setting::findOne(['name' => 'collapsedPostHeight', 'module_id' => 'base']);
        if ($setting == null) {
            Yii::$app->settings->set('collapsedPostHeight', 380);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230427_075712_add_collapsedPostHeight_setting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230427_075712_add_collapsedPostHeight_setting cannot be reverted.\n";

        return false;
    }
    */
}
