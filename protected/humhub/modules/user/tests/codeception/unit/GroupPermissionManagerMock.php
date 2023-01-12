<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\components\permission\BasePermission;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\user\components\GroupPermissionManager;

class GroupPermissionManagerMock extends GroupPermissionManager
{
    /**
     * @inheritdoc
     */
    protected $permissions = [
        2 => [
            ManageUsers::class,
            ManageGroups::class
        ],
        3 => [
            ManageUsers::class,
            ManageGroups::class,
            ManageModules::class,
            ManageSettings::class,
            ManageSpaces::class,
            SeeAdminInformation::class
        ]
    ];

    /**
     * @inheritdoc
     */
    protected function verify(BasePermission $permission)
    {
        $subject = $this->getSubject();
        if ($subject) {
            $permissions = $this->permissions[$subject->id];
            return in_array(get_class($permission), $permissions);
        }
        return false;
    }
}
