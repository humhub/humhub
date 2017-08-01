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

class DeprecatedPermissionAccessValidator extends PermissionAccessValidator
{
    public $name = 'permissions';
}