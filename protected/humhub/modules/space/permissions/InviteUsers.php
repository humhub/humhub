<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\permissions;

use humhub\modules\space\models\Space;

/**
 * Invite new users to space permission
 */
class InviteUsers extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_USER
    ];

    /**
     * @inheritdoc
     */
    protected $title = "邀请用户";

    /**
     * @inheritdoc
     */
    protected $description = "允许用户为此空间邀请新用户";

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

}
