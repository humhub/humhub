<?php

use humhub\components\Migration;

/**
 * Class m241022_084028_delete_desktop_notified
 */
class m241022_084028_delete_desktop_notified extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeDropColumn('notification', 'desktop_notified');
        $this->delete('contentcontainer_setting', [
            'name' => 'enable_html5_desktop_notifications',
            'module_id' => 'notification',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241022_084028_delete_desktop_notified cannot be reverted.\n";

        return false;
    }
}
