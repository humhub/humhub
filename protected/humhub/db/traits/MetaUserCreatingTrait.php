<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db\traits;

use humhub\modules\user\models\User;

/**
 * Meta information about creating for HumHub ActiveRecord's
 * @property null|User $createdBy. To set value see `setMetaCreating()`
 * @property-read  null|string $created_by. Only for for internal use.
 */
trait MetaUserCreatingTrait
{
    /**
     * @see \yii\db\BaseActiveRecord::hasOne()
     * @param $class
     * @param array $link
     * @return null|\yii\db\ActiveQueryInterface
     */
    abstract public function hasOne($class, array $link);

    /**
     * @return User|\yii\db\ActiveQueryInterface|null
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @param $userId string
     * @return bool
     */
    public function setMetaUserCreating($userId)
    {
        $user = User::findOne($userId);
        if ($user === null || !$user->isActive()) {
            return false;
        }
        $this->created_by = $user->id;

        return true;
    }
}
