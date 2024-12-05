<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\permissions;

use humhub\libs\BasePermission;
use humhub\modules\content\Module;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use Yii;

/**
 * Manage content permission for a content container
 *
 * @since 1.1
 * @author Luke
 */
class ManageContent extends BasePermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'content';

    protected $defaultState = self::STATE_DENY;

    /**
     * @inheritdoc
     */
    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        User::USERGROUP_SELF,
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
        User::USERGROUP_GUEST,
    ];

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('CommentModule.permissions', 'Manage content');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->contentContainer ?
            Yii::t('CommentModule.permissions', 'Can manage (e.g. archive, stick, move or delete) arbitrary content') :
            Yii::t('CommentModule.permissions', 'Can manage (e.g. edit or delete) all content (even private)');
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->contentContainer) {
            /** @var Module $module */
            $module = Yii::$app->getModule('content');
            if ($module->enableGlobalManageContentPermission) {
                $this->fixedGroups[] = Group::getAdminGroupId();
            }
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     *
     * Note: that this function always returns state self::STATE_ALLOW for the administration
     * group, this behaviour can't be overwritten by means of the configuration.
     */
    public function getDefaultState($groupId)
    {
        if (!$this->contentContainer && $groupId === Group::getAdminGroupId()) {
            /** @var Module $module */
            $module = Yii::$app->getModule('content');
            if ($module->enableGlobalManageContentPermission) {
                return self::STATE_ALLOW;
            }
        }

        return parent::getDefaultState($groupId);
    }
}
