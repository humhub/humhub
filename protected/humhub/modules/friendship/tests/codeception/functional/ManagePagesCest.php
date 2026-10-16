<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;

/**
 * The "manage friends" pages act through the `FriendshipButton` island per row since the web
 * `friendship/request` actions were removed in favor of the API (see
 * docs/develop/module-migrate-1.20.md): each list renders the island for the user of the row,
 * with the row's current state inlined.
 */
class ManagePagesCest
{
    public function _before(FunctionalTester $I)
    {
        $I->enableFriendships();
    }

    public function testFriendsListRendersTheIslandPerFriend(FunctionalTester $I)
    {
        $I->wantTo('see my friends list act through the friendship island');

        $user1 = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        Friendship::add($user1, $user2);
        Friendship::add($user2, $user1);

        $I->amUser1();
        $I->amOnRoute('/friendship/manage/list');

        $I->see('User2', '.grid-view');
        $I->seeElement('friendship-button', ['user-id' => (string)$user2->id]);
        Assert::assertStringContainsString(
            '"state":"friends"',
            $I->grabAttributeFrom('friendship-button[user-id="' . $user2->id . '"]', 'props'),
        );
    }

    public function testReceivedRequestsRenderTheIslandPerRequest(FunctionalTester $I)
    {
        $I->wantTo('answer a received friend request through the island');

        $user1 = User::findOne(['username' => 'User1']);
        $user3 = User::findOne(['username' => 'User3']);
        Friendship::add($user3, $user1);

        $I->amUser1();
        $I->amOnRoute('/friendship/manage/requests');

        $I->see('User3', '.grid-view');
        $I->seeElement('friendship-button', ['user-id' => (string)$user3->id]);
        Assert::assertStringContainsString(
            '"state":"requestReceived"',
            $I->grabAttributeFrom('friendship-button[user-id="' . $user3->id . '"]', 'props'),
        );
    }

    public function testSentRequestsRenderTheIslandPerRequest(FunctionalTester $I)
    {
        $I->wantTo('withdraw a sent friend request through the island');

        $user1 = User::findOne(['username' => 'User1']);
        $user3 = User::findOne(['username' => 'User3']);
        Friendship::add($user1, $user3);

        $I->amUser1();
        $I->amOnRoute('/friendship/manage/sent-requests');

        $I->see('User3', '.grid-view');
        $I->seeElement('friendship-button', ['user-id' => (string)$user3->id]);
        Assert::assertStringContainsString(
            '"state":"requestSent"',
            $I->grabAttributeFrom('friendship-button[user-id="' . $user3->id . '"]', 'props'),
        );
    }

    public function testTheWebRequestActionsAreGone(FunctionalTester $I)
    {
        $I->wantTo('confirm the friendship transitions have one way, the API');

        $I->amUser1();
        $I->amOnRoute('/friendship/request/add', ['userId' => 3]);
        $I->seeResponseCodeIs(404);
    }
}
