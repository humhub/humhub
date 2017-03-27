<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\permissions;

use humhub\modules\space\models\Space;

/**
 * CreateComment Permission
 */
class CreateComment extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
        Space::USERGROUP_USER,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [];

    /**
     * @inheritdoc
     */
    protected $title = 'Create comment';

    /**
     * @inheritdoc
     */
    protected $description = 'Allows the user to add comments';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'comment';

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->title = \Yii::t('CommentModule.permissions', 'Create comment');
        $this->description = \Yii::t('CommentModule.permissions', 'Allows the user to add comments');
    }

}
