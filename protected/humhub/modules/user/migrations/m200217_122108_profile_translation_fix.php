<?php

use yii\db\Migration;

/**
 * Class m200217_122108_profile_translation_fix
 */
class m200217_122108_profile_translation_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('profile_field', ['translation_category' => 'UserModule.models_Profile'], ['translation_category' => 'UserModule.models_Profile']);
        $this->update('profile_field', ['title' => 'First name'], ['title' => 'Firstname', 'internal_name' => 'firstname']);
        $this->update('profile_field', ['title' => 'Last name'], ['title' => 'Lastname', 'internal_name' => 'lastname']);
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
