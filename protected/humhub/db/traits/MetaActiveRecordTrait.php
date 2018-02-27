<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db\traits;

/**
 * Meta information for HumHub ActiveRecord's
 */
trait MetaActiveRecordTrait
{
    use MetaTimeCreatingTrait,
        MetaTimeUpdatingTrait,
        MetaUserCreatingTrait,
        MetaUserUpdatingTrait;

    public function setMeta($insert, $userId = null, $timeExpression = 'NOW()') {
        if ($insert) {
            $this->setMetaUserCreating($userId);
            $this->setMetaTimeCreating($timeExpression);
        } else {
            $this->setMetaUserUpdating($userId);
            $this->setMetaTimeUpdating($timeExpression);
        }
    }
}
