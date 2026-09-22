<?php

use humhub\models\Setting;
use yii\db\Migration;

/**
 * The stream module was dissolved into the content module in 1.20. Its only database setting
 * moves along with it and takes the name the space module already uses for the same thing.
 */
class m260912_101500_stream_module_settings extends Migration
{
    public function safeUp()
    {
        Setting::updateAll(
            ['module_id' => 'content', 'name' => 'defaultStreamSort'],
            ['module_id' => 'stream', 'name' => 'defaultSort'],
        );
    }

    public function safeDown()
    {
        Setting::updateAll(
            ['module_id' => 'stream', 'name' => 'defaultSort'],
            ['module_id' => 'content', 'name' => 'defaultStreamSort'],
        );
    }
}
