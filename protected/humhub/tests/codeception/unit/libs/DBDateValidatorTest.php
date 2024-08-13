<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\libs;


use Yii;
use humhub\libs\DbDateValidator;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Class MimeHelperTest
 */
class DBDateValidatorTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    public function _before()
    {
        parent::_before();
        Yii::$app->timeZone = 'Europe/Berlin';
        Yii::$app->setLanguage('en-US');
        $this->becomeUser('admin');
         Yii::$app->user->identity->setAttribute('time_zone', 'Europe/London');
    }

    public function testInitValues()
    {
        $validator = new DbDateValidator();
        $this->assertEquals('Europe/London', $validator->timeZone);
        $this->assertEquals('short', $validator->format);
        $this->assertEquals('en-US', $validator->locale);
    }

    public function testInDBFormatDateOnly()
    {
        // No time translation, since no time value given
        Yii::$app->setLanguage('sv');
        Yii::$app->timeZone = 'Europe/London'; // Set user tz back to app tz
        $model = new DateValidatorTestModel(['date' => '2019-12-20', 'time' => '12PM']);
        $validator = new DbDateValidator(['timeAttribute' => 'time']);
        $validator->validateAttribute($model, 'date');
        $this->assertEmpty($model->getErrors());
        $this->assertEquals('2019-12-20 12:00:00', $model->date);
    }

    public function testParseDateWithoutTimeValueFormatUS()
    {
        // No time translation, since no time value given
        $model = new DateValidatorTestModel(['date' => '12/1/19']);
        $validator = new DbDateValidator();
        $validator->validateAttribute($model, 'date');
        $this->assertEmpty($model->getErrors());
        $this->assertEquals('2019-12-01 00:00:00', $model->date);
    }

    public function testParseDateWithoutTimeValueFormatDe()
    {
        // No time translation, since no time value given
        Yii::$app->setLanguage('de');
        $model = new DateValidatorTestModel(['date' => '01.12.19']);
        $validator = new DbDateValidator();
        $this->assertEquals('de', $validator->locale);
        $validator->validateAttribute($model, 'date');
        $this->assertEmpty($model->getErrors());
        $this->assertEquals('2019-12-01 00:00:00', $model->date);
    }

    public function testParseDateWithTimeValueFormatUS()
    {
        // Translate from Cairo (UTC +2) to London (UTC + 0)
        Yii::$app->timeZone = 'UTC';
        $model = new DateValidatorTestModel(['date' => '12/1/19', 'time' => '12 PM']);
        $validator = new DbDateValidator(['timeAttribute' => 'time', 'timeZone' => 'Africa/Cairo']);
        $validator->validateAttribute($model, 'date');
        $this->assertEmpty($model->getErrors());
        $this->assertEquals('2019-12-01 10:00:00', $model->date);
    }

    public function testParseDateWithTimeValueFormatDE()
    {
        // Translate from Cairo (UTC +2) to Berlin (UTC + 1)
        Yii::$app->setLanguage('de');
        $model = new DateValidatorTestModel(['date' => '01.12.19', 'time' => '12:00']);
        $validator = new DbDateValidator(['timeAttribute' => 'time', 'timeZone' => 'Africa/Cairo']);
        $validator->validateAttribute($model, 'date');
        $this->assertEmpty($model->getErrors());
        $this->assertEquals('2019-12-01 11:00:00', $model->date);
    }


    public function testDoubleValidation()
    {
        // Ensure that double validation does not translate the value two times
        $model = new DateValidatorTestModel(['date' => '12/1/19', 'time' => '12 PM']);
        $validator = new DbDateValidator(['timeAttribute' => 'time', 'timeZone' => 'Africa/Cairo']);
        $validator->validateAttribute($model, 'date');
        $validator->validateAttribute($model, 'date');
        $this->assertEquals('2019-12-01 11:00:00', $model->date);
    }

    public function testValidateWithUnsetTimeAttribute()
    {
        // No time given, so do not translate
        $model = new DateValidatorTestModel(['date' => '12/1/19']);
        $validator = new DbDateValidator(['timeAttribute' => 'time']);
        $validator->validateAttribute($model, 'date');
        $this->assertEquals('2019-12-01 00:00:00', $model->date);
        $this->assertEmpty($model->getErrors());
    }
}
