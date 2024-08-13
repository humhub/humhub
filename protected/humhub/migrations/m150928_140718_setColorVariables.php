<?php

use yii\db\Migration;


class m150928_140718_setColorVariables extends Migration
{
    public function up()
    {
        //\humhub\components\Theme::setColorVariables(Yii::$app->view->theme->name);
    }

    public function down()
    {
        echo "m150928_140718_setColorVariables cannot be reverted.\n";

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
