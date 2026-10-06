<?php

use humhub\components\Migration;

/**
 * Adds an index on `live.created_at`, used by the polling lookup query.
 */
class m261006_120000_created_at_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateIndex('idx_created_at', 'live', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropIndex('idx_created_at', 'live');
    }
}
