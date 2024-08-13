<?php

use yii\db\Migration;

/**
 * Class m200323_162006_fix_visibility
 */
class m200323_162006_fix_visibility extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('space', ['visibility' => 0], 'visibility IS NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200323_162006_fix_visibility cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200323_162006_fix_visibility cannot be reverted.\n";

        return false;
    }
    */
}
