<?php

use yii\db\Migration;

/**
 * Class m200715_171721_defaultOption
 */
class m200715_171721_defaultOption extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /** @var \humhub\modules\marketplace\Module $module */
        $module = Yii::$app->getModule('marketplace');
        $module->settings->set('includeCommunityModules', 1);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200715_171721_defaultOption cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200715_171721_defaultOption cannot be reverted.\n";

        return false;
    }
    */
}
