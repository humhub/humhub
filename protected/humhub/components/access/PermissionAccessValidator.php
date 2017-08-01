<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 30.07.2017
 * Time: 04:04
 */

namespace humhub\components\access;


use Yii;
use yii\base\InvalidParamException;

class PermissionAccessValidator extends ActionAccessValidator
{
    public $name = 'permission';

    public $strict = false;

    protected function validate($rule)
    {
        if(Yii::$app->user->isAdmin()) {
            return true;
        }

        if (isset($rule[$this->name]) && !empty($rule[$this->name])) {
            return $this->verifyPermission($rule[$this->name], $rule);
        }

        throw new InvalidParamException('Invalid permission rule provided for action ' . $this->action);
    }

    /**
     * Checks if the user has the given $permission.
     *
     * @param string|string[]|BasePermission $permission
     * @param array $params
     * @param array $rule
     * @return bool true if the given $permission is granted
     */
    protected function verifyPermission($permission, $rule)
    {
        return Yii::$app->user->can($permission, $rule);
    }

    protected function extractActions($rule)
    {
        $actions = null;

        if (isset($rule['actions'])) {
            $actions = $rule['actions'];
        }

        return $actions;
    }
}