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

namespace humhub\modules\content\tests\codeception\unit;


use humhub\libs\BasePermission;

class ContentTestPermission1 extends BasePermission
{
    public $moduleId = 'content';

    public $id = 'content-test-permission2';

}