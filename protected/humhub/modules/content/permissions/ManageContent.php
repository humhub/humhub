<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\permissions;

use humhub\modules\user\models\User;
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
        User::USERGROUP_SELF,
        User::USERGROUP_FRIEND,
        User::USERGROUP_USER,
        User::USERGROUP_GUEST
    ];

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
    protected $title;

    /**
     * @inheritdoc
     */
    protected $description;

    /**
     * @inheritdoc
     */
    protected $moduleId = 'content';

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->title = \Yii::t('CommentModule.permissions', 'Manage content');
        $this->description = \Yii::t('CommentModule.permissions', 'Can manage (e.g. archive, stick or delete) arbitrary content');
    }
}
