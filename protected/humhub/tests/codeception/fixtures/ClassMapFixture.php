<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\fixtures;

use humhub\models\ClassMap;
use yii\test\ActiveFixture;

class ClassMapFixture extends ActiveFixture
{
    public $modelClass = ClassMap::class;

    public $depends = [
        ModulesEnabledFixture::class,
    ];
}
