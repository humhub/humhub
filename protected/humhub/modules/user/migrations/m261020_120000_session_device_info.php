<?php

use humhub\components\Migration;

/**
 * Records when a session was logged in and from which browser, for the active sessions overview
 */
class m261020_120000_session_device_info extends Migration
{
    public function safeUp()
    {
        $this->safeAddColumn('user_http_session', 'logged_in_at', $this->integer()->null());
        $this->safeAddColumn('user_http_session', 'user_agent', $this->string(255)->null());
    }

    public function safeDown()
    {
        $this->safeDropColumn('user_http_session', 'user_agent');
        $this->safeDropColumn('user_http_session', 'logged_in_at');
    }
}
