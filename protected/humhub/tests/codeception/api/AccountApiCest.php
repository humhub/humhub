<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The caller's own account data
 * (`humhub\modules\user\controllers\api\AccountController`).
 */
class AccountApiCest
{
    public function testCurrentAccount(ApiTester $I)
    {
        $I->wantTo('read my own account');
        $I->amLoggedInAs(1);

        $I->sendGet('account');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 1, 'displayName' => 'Admin Tester', 'contentContainerId' => 1]);
        // The user shape carries neither localized text nor presence — both are client
        // concerns and would make an otherwise cacheable payload per-caller and per-language
        // (see `user\serializers\UserSerializer`).
        foreach (['imageAlt', 'online'] as $absent) {
            Assert::assertSame([], $I->grabDataFromResponseByJsonPath('$.' . $absent));
        }
    }

    public function testBlockedUsers(ApiTester $I)
    {
        $I->wantTo('read the ids of the users I blocked');

        // The blocked user's own `blockForUser()` writes into the BLOCKING user's list
        $admin = User::findOne(['id' => 1]);
        $user1 = User::findOne(['id' => 2]);
        Assert::assertTrue($user1->blockForUser($admin));

        try {
            $I->amLoggedInAs(1);
            $I->sendGet('account/blocked-users');
            $I->seeResponseCodeIs(200);
            $I->seeResponseContainsJson(['results' => [2]]);
        } finally {
            $user1->unblockForUser($admin);
        }
    }

    public function testBlockedUsersIsEmptyWithoutBlocks(ApiTester $I)
    {
        $I->wantTo('see an empty list when I blocked nobody');
        $I->amLoggedInAs(2);

        $I->sendGet('account/blocked-users');
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals(json_encode(['results' => []]));
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('see own-account endpoints stay authenticated-only');

        // Even with guest access enabled platform-wide: a guest has no account
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        $I->sendGet('account');
        $I->seeResponseCodeIs(401);

        $I->sendGet('account/blocked-users');
        $I->seeResponseCodeIs(401);
    }
}
