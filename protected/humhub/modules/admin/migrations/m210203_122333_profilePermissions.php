<?php

use humhub\components\Migration;

/**
 * Class m210203_122333_profilePermissions
 */
class m210203_122333_profilePermissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Dont change behavior for existing installations
        if (!$this->isInitialInstallation()) {
            $this->insert('setting', [
                'name' => 'enableProfilePermissions',
                'value' => '1',
                'module_id' => 'user'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210203_122333_profilePermissions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210203_122333_profilePermissions cannot be reverted.\n";

        return false;
    }
    */
}
