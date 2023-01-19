<?php

use humhub\components\Migration;


/**
 * Class m201130_073907_default_permissions
 */
class m201130_073908_disable_legacy_richtextparser extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->isInitialInstallation()) {
            $this->insert('setting', [
                'name' => 'richtextCompatMode',
                'value' => 0,
                'module_id' => 'content'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201130_073908_disable_legacy_richtextparser.\n";

        return false;
    }
}
