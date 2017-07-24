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
 * Date: 24.07.2017
 * Time: 15:56
 */

namespace humhub\modules\content\tests\codeception\unit;


use humhub\modules\content\models\ContentTag;

class TestTagSameModule extends ContentTag
{
    public $moduleId = 'test';

    public $includeTypeQuery = true;

    public static function getLabel()
    {
        return 'testCategory';
    }

}