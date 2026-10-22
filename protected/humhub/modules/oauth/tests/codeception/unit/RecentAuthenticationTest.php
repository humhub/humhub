<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class RecentAuthenticationTest extends HumHubDbTestCase
{
    public function testUsersWithAPasswordMustConfirmIt()
    {
        $user = User::findOne(['id' => 2]);
        $rule = new RecentAuthentication($user);

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
}
