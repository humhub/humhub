<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\components\SudoMode;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The session state of the sudo mode and the password confirmation (docs/develop/concept-sudo-mode.md)
 */
class SudoModeTest extends HumHubDbTestCase
{
    private const PASSWORD = 'user^humhub@PASS%worD!';

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->delete('user.sudo.failures.2');
        Yii::$app->getModule('user')->settings->delete('auth.sudoIdleMinutes');
    }

    public function testStartsInactiveAndStaysActiveForTheIdleWindow()
    {
        $sudo = new SudoMode(User::findOne(['id' => 2]));
        Yii::$app->session->open();

        try {
            SudoMode::forget();
            $this->assertFalse($sudo->isActive());

            $sudo->touch();
            $this->assertTrue($sudo->isActive());

            // One minute window: a touch 30 seconds ago still counts, one 61 seconds ago does not
            Yii::$app->getModule('user')->settings->set('auth.sudoIdleMinutes', 1);
            Yii::$app->session->set(SudoMode::SESSION_KEY, ['userId' => 2, 'time' => time() - 30]);
            $this->assertTrue($sudo->isActive());
            Yii::$app->session->set(SudoMode::SESSION_KEY, ['userId' => 2, 'time' => time() - 61]);
            $this->assertFalse($sudo->isActive());

            // A protected request refreshes the window
            $sudo->touch();
            $this->assertTrue($sudo->isActive());
        } finally {
            Yii::$app->session->destroy();
        }
    }

    public function testOnlyTheStateOfTheSameUserCounts()
    {
        $sudo = new SudoMode(User::findOne(['id' => 2]));
        Yii::$app->session->open();

        try {
            (new SudoMode(User::findOne(['id' => 1])))->touch();
            $this->assertFalse($sudo->isActive());

            $sudo->touch();
            $this->assertTrue($sudo->isActive());

            SudoMode::forget();
            $this->assertFalse($sudo->isActive());
        } finally {
            Yii::$app->session->destroy();
        }
    }

    public function testIsInactiveWithoutASession()
    {
        $this->assertFalse((new SudoMode(User::findOne(['id' => 2])))->isActive());
    }

    public function testConfirmsWithTheLocalPassword()
    {
        $sudo = new SudoMode(User::findOne(['id' => 2]));
        Yii::$app->session->open();

        try {
            $this->assertTrue($sudo->hasPasswordFactor());
            $this->assertFalse($sudo->confirm('wrong'));
            $this->assertFalse($sudo->confirm(null));
            $this->assertFalse($sudo->confirm(''));
            $this->assertFalse($sudo->isActive());

            $this->assertTrue($sudo->confirm(self::PASSWORD));
            $this->assertTrue($sudo->isActive());
        } finally {
            Yii::$app->session->destroy();
        }
    }

    public function testRepeatedWrongPasswordsAreThrottled()
    {
        $sudo = new SudoMode(User::findOne(['id' => 2]));

        for ($i = 0; $i < SudoMode::MAX_FAILURES; $i++) {
            $this->assertFalse($sudo->isThrottled());
            $this->assertFalse($sudo->confirm('wrong'));
        }

        $this->assertTrue($sudo->isThrottled());
        $this->assertFalse($sudo->confirm(self::PASSWORD));

        Yii::$app->cache->delete('user.sudo.failures.2');
        $this->assertTrue($sudo->confirm(self::PASSWORD));
    }

    public function testSuccessResetsTheFailureCount()
    {
        $sudo = new SudoMode(User::findOne(['id' => 2]));

        for ($i = 0; $i < SudoMode::MAX_FAILURES - 1; $i++) {
            $sudo->confirm('wrong');
        }
        $this->assertTrue($sudo->confirm(self::PASSWORD));
        $this->assertFalse($sudo->confirm('wrong'));
        $this->assertFalse($sudo->isThrottled());
    }

    public function testUsersWithoutAPasswordHaveNoPasswordFactor()
    {
        $user = User::findOne(['id' => 2]);
        $user->currentPassword?->delete();
        $user->refresh();
        $sudo = new SudoMode($user);

        $this->assertFalse($sudo->hasPasswordFactor());
        $this->assertFalse($sudo->confirm(self::PASSWORD));
        $this->assertSame(['/user/auth/login'], $sudo->getReloginRoute());
    }

    public function testTheSettingsDriveWindowAndAdminRequirement()
    {
        $settings = Yii::$app->getModule('user')->settings;
        $this->assertSame(15 * 60, SudoMode::getIdleSeconds());
        $this->assertFalse(SudoMode::isRequiredForAdmin());

        $settings->set('auth.sudoIdleMinutes', 0);
        $this->assertSame(60, SudoMode::getIdleSeconds());
        $settings->set('auth.sudoIdleMinutes', 120);
        $this->assertSame(7200, SudoMode::getIdleSeconds());

        $settings->set('auth.sudoAdmin', 1);
        $this->assertTrue(SudoMode::isRequiredForAdmin());
        $settings->delete('auth.sudoAdmin');
        $settings->delete('auth.sudoIdleMinutes');
    }
}
