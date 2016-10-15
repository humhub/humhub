<?php


use yii\db\Migration;

class m150714_093525_activity extends Migration
{

    public function up()
    {
        $this->dropColumn('activity', 'created_by');
        $this->dropColumn('activity', 'created_at');
        $this->dropColumn('activity', 'updated_by');
        $this->dropColumn('activity', 'updated_at');

        $this->update('activity', ['module' => 'comment'], ['class' => 'humhub\modules\comment\activities\NewComment']);
        $this->update('activity', ['module' => 'content'], ['class' => 'humhub\modules\content\activities\ContentCreated']);
        $this->update('activity', ['module' => 'like'], ['class' => 'humhub\modules\like\activities\Liked']);
        $this->update('activity', ['module' => 'space'], ['class' => 'humhub\modules\space\activities\Created']);
        $this->update('activity', ['module' => 'space'], ['class' => 'humhub\modules\space\activities\MemberAdded']);
        $this->update('activity', ['module' => 'space'], ['class' => 'humhub\modules\space\activities\MemberRemoved']);
        $this->update('activity', ['module' => 'user'], ['class' => 'humhub\modules\user\activities\UserFollow']);
    }

    public function down()
    {
        echo "m150714_093525_activity cannot be reverted.\n";

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
