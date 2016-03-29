<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\permissions;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * CreatePublicContent Permission
 */
class CreatePublicContent extends \humhub\libs\BasePermission
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
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_USER,
        User::USERGROUP_SELF,
        User::USERGROUP_FRIEND,
        User::USERGROUP_USER,
        User::USERGROUP_GUEST
    ];

    /**
     * @inheritdoc
     */
    protected $title = 'Create public content';

    /**
     * @inheritdoc
     */
    protected $description = 'Allows the user to create public content';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'content';

    public function __construct($config = array()) {
        parent::__construct($config);
        
        $this->title = \Yii::t('SpaceModule.permissions', 'Create public content');
        $this->description = \Yii::t('SpaceModule.permissions', 'Allows the user to create public content');
    }
    
}
