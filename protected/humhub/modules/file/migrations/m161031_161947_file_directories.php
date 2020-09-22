<?php

use yii\db\Migration;

class m161031_161947_file_directories extends Migration
{

    public function up()
    {
        $uploadsDir = Yii::getAlias("@webroot/uploads/file");

        if (is_dir($uploadsDir)) {
            foreach (scandir($uploadsDir) as $guid) {
                $oldDir = $uploadsDir . DIRECTORY_SEPARATOR . $guid;

                if (is_dir($oldDir) && strlen($guid) == 36 && is_writable($oldDir)) {
                    $newDir = $uploadsDir . DIRECTORY_SEPARATOR . substr($guid, 0, 1) . DIRECTORY_SEPARATOR . substr($guid, 1, 1);
                    if (!is_dir($newDir)) {
                        yii\helpers\FileHelper::createDirectory($newDir, 0775, true);
                    }
                    rename($oldDir, $newDir . DIRECTORY_SEPARATOR . $guid);
                }
            }
        }
    }

    public function down()
    {
        echo "m161031_161947_file_directories cannot be reverted.\n";

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
