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
            9990 => '10K',
            123456 => '123K',
            123999 => '124K',
            899999 => '900K',
            999999 => '1M',
            1234567 => '1M',
            123456789 => '123M',
            123999500 => '124M',
            999999499 => '1B',
            999999500 => '1B',
            1234567899 => '1B',
            123456789999 => '123B',
            12345678999999 => '12346B',
            999999999499999 => '1000000B',
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
            9990 => '١٠K',
            123456 => '١٢٣K',
            123999 => '١٢٤K',
            899999 => '٩٠٠K',
            999999 => '١M',
            1234567 => '١M',
            123456789 => '١٢٣M',
            123999500 => '١٢٤M',
            999999499 => '١B',
            999999500 => '١B',
            1234567899 => '١B',
            123456789999 => '١٢٣B',
            12345678999999 => '١٢٣٤٦B',
            999999999499999 => '١٠٠٠٠٠٠B',
            999999999500000 => '١٠٠٠٠٠٠B',
        ];
        foreach ($testNumbers as $numberValue => $result) {
            $this->assertEquals(Yii::$app->formatter->asShortInteger($numberValue), $result);
        }
    }
}
