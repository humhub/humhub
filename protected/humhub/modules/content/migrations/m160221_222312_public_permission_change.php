<?php


use yii\db\Migration;
use humhub\modules\content\permissions\CreatePublicContent;

class m160221_222312_public_permission_change extends Migration
{

    public function up()
    {
        $this->update('contentcontainer_permission', [
            'permission_id' => CreatePublicContent::className(),
            'class' => CreatePublicContent::className(),
            'module_id' => 'content'
                ], [
            'permission_id' => 'humhub\modules\space\permissions\CreatePublicContent',
            'class' => 'humhub\modules\space\permissions\CreatePublicContent',
            'module_id' => 'space',
        ]);
    }

    public function down()
    {
        echo "m160221_222312_public_permission_change cannot be reverted.\n";

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
