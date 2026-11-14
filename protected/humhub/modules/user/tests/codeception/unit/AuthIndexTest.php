<?php

namespace humhub\modules\user\tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Checks the index that backs the auth client login lookup
 * `Auth::findOne(['source' => ..., 'source_id' => ...])`.
 *
 * @see \m261108_100200_user_auth_source_index
 */
class AuthIndexTest extends HumHubDbTestCase
{
    public function testSourceIsIndexed()
    {
        $columns = Yii::$app->db->createCommand(
            'SELECT COLUMN_NAME FROM information_schema.STATISTICS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :name'
            . ' ORDER BY SEQ_IN_INDEX',
            [':table' => 'user_auth', ':name' => 'idx_user_auth_source'],
        )->queryColumn();

        $this->assertSame(['source', 'source_id'], $columns);
    }
}
