<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\permissions;

use humhub\modules\space\models\Space;

/**
 * CreateComment Permission
 */
class CanLike extends \humhub\libs\BasePermission
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
        Space::USERGROUP_GUEST,
    ];

    /**
     * @inheritdoc
     */
    protected $title = 'Add Like';

    /**
     * @inheritdoc
     */
    protected $description = 'Allows the user to add likes';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'like';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = \Yii::t('LikeModule.permissions', $this->title);
        $this->description = \Yii::t('LikeModule.permissions', $this->description);
    }

}
