<?php

use humhub\components\Migration;

class m170110_152425_space_follow_reset_send_notification extends Migration
{
    public function up()
    {
        // Reset space notification sending in order to enable the new notification system, this was not in use for spaces and users before.
        $this->updateSilent('user_follow', ['send_notifications' => 0], ['object_model' => \humhub\modules\space\models\Space::class]);
        $this->updateSilent('user_follow', ['send_notifications' => 0], ['object_model' => \humhub\modules\user\models\User::class]);
    }

    public function down()
    {
        echo "m170110_152425_space_follow_reset_send_notification cannot be reverted.\n";

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
