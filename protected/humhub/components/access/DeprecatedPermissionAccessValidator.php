<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\components\access;


use Yii;
use yii\base\InvalidArgumentException;

class DeprecatedPermissionAccessValidator extends PermissionAccessValidator
{
    public $name = 'permissions';
}
