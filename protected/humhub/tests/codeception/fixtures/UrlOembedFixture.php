<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\fixtures;

use humhub\models\ModuleEnabled;
use humhub\models\UrlOembed;
use yii\test\ActiveFixture;

class UrlOembedFixture extends ActiveFixture
{

    public $modelClass = UrlOembed::class;

}
