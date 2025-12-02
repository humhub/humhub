<?php

use humhub\components\Migration;

class m251201_073817_rename_proxy_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('setting', ['name' => 'proxyEnabled'], ['name' => 'proxy.enabled', 'module_id' => 'base']);
        $this->update('setting', ['name' => 'proxyServer'], ['name' => 'proxy.server', 'module_id' => 'base']);
        $this->update('setting', ['name' => 'proxyPort'], ['name' => 'proxy.port', 'module_id' => 'base']);
        $this->update('setting', ['name' => 'proxyUser'], ['name' => 'proxy.user', 'module_id' => 'base']);
        $this->update('setting', ['name' => 'proxyPassword'], ['name' => 'proxy.password', 'module_id' => 'base']);
        $this->update('setting', ['name' => 'proxyNoproxy'], ['name' => 'proxy.noproxy', 'module_id' => 'base']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251201_073817_rename_proxy_settings cannot be reverted.\n";

        return false;
    }
}
