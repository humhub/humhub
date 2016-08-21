<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\fixtures\modules\custom_pages\template;

use yii\test\ActiveFixture;

class ContainerContentFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\custom_pages\modules\template\models\ContainerContent';
    public $dataFile = '@custom_pages/tests/codeception/fixtures/data/containerContent.php';

}
