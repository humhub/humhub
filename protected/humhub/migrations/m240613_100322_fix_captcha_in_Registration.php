<?php

use yii\db\Migration;

/**
 * Class m240613_100322_fix_captcha_in_Registration
 */
class m240613_100322_fix_captcha_in_Registration extends Migration
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
        echo "m240613_100322_fix_captcha_in_Registration cannot be reverted.\n";

        return false;
    }

}
