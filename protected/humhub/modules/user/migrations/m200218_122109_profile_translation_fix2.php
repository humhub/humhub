<?php

use yii\db\Migration;

/**
 * Class m200217_122108_profile_translation_fix
 */
class m200218_122109_profile_translation_fix2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('profile_field', ['translation_category' => 'UserModule.profile'], ['translation_category' => 'UserModule.models_Profile']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200217_122108_profile_translation_fix cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200217_122108_profile_translation_fix cannot be reverted.\n";

        return false;
    }
    */
}
