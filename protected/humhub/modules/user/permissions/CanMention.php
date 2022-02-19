<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\permissions;

use Yii;
use humhub\modules\user\models\User;

/**
 * Can Mention Permission
 */
class CanMention extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        User::USERGROUP_SELF,
        User::USERGROUP_USER,
        User::USERGROUP_FRIEND
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        User::USERGROUP_SELF,
    ];

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->title = \Yii::t('UserModule.base', 'Mentioning');
        $this->description = \Yii::t('UserModule.base', 'Allows users to @mention you. Select "Deny" to allow only friends to mention you.');
    }

    /**
     * @inheritdoc
     */
    public function getDefaultState($groupId)
    {
        // When friendship is disabled, also allow normal members to mention you
        if (!Yii::$app->getModule('friendship')->getIsEnabled()) {
            return self::STATE_ALLOW;
        }

        return parent::getDefaultState($groupId);
    }

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
    protected $moduleId = 'user';

}
