<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\DAV\Locks\Backend\PDO;
use yii\db\Connection;

/**
 * The locks of all users in the table `dav_lock`, shared by every node of a cluster.
 * Sabre gives a lock 30 minutes; the daily cleanup removes the expired ones.
 *
 * @since 1.20
 */
class LockBackend extends PDO
{
    public const TABLE = '{{%dav_lock}}';

    public function __construct(Connection $db)
    {
        parent::__construct($db->getMasterPdo());
        $this->tableName = $db->getSchema()->getRawTableName(self::TABLE);
    }
}
