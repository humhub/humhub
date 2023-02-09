<?php

use humhub\modules\user\Module;
use yii\db\Migration;

/**
 * Class m230130_140944_rename_auth_invite_setting
 */
class m230130_140944_rename_auth_invite_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        $settingsManager = $module->settings;
        $internalUsersCanInvite = $settingsManager->get('auth.internalUsersCanInvite');
        $settingsManager->set('auth.internalUsersCanInviteByEmail', $internalUsersCanInvite);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230130_140944_rename_auth_invite_setting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230130_140944_rename_auth_invite_setting cannot be reverted.\n";

        return false;
    }
    */
}
