<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\fixtures;

use humhub\models\GlobalId;
use yii\test\ActiveFixture;

class GlobalIdFixture extends ActiveFixture
{
    public $modelClass = GlobalId::class;


    public $depends = [
        ClassMapFixture::class,
    ];
}
