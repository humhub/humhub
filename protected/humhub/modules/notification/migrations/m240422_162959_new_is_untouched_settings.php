<?php

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\Module;
use humhub\modules\user\models\User;
use yii\db\Migration;

/**
 * Class m240422_162959_new_is_untouched_settings
 */
class m240422_162959_new_is_untouched_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (User::find()->each() as $user) {
            /** @var Module $module */
            $module = Yii::$app->getModule('notification');
            try {
                $settingsManager = $module->settings->user($user);
                if ($settingsManager && $settingsManager->get('notification.like_email') !== null) {
                    $settingsManager->set(NotificationManager::IS_TOUCHED_SETTINGS, true);
                }
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240422_162959_new_is_untouched_settings cannot be reverted.\n";

        return false;
    }
}
