<?php

use humhub\libs\DynamicConfig;
use yii\db\Migration;

/**
 * Class m241211_193138_reduce_dynamic_config
 */
class m241211_193138_reduce_dynamic_config extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (Yii::$app->isInstalled()) {
            DynamicConfig::load();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241211_193138_reduce_dynamic_config cannot be reverted.\n";

        return false;
    }
}
