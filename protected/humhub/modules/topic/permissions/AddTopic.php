<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\permissions;


use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

class AddTopic extends BasePermission
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'topic';

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
        return Yii::t('TopicModule.meeting', 'Add Topics');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('TopicModule.permissions', 'Can add new topics');
    }
}
