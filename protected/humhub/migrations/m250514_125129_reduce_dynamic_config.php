<?php

use humhub\libs\DynamicConfig;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

class m250514_125129_reduce_dynamic_config extends Migration
{
    public function safeUp()
    {
        if (!!ArrayHelper::getValue(Yii::$app->params, 'installed', false)) {
            Yii::$app->installationState->setInstalled();
            DynamicConfig::load();
        }
    }

    public function safeDown()
    {
        echo "m250514_125129_reduce_dynamic_config cannot be reverted.\n";

        return false;
    }
}
