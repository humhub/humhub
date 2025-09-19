<?php

use humhub\models\Setting;
use yii\db\Migration;

class m250807_194741_remove_cache_settings extends Migration
{
    public function safeUp()
    {
        Setting::deleteAll([
            'AND',
            [
                'OR',
                ['=', 'name', 'cacheClass'],
                ['=', 'name', 'cacheExpireTime'],
                ['=', 'name', 'cacheReloadableScript'],
            ],
            ['=', 'module_id', 'base'],
        ]);
    }

    public function safeDown()
    {
        echo "m250807_194741_remove_cache_settings cannot be reverted.\n";

        return false;
    }
}
