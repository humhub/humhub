<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\libs\BaseSettingsManager;
use tests\codeception\_support\HumHubDbTestCase;

class BaseSettingsManagerTest extends HumHubDbTestCase
{
    public function testIsDatabaseInstalled()
    {
        $this->assertTrue(BaseSettingsManager::isDatabaseInstalled());
    }
}
