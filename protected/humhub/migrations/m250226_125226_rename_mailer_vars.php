<?php

use humhub\models\Setting;
use yii\db\Migration;

class m250226_125226_rename_mailer_vars extends Migration
{
    private function keyMap()
    {
        return [
            'mailer.transportType' => 'mailerTransportType',
            'mailer.dsn' => 'mailerDsn',
            'mailer.hostname' => 'mailerHostname',
            'mailer.username' => 'mailerUsername',
            'mailer.password' => 'mailerPassword',
            'mailer.useSmtps' => 'mailerUseSmtps',
            'mailer.port' => 'mailerPort',
            'mailer.encryption' => 'mailerEncryption',
            'mailer.allowSelfSignedCerts' => 'mailerAllowSelfSignedCerts',
            'mailer.systemEmailAddress' => 'mailerSystemEmailAddress',
            'mailer.systemEmailName' => 'mailerSystemEmailName',
            'mailer.systemEmailReplyTo' => 'mailerSystemEmailReplyTo',
        ];
    }

    public function safeUp()
    {
        foreach ($this->keyMap() as $oldKey => $newKey) {
            $oldSetting = Setting::find()->where([
                'module_id' => 'base',
                'name' => $oldKey,
            ])->one();

            if ($oldSetting) {
                Yii::$app->settings->set($newKey, $oldSetting->value);
                $oldSetting->delete();
            }
        }

        Yii::$app->settings->reload();
    }

    public function safeDown()
    {
        echo "m250226_125226_rename_mailer_vars does not support migration down.\n";
        return false;
    }
}
