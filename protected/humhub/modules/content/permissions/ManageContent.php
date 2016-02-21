<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\permissions;

use humhub\modules\space\models\Space;

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
    protected $fixedGroups = [
        Space::USERGROUP_GUEST,
        Space::USERGROUP_MEMBER,
        Space::USERGROUP_USER,
    ];

    /**
     * @inheritdoc
     */
    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
    ];

    /**
     * @inheritdoc
     */
    protected $title = "Manage content";

    /**
     * @inheritdoc
     */
    protected $description = "Can manage (e.g. archive, stick or delete) arbitrary content";

    /**
     * @inheritdoc
     */
    protected $moduleId = 'content';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_DENY;

}
