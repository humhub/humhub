<?php

use yii\db\Migration;

/**
 * Class m220210_212517_update_settings_mail_font_variable
 */
class m220210_212518_update_settings_mail_font_variable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        Yii::$app->settings->deleteAll('theme.var.');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        echo "m131023_165755_initial does not support migration down.\n";
        return false;
    }
}
