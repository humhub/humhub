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
        try {
            Yii::$app->db->open();
            $databaseInstalled = true;
        } catch (\Exception $e) {
            $databaseInstalled = false;
        }

        $this->assertTrue($databaseInstalled);
    }
}
