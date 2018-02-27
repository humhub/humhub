<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db\traits;

/**
 * Meta information about time updating for HumHub ActiveRecord's
 * @property string $updatedAt. To set value see `setMetaTimeUpdating()`
 * @property-read  null|string|\yii\db\Expression $updated_at. Only for for internal use.
 */
trait MetaTimeUpdatingTrait
{
    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return (string)$this->updated_at;
    }

    /**
     * @param $timeExpression string to passed into DATETIME column updated_at
     */
    public function setMetaTimeUpdating($timeExpression = 'NOW()')
    {
        $this->updated_at = new \yii\db\Expression($timeExpression);
    }
}
