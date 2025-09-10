<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\access\ControllerAccess;
use humhub\components\access\DelegateAccessValidator;
use Yii;

/**
 * Base controller for group managers
 *
 * @author luke
 */
class GroupManagerController extends Controller
{
    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            ['checkCanManageUsers'],
        ];
    }

    /**
     * Check the current user can manage other users
     *
     * @param array $rule
     * @param DelegateAccessValidator $access
     * @return bool
     */
    public function checkCanManageUsers($rule, $access): bool
    {
        if (Yii::$app->user->getIdentity()->canManageUsers()) {
            return true;
        }

        $access->code = 403;
        return false;
    }

}
