<?php

use humhub\components\Migration;

/**
 * Class m241004_093044_server_timezone
 */
class m241004_093044_server_timezone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->settings->set('serverTimeZone', Yii::$app->settings->get('defaultTimeZone', 'UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241004_093044_server_timezone cannot be reverted.\n";

        return false;
    }
}
