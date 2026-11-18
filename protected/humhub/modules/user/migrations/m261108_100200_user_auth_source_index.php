<?php

use humhub\components\Migration;

/**
 * Adds an index on `user_auth` (source, source_id).
 *
 * Every login through an auth client resolves the user with
 * `Auth::findOne(['source' => $clientId, 'source_id' => $externalId])`
 * (AuthClientService, AuthClientUserService). The table only had the
 * foreign key index on `user_id`, so each such login scanned the whole
 * table.
 *
 * The index is not unique on purpose: existing installations may hold
 * duplicate rows, and the services handle them.
 *
 * Class m261108_100200_user_auth_source_index
 */
class m261108_100200_user_auth_source_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateIndex('idx_user_auth_source', 'user_auth', ['source', 'source_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropIndex('idx_user_auth_source', 'user_auth');
    }
}
