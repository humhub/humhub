<?php

use humhub\models\Setting;
use yii\db\Migration;

/**
 * Class m220606_205507_mailer_settings
 */
class m220606_205507_mailer_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $setting = Setting::findOne(['name' => 'mailer.encryption', 'module_id' => 'base']);
        if ($setting !== null) {
            if ($setting->value === 'tls' || $setting->value === 'ssl') {
                Yii::$app->settings->set('mailer.useSmtps', true);
            }
            $setting->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220606_205507_mailer_settings cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220606_205507_mailer_settings cannot be reverted.\n";

        return false;
    }
    */
}
