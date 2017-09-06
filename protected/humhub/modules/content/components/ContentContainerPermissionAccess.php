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
 * Time: 04:15
 */

namespace humhub\modules\content\components;


use humhub\components\access\PermissionAccessValidator;

class ContentContainerPermissionAccess extends PermissionAccessValidator
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    protected function verifyPermission($permission, $rule)
    {
        return parent::verifyPermission($permission, $rule) || $this->contentContainer->can($permission, $rule);
    }
}