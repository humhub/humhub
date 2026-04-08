<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\helpers\Html;
use humhub\modules\user\models\User;

/**
 * UserGroups is a virtual profile field that displays the user group memberships
 *
 * @since 1.18
 */
class UserGroupMemberships extends BaseTypeVirtual
{
    /**
     * @inheritdoc
     */
    protected bool $isCacheable = true;

    /**
     * @inheritdoc
     */
    protected function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string
    {
        $value = implode(', ', $user->getGroups()
            ->select('group.name')
            ->andWhere(['group.show_at_directory' => 1])
            ->column());

        return $encode ? Html::encode($value) : $value;
    }
}
