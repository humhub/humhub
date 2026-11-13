<?php

use humhub\components\Migration;

/**
 * Adds an index on `activity.created_at`.
 *
 * The mail summary (humhub\modules\activity\components\MailSummary) and the
 * activity date-range queries (ActiveQueryActivity::dateRange()) filter on
 * `activity.created_at`, which had no index: every per-user summary check
 * scanned the whole activity table.
 *
 * Class m261108_100000_activity_created_at_index
 */
class m261108_100000_activity_created_at_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateIndex('idx_activity_created_at', 'activity', ['created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropIndex('idx_activity_created_at', 'activity');
    }
}
