<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ApplicationBaseTest extends HumHubDbTestCase
{
    public function testIsDatabaseInstalled()
    {
        $this->assertTrue(Yii::$app->isDatabaseInstalled());
    }
}
