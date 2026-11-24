<?php

use humhub\components\Migration;

/**
 * The module that issued a personal access token for a device (`DeviceConnection`).
 */
class m261122_120000_grant_issuer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('oauth_grant', 'issuer', $this->string(100)->null()->after('type'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('oauth_grant', 'issuer');
    }
}
