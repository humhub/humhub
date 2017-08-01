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
 * Date: 27.07.2017
 * Time: 00:06
 */

namespace humhub\tests\codeception\unit\components\access;


use humhub\libs\BasePermission;

class AccessTestPermission1 extends BasePermission
{
    public $moduleId = 'content';

    public $id = 'content-test-permission2';

}