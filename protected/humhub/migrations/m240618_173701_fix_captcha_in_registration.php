<?php

use yii\db\Migration;

/**
 * Class m240618_173701_fix_captcha_in_registration
 */
class m240618_173701_fix_captcha_in_registration extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('setting', ['name' => 'auth.showCaptureInRegisterForm']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240618_173701_fix_captcha_in_registration cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240618_173701_fix_captcha_in_registration cannot be reverted.\n";

        return false;
    }
    */
}
