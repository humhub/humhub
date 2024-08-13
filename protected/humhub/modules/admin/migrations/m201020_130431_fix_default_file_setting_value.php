<?php

use yii\db\Migration;

/**
 * Class m201020_130431_fix_default_file_setting_value
 */
class m201020_130431_fix_default_file_setting_value extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('setting', ['name' => 'excludeMediaFilesPreview'], ['name' => 'hideImageFileInfo', 'module_id' => 'file']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201020_130431_fix_default_file_setting_value cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201020_130431_fix_default_file_setting_value cannot be reverted.\n";

        return false;
    }
    */
}
