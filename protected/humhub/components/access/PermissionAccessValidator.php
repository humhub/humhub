<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\components\access;

use humhub\libs\BasePermission;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

class PermissionAccessValidator extends ActionAccessValidator
{
    public $name = 'permission';

    public $strict = false;

    protected function validate($rule)
    {
        if (Yii::$app->user->isAdmin()) {
            return true;
        }

        if (isset($rule[$this->name]) && !empty($rule[$this->name])) {
            return $this->verifyPermission($rule[$this->name], $rule);
        }

        throw new InvalidArgumentException('Invalid permission rule provided for action ' . $this->action);
    }

    /**
     * Checks if the user has the given $permission.
     *
     * @param string|string[]|BasePermission $permission
     * @param array $params
     * @return bool true if the given $permission is granted
     * @throws \Throwable
     * @throws InvalidConfigException
     */
    protected function verifyPermission($permission, $params)
    {
        return Yii::$app->user->can($permission, $params);
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
