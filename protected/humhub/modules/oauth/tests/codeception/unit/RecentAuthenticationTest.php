<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\user\models\User;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;

class RecentAuthenticationTest extends HumHubDbTestCase
{
    public function testUsersWithAPasswordMustConfirmIt()
    {
        $user = User::findOne(['id' => 2]);
        $rule = new RecentAuthentication($user, null);

        $this->assertTrue($rule->requiresPassword());
        $this->assertTrue($rule->check('user^humhub@PASS%worD!'));
        $this->assertFalse($rule->check('wrong'));
        $this->assertFalse($rule->check(null));
    }

    public function testUsersWithoutAPasswordNeedAFreshLogin()
    {
        $user = User::findOne(['id' => 2]);
        $user->currentPassword?->delete();
        $user->refresh();

        $rule = new RecentAuthentication($user, loggedInAt: time() - 60);
        $this->assertFalse($rule->requiresPassword());
        $this->assertTrue($rule->check(null));

        $rule = new RecentAuthentication($user, loggedInAt: time() - 3600);
        $this->assertFalse($rule->check(null));

        $rule = new RecentAuthentication($user, loggedInAt: null);
        $this->assertFalse($rule->check(null));
    }

    public function testFreshLoginCheckFailsWithoutAnActiveSession()
    {
        $user = User::findOne(['id' => 2]);
        $user->currentPassword?->delete();
        $user->refresh();

        $this->assertFalse(RecentAuthentication::forCurrentSession($user)->check(null));
    }

    public function testRepeatedWrongPasswordsAreThrottled()
    {
        $user = User::findOne(['id' => 2]);
        Yii::$app->cache->delete('oauth.recentAuthentication.2');
        $rule = new RecentAuthentication($user, null);

        for ($i = 0; $i < RecentAuthentication::MAX_FAILURES; $i++) {
            $this->assertFalse($rule->isThrottled());
            $this->assertFalse($rule->check('wrong'));
        }

        $this->assertTrue($rule->isThrottled());
        $this->assertFalse($rule->check('user^humhub@PASS%worD!'));

        Yii::$app->cache->delete('oauth.recentAuthentication.2');
        $this->assertTrue($rule->check('user^humhub@PASS%worD!'));
    }

    public function testSuccessResetsTheFailureCount()
    {
        $user = User::findOne(['id' => 2]);
        Yii::$app->cache->delete('oauth.recentAuthentication.2');
        $rule = new RecentAuthentication($user, null);

        for ($i = 0; $i < RecentAuthentication::MAX_FAILURES - 1; $i++) {
            $rule->check('wrong');
        }
        $this->assertTrue($rule->check('user^humhub@PASS%worD!'));
        $this->assertFalse($rule->check('wrong'));
        $this->assertFalse($rule->isThrottled());
    }
}
