<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\libs;

use Codeception\Test\Unit;
use humhub\libs\DateHelper;
use humhub\libs\DbDateValidator;
use Yii;

/**
 * Class MimeHelperTest
 */
class DateHelperTest extends Unit
{
    public function _before()
    {
        parent::_before();
        Yii::$app->timeZone = 'Europe/Berlin';
        Yii::$app->formatter->timeZone =  'UTC';
    }

    public function testIsInDBFormat()
    {
        $this->assertFalse(DateHelper::isInDbFormat('2019-12-01'));
        $this->assertTrue(DateHelper::isInDbFormat('2019-12-01 12:30:00'));

        $this->assertFalse(DateHelper::isInDbFormat('2019-13-01'));
        $this->assertFalse(DateHelper::isInDbFormat('2019-13-01 12:30:00'));
    }
}
