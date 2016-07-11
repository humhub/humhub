<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\permissions;

use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

/**
 * CreatePost Permission
 */
class CreatePost extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
        User::USERGROUP_SELF,
        User::USERGROUP_FRIEND
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_USER,
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
    protected $moduleId = 'post';

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->title = \Yii::t('CommentModule.permissions', 'Create post');
        $this->description = \Yii::t('CommentModule.permissions', 'Allows the user to create posts');
    }

}
