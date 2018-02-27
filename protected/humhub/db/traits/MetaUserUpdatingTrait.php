<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db\traits;

use humhub\modules\user\models\User;

/**
 * Meta information about updating for HumHub ActiveRecord's
 * @property null|User $updatedBy To set value see `setMetaUpdating()`
 * @property-read  null|string $updated_by. Only for for internal use.
 */
trait MetaUserUpdatingTrait
{
    /**
     * @see \yii\db\BaseActiveRecord::hasOne()
     * @param $class
     * @param array $link
     * @return null|\yii\db\ActiveQueryInterface
     */
    abstract public function hasOne($class, $link);

    /**
     * @return User|\yii\db\ActiveQueryInterface|null
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @param $userId string
     * @return bool
     */
    public function setMetaUserUpdating($userId)
    {
        $user = User::findOne($userId);
        if ($user === null || !$user->isActive()) {
            return false;
        }
        $this->updated_by = $user->id;

        return true;
    }
}
