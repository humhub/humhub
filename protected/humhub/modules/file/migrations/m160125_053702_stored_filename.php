<?php


use yii\db\Migration;

class m160125_053702_stored_filename extends Migration
{
    public function up()
    {
        foreach (\humhub\modules\file\models\File::find()->all() as $file) {
            
            $oldFileName = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFileName();
            $newFileName = $file->getPath() . DIRECTORY_SEPARATOR . 'file';

            if (!file_exists($newFileName) && file_exists($oldFileName) && is_writable($file->getPath())) {
                rename($oldFileName, $newFileName);
            }
        }
    }

    public function down()
    {
        echo "m160125_053702_stored_filename cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
