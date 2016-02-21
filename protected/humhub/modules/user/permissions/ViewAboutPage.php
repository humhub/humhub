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
 * ViewAboutPage Permission
 */
class ViewAboutPage extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        User::USERGROUP_SELF,
        User::USERGROUP_FRIEND,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        User::USERGROUP_SELF
    ];

    /**
     * @inheritdoc
     */
    public function getDefaultState($groupId)
    {
        // When friendship is disabled, also allow normal members to see about page
        if ($groupId == User::USERGROUP_USER && !Yii::$app->getModule('friendship')->getIsEnabled()) {
            return self::STATE_ALLOW;
        }

        return parent::getDefaultState($groupId);
    }

    /**
     * @inheritdoc
     */
    protected $title = "About page";

    /**
     * @inheritdoc
     */
    protected $description = "Allows access to your about page";

    /**
     * @inheritdoc
     */
    protected $moduleId = 'user';

}
