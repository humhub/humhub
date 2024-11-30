<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\libs\BasePermission;

class ContentTestPermission2 extends BasePermission
{
    public $moduleId = 'content';
    public $id = 'content-test-permission';
}
