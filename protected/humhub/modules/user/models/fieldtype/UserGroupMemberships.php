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
        $groupNames = array_map(fn($group) => $group->name, $user->groups);
        $value = implode(', ', $groupNames);

        return $encode ? Html::encode($value) : $value;
    }
}
