<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\models\RecordMap;
use humhub\modules\post\models\Post;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The like API (`humhub\modules\like\controllers\api\LikeController`).
 *
 * Fixture baseline: content 1 is Admin's (user 1) private profile post, content 10 a public
 * post in Space 2 where User1 (user 2) is a member. Likes on content 1 exist in the fixture
 * set, so this suite asserts state transitions rather than absolute totals where they are
 * shared.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class LikeApiCest
{
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    /**
     * The platform-wide record id of a post — what every serialized record carries as
     * `recordId` and what a client passes back here.
     */
    private function recordId(int $postId): int
    {
        return RecordMap::getId(Post::findOne(['id' => $postId]));
    }

    public function testStateAndAddressingModes(ApiTester $I)
    {
        $I->wantTo('read the like state, addressed both ways');
        $I->amLoggedInAs(1);

        $recordId = $this->recordId(1);

        $I->sendGet("like/state?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['liked' => false, 'canLike' => true]);
        $total = (int)$I->grabDataFromResponseByJsonPath('$.total')[0];

        // `model` + `pk` addresses the same record
        $I->sendGet('like/state?model=' . urlencode(Post::class) . '&pk=1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => $total, 'liked' => false, 'canLike' => true]);

        $I->sendGet('like/state?recordId=99999');
        $I->seeResponseCodeIs(404);
    }

    public function testBatchedStates(ApiTester $I)
    {
        $I->wantTo('read the like states of many records in one request');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $visible = $this->recordId(10);   // public post in Space 2, User1 is a member
        $invisible = $this->recordId(1);  // Admin's private profile post

        $I->sendPost("like?recordId=$visible");
        $I->seeResponseCodeIs(200);

        $I->sendGet("like/states?recordIds=$visible,$invisible,999999");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['results' => [$visible => ['total' => 1, 'liked' => true, 'canLike' => true]]]);

        // Records the caller may not see, and ids that resolve to nothing, are simply absent -
        // one invisible record must not cost the client the whole window's states.
        Assert::assertSame([], $I->grabDataFromResponseByJsonPath('$.results.' . $invisible));
        Assert::assertSame([], $I->grabDataFromResponseByJsonPath('$.results.999999'));

        // An empty request is an empty map, not an error, and `results` stays an object
        $I->sendGet('like/states');
        $I->seeResponseCodeIs(200);
        Assert::assertStringContainsString('"results":{}', $I->grabResponse());

        // A record with no likes at all still reports a state (total 0), so a client never
        // has to distinguish "no state" from "no likes"
        $I->sendDelete("like?recordId=$visible");
        $I->seeResponseCodeIs(200);
        $I->sendGet("like/states?recordIds=$visible");
        $I->seeResponseContainsJson(['results' => [$visible => ['total' => 0, 'liked' => false]]]);
    }

    public function testBatchedStatesAreGuestReadable(ApiTester $I)
    {
        $I->wantTo('read like states as a guest');

        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);
        $recordId = $this->recordId(10);

        try {
            $I->sendGet("like/states?recordIds=$recordId");
            $I->seeResponseCodeIs(200);
            // A guest has liked nothing and may like nothing, but sees the count
            $I->seeResponseContainsJson(['results' => [$recordId => ['liked' => false, 'canLike' => false]]]);
        } finally {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);
        }
    }

    public function testLikeAndUnlike(ApiTester $I)
    {
        $I->wantTo('like and unlike a record');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $recordId = $this->recordId(1);

        $I->sendGet("like/state?recordId=$recordId");
        $before = (int)$I->grabDataFromResponseByJsonPath('$.total')[0];

        $I->sendPost("like?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => $before + 1, 'liked' => true, 'canLike' => true]);

        // Liking twice does not double-count
        $I->sendPost("like?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => $before + 1, 'liked' => true]);

        $I->sendDelete("like?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => $before, 'liked' => false]);

        // Unliking is idempotent — removing what was never liked is a success
        $I->sendDelete("like?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => $before, 'liked' => false]);
    }

    public function testUserList(ApiTester $I)
    {
        $I->wantTo('page through the users who liked a record');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        // A fresh record, so the list is deterministic
        $post = new Post(['message' => 'Like list probe']);
        $post->content->container = Yii::$app->user->getIdentity();
        Assert::assertTrue($post->save());
        $recordId = RecordMap::getId($post);

        $I->sendGet("like/users?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['results' => [], 'total' => 0, 'page' => 1, 'pages' => 0]);

        $I->sendPost("like?recordId=$recordId");
        $I->seeResponseCodeIs(200);

        $I->sendGet("like/users?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'total' => 1,
            'page' => 1,
            'pages' => 1,
            // Users are returned directly, not wrapped in like rows
            'results' => [['id' => 1, 'displayName' => 'Admin Tester']],
        ]);
        Assert::assertStringStartsWith('http', $I->grabDataFromResponseByJsonPath('$.results[0].imageUrl')[0]);

        // Oversized page sizes are clamped rather than passed through
        $I->sendGet("like/users?recordId=$recordId&pageSize=9999");
        $I->seeResponseCodeIs(200);
        Assert::assertLessThanOrEqual(100, (int)$I->grabDataFromResponseByJsonPath('$.pageSize')[0]);
    }

    public function testLikingUnviewableContentIsRejected(ApiTester $I)
    {
        $I->wantTo('be refused for content I cannot see');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        // Content 1 is Admin's private profile post
        $recordId = $this->recordId(1);

        $I->sendGet("like/state?recordId=$recordId");
        $I->seeResponseCodeIs(403);

        $I->sendPost("like?recordId=$recordId");
        $I->seeResponseCodeIs(403);
    }

    public function testGuestAccess(ApiTester $I)
    {
        $I->wantTo('see guest access to the like state');

        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);
        $recordId = $this->recordId(10);

        // Reading is allowed for guest-visible content; a guest can never like
        $I->sendGet("like/state?recordId=$recordId");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['liked' => false, 'canLike' => false]);

        $I->sendGet("like/users?recordId=$recordId");
        $I->seeResponseCodeIs(200);

        // Content that is not guest-visible stays denied
        $I->sendGet('like/state?recordId=' . $this->recordId(1));
        $I->seeResponseCodeIs(403);

        // Mutations are never guest-accessible
        $I->sendPost("like?recordId=$recordId");
        $I->seeResponseCodeIs(401);
        $I->sendDelete("like?recordId=$recordId");
        $I->seeResponseCodeIs(401);
    }
}
