<?php

use humhub\libs\DynamicConfig;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

class m250514_125129_reduce_dynamic_config extends Migration
{
    public function safeUp()
    {
        $installed = ArrayHelper::getValue(Yii::$app->params, 'installed');

        if (!YII_ENV_TEST && ($installed || is_null($installed))) {
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
