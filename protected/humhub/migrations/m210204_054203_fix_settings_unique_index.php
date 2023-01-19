<?php

use humhub\models\Setting;
use yii\db\Migration;

/**
 * Class m210204_054203_fix_settings_unique_index
 */
class m210204_054203_fix_settings_unique_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Remove old duplicated settings, keep only the latest saved value of each duplicated setting
        $duplicateSettings = Setting::find()
            ->select(['name', 'module_id', 'MAX(id) AS latest_id'])
            ->groupBy(['name', 'module_id'])
            ->having('COUNT(*) > 1')
            ->asArray()
            ->all();
        foreach ($duplicateSettings as $duplicateSetting) {
            $this->delete('setting', 'name=:name AND module_id=:module_id AND id!=:latest_id', [
                ':name' => $duplicateSetting['name'],
                ':module_id' => $duplicateSetting['module_id'],
                ':latest_id' => $duplicateSetting['latest_id'],
            ]);
        }
        // Convert the index(name, module_id) to unique
        $this->dropIndex('unique-setting', 'setting');
        $this->createIndex('unique-setting', 'setting', ['name', 'module_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210204_054203_fix_settings_unique_index cannot be reverted.\n";

        return false;
    }
}
