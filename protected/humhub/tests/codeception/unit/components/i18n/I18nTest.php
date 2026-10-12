<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\tests\codeception\unit;

use humhub\modules\user\models\User;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use yii\log\Logger;

class I18nTest extends HumHubDbTestCase
{
    public $fixtureConfig = [
        'default',
    ];

    public function testShowMeridian()
    {
        $this->becomeUser('Admin');
        $this->assertEquals('UTC', Yii::$app->formatter->timeZone);
        $this->assertEquals('en-US', Yii::$app->formatter->locale);
        $this->assertTrue(Yii::$app->formatter->isShowMeridiem());

        Yii::$app->formatter->locale = 'de';

        $this->assertFalse(Yii::$app->formatter->isShowMeridiem());

        Yii::$app->i18n->autosetLocale();
        $this->assertEquals('UTC', Yii::$app->formatter->timeZone);
        $this->assertEquals('en-US', Yii::$app->formatter->locale);
    }

    public function testChangeLocale()
    {
        $this->becomeUser('Admin');
        $this->assertEquals('UTC', Yii::$app->formatter->timeZone);
        $this->assertEquals('en-US', Yii::$app->formatter->locale);

        $user = Yii::$app->user->getIdentity()->language = 'de';
        Yii::$app->i18n->autosetLocale();

        $this->assertEquals('de', Yii::$app->formatter->locale);
        $this->assertEquals('de', Yii::$app->language);
    }

    public function testUnknownUserTimeZoneIsReplacedByDefaultTimeZoneAndStored()
    {
        static::logInitialize();
        Yii::$app->settings->set('defaultTimeZone', 'Europe/Berlin');
        $user = User::findOne(['username' => 'Admin']);
        $user->updateAttributes(['time_zone' => 'Europe/Kiew']);

        Yii::$app->i18n->setUserLocale($user);

        $this->assertEquals('Europe/Berlin', Yii::$app->formatter->timeZone);
        $this->assertEquals('Europe/Berlin', $user->getAttribute('time_zone'));
        $this->assertEquals('Europe/Berlin', User::findOne($user->id)->getAttribute('time_zone'));
        $this->assertLogRegexCount(1, '/Europe\/Kiew/', Logger::LEVEL_ERROR);
    }

    public function testUnknownUserAndDefaultTimeZoneFallBackToServerTimeZone()
    {
        Yii::$app->settings->set('defaultTimeZone', 'Europe/Kiew');
        $user = User::findOne(['username' => 'Admin']);
        $user->updateAttributes(['time_zone' => 'Europe/Kiew']);

        Yii::$app->i18n->setUserLocale($user);

        $this->assertEquals(Yii::$app->timeZone, Yii::$app->formatter->timeZone);
        $this->assertEquals(Yii::$app->timeZone, User::findOne($user->id)->getAttribute('time_zone'));
    }

    public function testKnownUserTimeZoneIsKept()
    {
        static::logInitialize();
        $user = User::findOne(['username' => 'Admin']);
        $user->updateAttributes(['time_zone' => 'Europe/Berlin']);

        Yii::$app->i18n->setUserLocale($user);

        $this->assertEquals('Europe/Berlin', Yii::$app->formatter->timeZone);
        $this->assertEquals('Europe/Berlin', User::findOne($user->id)->getAttribute('time_zone'));
        $this->assertNotLogRegex('/time zone/i', Logger::LEVEL_ERROR);
    }
}
