<?php

use humhub\components\Migration;
use humhub\modules\oauth\Module;

/**
 * Tables of the OAuth server, the built-in client of the HumHub app and the server keys.
 */
class m261022_120000_oauth_initial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('oauth_client', [
            'id' => $this->primaryKey(),
            'client_id' => $this->string(80)->notNull(),
            'type' => $this->string(10)->notNull(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text()->null(),
            'owner_user_id' => $this->integer()->null(),
            'secret_hash' => $this->char(64)->null(),
            'redirect_uris' => $this->text()->notNull(),
            'scopes' => $this->text()->notNull(),
            'skip_consent' => $this->boolean()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->safeCreateIndex('ux_oauth_client_client_id', 'oauth_client', 'client_id', true);
        $this->safeAddForeignKey('fk_oauth_client_owner', 'oauth_client', 'owner_user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_oauth_client_created_by', 'oauth_client', 'created_by', 'user', 'id', 'SET NULL', 'CASCADE');

        $this->safeCreateTable('oauth_grant', [
            'id' => $this->primaryKey(),
            'guid' => $this->char(36)->notNull(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->string(10)->notNull(),
            'client_id' => $this->integer()->null(),
            'name' => $this->string(100)->notNull(),
            'scopes' => $this->text()->notNull(),
            'token_hash' => $this->char(64)->null(),
            'token_hint' => $this->char(4)->null(),
            'user_agent' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
            'last_used_at' => $this->integer()->null(),
            'expires_at' => $this->integer()->null(),
        ]);
        $this->safeCreateIndex('ux_oauth_grant_guid', 'oauth_grant', 'guid', true);
        $this->safeCreateIndex('ux_oauth_grant_token_hash', 'oauth_grant', 'token_hash', true);
        $this->safeCreateIndex('idx_oauth_grant_expires_at', 'oauth_grant', 'expires_at');
        $this->safeCreateIndex('idx_oauth_grant_created_at', 'oauth_grant', 'created_at');
        $this->safeAddForeignKey('fk_oauth_grant_user', 'oauth_grant', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->safeAddForeignKey('fk_oauth_grant_client', 'oauth_grant', 'client_id', 'oauth_client', 'id', 'CASCADE', 'CASCADE');

        foreach (['oauth_auth_code', 'oauth_access_token', 'oauth_refresh_token'] as $table) {
            $columns = [
                'id' => $this->string(80)->notNull()->append('PRIMARY KEY'),
                'grant_id' => $this->integer()->notNull(),
                'expires_at' => $this->integer()->notNull(),
                'is_revoked' => $this->boolean()->notNull()->defaultValue(0),
            ];
            if ($table === 'oauth_refresh_token') {
                $columns['access_token_id'] = $this->string(80)->null();
            }
            $this->safeCreateTable($table, $columns);
            $this->safeCreateIndex('idx_' . $table . '_expires_at', $table, 'expires_at');
            $this->safeAddForeignKey('fk_' . $table . '_grant', $table, 'grant_id', 'oauth_grant', 'id', 'CASCADE', 'CASCADE');
        }

        $this->safeCreateTable('oauth_web_session_code', [
            'code_hash' => $this->char(64)->notNull()->append('PRIMARY KEY'),
            'grant_id' => $this->integer()->notNull(),
            'expires_at' => $this->integer()->notNull(),
        ]);
        $this->safeCreateIndex('idx_oauth_web_session_code_expires_at', 'oauth_web_session_code', 'expires_at');
        $this->safeAddForeignKey('fk_oauth_web_session_code_grant', 'oauth_web_session_code', 'grant_id', 'oauth_grant', 'id', 'CASCADE', 'CASCADE');

        // A re-run after a failed attempt finds the tables (DDL is not rolled back) and the client
        if (!(new \yii\db\Query())->from('oauth_client')->where(['client_id' => 'humhub-app'])->exists($this->db)) {
            $this->insertSilent('oauth_client', [
                'client_id' => 'humhub-app',
                'type' => 'system',
                'name' => 'HumHub App',
                'redirect_uris' => json_encode(['com.humhub.app:/oauth/callback']),
                'scopes' => json_encode(['full']),
                'skip_consent' => 0,
                'is_active' => 1,
                'created_at' => time(),
            ]);
        }

        Module::instance()->getKeyStore()->generateIfMissing();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['oauth_web_session_code', 'oauth_refresh_token', 'oauth_access_token', 'oauth_auth_code', 'oauth_grant', 'oauth_client'] as $table) {
            $this->safeDropTable($table);
        }
    }
}
