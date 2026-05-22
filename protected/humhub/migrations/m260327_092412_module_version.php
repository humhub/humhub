<?php

use humhub\components\Migration;
use humhub\models\ModuleEnabled;

class m260327_092412_module_version extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('module_enabled', 'version', $this->string(16)->notNull());

        foreach (ModuleEnabled::find()->each() as $moduleEnabled) {
            /* @var ModuleEnabled $moduleEnabled */
            $moduleEnabled->version = Yii::$app->getModule($moduleEnabled->module_id)?->version;
            $moduleEnabled->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('module_enabled', 'version');
    }
}
