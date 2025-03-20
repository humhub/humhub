<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use Yii;

class FormatterTest extends Unit
{
    public function testAsShortInteger()
    {
        $testNumbers = [
            1 => '1',
            12 => '12',
            123 => '123',
            999 => '999',
            1000 => '1K',
            1234 => '1K',
            9990 => '9K',
            123456 => '123K',
            123999 => '123K',
            999999 => '999K',
            1234567 => '1M',
            123456789 => '123M',
            123999500 => '124M',
            999999499 => '999M',
            999999500 => '1000M',
            1234567899 => '1B',
            123456789999 => '123B',
            12345678999999 => '12345B',
            999999999499999 => '999999B',
            999999999500000 => '1000000B',
        ];
        foreach ($testNumbers as $numberValue => $result) {
            $this->assertEquals(Yii::$app->formatter->asShortInteger($numberValue), $result);
        }
    }

    public function testAsShortIntegerArabic()
    {
        Yii::$app->formatter->locale = 'ar';

        $testNumbers = [
            1 => '١',
            12 => '١٢',
            123 => '١٢٣',
            999 => '٩٩٩',
            1000 => '١K',
            1234 => '١K',
            9990 => '٩K',
            123456 => '١٢٣K',
            123999 => '١٢٣K',
            999999 => '٩٩٩K',
            1234567 => '١M',
            123456789 => '١٢٣M',
            123999500 => '١٢٤M',
            999999499 => '٩٩٩M',
            999999500 => '١٠٠٠M',
            1234567899 => '١B',
            123456789999 => '١٢٣B',
            12345678999999 => '١٢٣٤٥B',
            999999999499999 => '٩٩٩٩٩٩B',
            999999999500000 => '١٠٠٠٠٠٠B',
        ];
        foreach ($testNumbers as $numberValue => $result) {
            $this->assertEquals(Yii::$app->formatter->asShortInteger($numberValue), $result);
        }
    }
}
