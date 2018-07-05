<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\permissions;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * CreatePrivateContent Permission
 */
class CreatePrivateContent extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    protected $moduleId = 'content';


    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
        User::USERGROUP_SELF,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_USER,
        Space::USERGROUP_GUEST,
        User::USERGROUP_SELF,
        User::USERGROUP_FRIEND,
        User::USERGROUP_USER,
        User::USERGROUP_GUEST
    ];

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('SpaceModule.permissions', 'Create private content');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('SpaceModule.permissions', 'Allows the user to create private content');
    }
}
