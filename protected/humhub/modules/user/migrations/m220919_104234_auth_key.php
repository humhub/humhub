<?php

use humhub\components\Migration;

/**
 * Class m220919_104234_auth_key
 */
class m220919_104234_auth_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('user', 'auth_key', $this->string(32)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('user', 'auth_key');
    }
}
