<?php

use yii\db\Migration;

/**
 * Class m221111_100450_rename_profile_url
 */
class m221111_100450_rename_profile_url extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('profile_field', ['title' => 'Website URL'], [
            'internal_name' => 'url',
            'title' => 'Url'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221111_100450_rename_profile_url cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221111_100450_rename_profile_url cannot be reverted.\n";

        return false;
    }
    */
}
