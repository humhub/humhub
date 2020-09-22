<?php


use yii\db\Migration;

class m151022_131128_module_fix extends Migration
{
    public function up()
    {
        $this->update('notification', ['module' => 'space'], ['class' => 'humhub\modules\space\notifications\Invite']);
        $this->update('notification', ['module' => 'space'], ['class' => 'humhub\modules\space\notifications\InviteAccepted']);
        $this->update('notification', ['module' => 'space'], ['class' => 'humhub\modules\space\notifications\InviteDeclined']);
        $this->update('notification', ['module' => 'space'], ['class' => 'humhub\modules\space\notifications\ApprovalRequest']);
        $this->update('notification', ['module' => 'space'], ['class' => 'humhub\modules\space\notifications\ApprovalRequestAccepted']);
        $this->update('notification', ['module' => 'space'], ['class' => 'humhub\modules\space\notifications\ApprovalRequestDeclined']);
        $this->update('notification', ['module' => 'admin'], ['class' => 'humhub\modules\admin\notifications\NewVersionAvailable']);
    }

    public function down()
    {
        echo "m151022_131128_module_fix cannot be reverted.\n";

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
