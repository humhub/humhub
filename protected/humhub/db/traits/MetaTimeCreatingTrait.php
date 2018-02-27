<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db\traits;

/**
 * Meta information about time creating for HumHub ActiveRecord's
 * @property string $createdAt. To set value see `setMetaCreating()`
 * @property-read  null|string|\yii\db\Expression $created_at. Only for for internal use.
 */
trait MetaTimeCreatingTrait
{
    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return (string)$this->created_at;
    }

    /**
     * @param $timeExpression string to passed into DATETIME column created_at
     */
    public function setMetaTimeCreating($timeExpression = 'NOW()')
    {
        $this->created_at = new \yii\db\Expression($timeExpression);
    }
}
