<?php

namespace humhub\modules\activity\tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Checks the index that backs the date-bounded activity queries
 * (MailSummary, ActiveQueryActivity::dateRange()).
 *
 * @see \m261108_100000_activity_created_at_index
 */
class ActivityIndexTest extends HumHubDbTestCase
{
    public function testCreatedAtIsIndexed()
    {
        $columns = Yii::$app->db->createCommand(
            'SELECT COLUMN_NAME FROM information_schema.STATISTICS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :name'
            . ' ORDER BY SEQ_IN_INDEX',
            [':table' => 'activity', ':name' => 'idx_activity_created_at'],
        )->queryColumn();

        $this->assertSame(['created_at'], $columns);
    }
}
