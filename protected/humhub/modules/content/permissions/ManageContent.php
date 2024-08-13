<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\permissions;

use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use Yii;

/**
 * Manage content permission for a content container
 * 
 * @since 1.1
 * @author Luke
 */
class ManageContent extends \humhub\libs\BasePermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'content';

    /**
     * @inheritdoc
     */
    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        User::USERGROUP_SELF
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_GUEST,
        Space::USERGROUP_MEMBER,
        Space::USERGROUP_USER,
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
        return  Yii::t('CommentModule.permissions', 'Manage content');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('CommentModule.permissions', 'Can manage (e.g. archive, stick, move or delete) arbitrary content');
    }
}
