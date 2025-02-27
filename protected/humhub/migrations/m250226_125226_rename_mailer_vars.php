<?php

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
            $value = Yii::$app->settings->get($oldKey);
            Yii::$app->settings->set($newKey, $value);
            Yii::$app->settings->delete($oldKey);
        }
    }

    public function safeDown()
    {
        foreach ($this->keyMap() as $newKey => $oldKey) {
            $value = Yii::$app->settings->get($oldKey);
            Yii::$app->settings->set($newKey, $value);
            Yii::$app->settings->delete($oldKey);
        }
    }
}
