<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\components\api\SerializeEvent;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\CommentDeleted;
use humhub\modules\notification\models\Notification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;
use yii\base\Event;

/**
 * The comment API (`humhub\modules\comment\controllers\api\CommentController`).
 *
 * Fixture baseline: content 1 is Admin's (user 1) private profile post, content 10 a public
 * post in Space 2 where User1 (user 2) is a member. This suite loads no comment fixtures, so
 * every test creates the comments it needs.
 *
 * **One identity per test.** The API base controller replaces the application's user
 * component with a session-less one, after which the test framework can no longer seed a
 * session — so `amLoggedInAs()` must run before the first request of a test and cannot be
 * changed afterwards. A case that needs a second author seeds that comment in-process
 * (see {@see self::seedComment()}) instead of switching identities.
 */
class CommentApiCest
{
    /**
     * Arms the CSRF token pair a session-authenticated write needs, exactly like
     * `humhub.client` does in the browser.
     */
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    private function createComment(ApiTester $I, int $contentId, string $message, ?int $parentCommentId = null): int
    {
        $url = "comment?contentId=$contentId" . ($parentCommentId ? "&parentCommentId=$parentCommentId" : '');
        $I->sendPost($url, ['message' => $message]);
        $I->seeResponseCodeIs(200);

        return (int)$I->grabDataFromResponseByJsonPath('$.id')[0];
    }

    /**
     * Creates a comment row attributed to `$userId`, without going through the model — for
     * cases that need a comment authored by someone other than the session user (see the
     * class docblock), including the guest cases where no identity exists at all and the
     * model's own after-save side effects (notifications, activities) could not run.
     */
    private function seedComment(int $contentId, int $userId, string $message): int
    {
        $now = date('Y-m-d H:i:s');
        Yii::$app->db->createCommand()->insert('comment', [
            'content_id' => $contentId,
            'parent_comment_id' => null,
            'message' => $message,
            'created_at' => $now,
            'created_by' => $userId,
            'updated_at' => $now,
            'updated_by' => $userId,
        ])->execute();

        return (int)Yii::$app->db->getLastInsertID();
    }

    /**
     * A post of the session user's own profile, so a test that asserts absolute comment
     * counts is not affected by comments other tests left on the shared fixture content
     * (this suite shares its database and loads no comment fixtures).
     */
    private function seedContent(int $userId): int
    {
        $post = new Post(['message' => 'API test content']);
        $post->content->container = User::findOne(['id' => $userId]);
        Assert::assertTrue($post->save(), implode(' ', $post->getFirstErrors()));

        return $post->content->id;
    }

    public function testCommentShape(ApiTester $I)
    {
        $I->wantTo('read a single comment in the v2 shape');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $id = $this->createComment($I, 1, 'Comment shape probe');

        $I->sendGet("comment/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $id,
            'message' => 'Comment shape probe',
            'contentId' => 1,
            'parentCommentId' => null,
            'files' => [],
            'childCount' => 0,
            'replies' => ['total' => 0, 'items' => [], 'hasMore' => false],
            'createdBy' => ['id' => 1, 'displayName' => 'Admin Tester', 'contentContainerId' => 1],
        ]);

        // ISO-8601 with offset, not the storage format
        $createdAt = $I->grabDataFromResponseByJsonPath('$.createdAt')[0];
        Assert::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $createdAt);
        Assert::assertSame($createdAt, $I->grabDataFromResponseByJsonPath('$.updatedAt')[0]);

        // camelCase throughout, absolute URLs
        $I->dontSeeResponseContainsJson(['createdBy' => ['display_name' => 'Admin Tester']]);
        Assert::assertStringStartsWith('http', $I->grabDataFromResponseByJsonPath('$.createdBy.imageUrl')[0]);
        Assert::assertStringStartsWith('http', $I->grabDataFromResponseByJsonPath('$.url')[0]);
        Assert::assertNotEmpty($I->grabDataFromResponseByJsonPath('$.recordId')[0]);

        // `extensions` is an object on the wire, never a list
        Assert::assertStringContainsString('"extensions":{}', $I->grabResponse());

        // Fields a client derives itself are absent by design
        foreach (['isEdited', 'blocked', 'canAdminDelete', 'attachmentsHtml', 'permalink'] as $absent) {
            Assert::assertEmpty($I->grabDataFromResponseByJsonPath('$.' . $absent), $absent . ' must not be part of the payload');
        }
    }

    public function testWindowPaginationAndCounts(ApiTester $I)
    {
        $I->wantTo('page through a comment window');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $contentId = $this->seedContent(1);

        $roots = [];
        for ($i = 1; $i <= 4; $i++) {
            $roots[$i] = $this->createComment($I, $contentId, 'Root ' . $i);
        }
        $this->createComment($I, $contentId, 'Reply 1', $roots[2]);

        // Newest `commentsPreviewMax` (2) roots, oldest first inside the window
        $I->sendGet("comment/content/$contentId/window");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'total' => 5,        // all comments including replies (the badge count)
            'rootTotal' => 4,    // root comments only
            'prevCount' => 2,
            'nextCount' => 0,
        ]);
        Assert::assertEquals([$roots[3], $roots[4]], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        // "Show previous" from root 3: both remaining older roots come back, because a single
        // comment beyond the page size is returned directly instead of being hidden behind
        // another "show previous" link ("keep one leftover").
        $I->sendGet("comment/content/$contentId/window?commentId={$roots[3]}&direction=previous&pageSize=1");
        $I->seeResponseCodeIs(200);
        Assert::assertEquals([$roots[1], $roots[2]], $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        $I->seeResponseContainsJson(['prevCount' => 0, 'nextCount' => 2]);

        // A non-positive page size must not drop the LIMIT and return the whole thread
        $I->sendGet("comment/content/$contentId/window?commentId={$roots[4]}&direction=previous&pageSize=0");
        $I->seeResponseCodeIs(200);
        Assert::assertEquals([$roots[3]], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        // An oversized client limit is clamped instead of being passed through
        $I->sendGet("comment/content/$contentId/window?limit=9999");
        $I->seeResponseCodeIs(200);
        Assert::assertLessThanOrEqual(10, count($I->grabDataFromResponseByJsonPath('$.results[*].id')));

        // Reply window of one thread — `total` is scoped to the thread, `rootTotal` stays
        // content-global
        $I->sendGet("comment/parent/{$roots[2]}/window");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => 1, 'rootTotal' => 4]);
        Assert::assertEquals(['Reply 1'], $I->grabDataFromResponseByJsonPath('$.results[*].message'));

        $I->sendGet('comment/parent/99999/window');
        $I->seeResponseCodeIs(404);
    }

    public function testReplyPreviewIsEmbedded(ApiTester $I)
    {
        $I->wantTo('see a root comment carry a preview of its newest replies');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $root = $this->createComment($I, 1, 'Root with replies');
        // Four replies: with `commentsPreviewMax` = 2, three would still be shown in full —
        // a single hidden comment is rendered directly instead of behind a "show more" link.
        foreach (['A', 'B', 'C', 'D'] as $suffix) {
            $this->createComment($I, 1, 'Reply ' . $suffix, $root);
        }

        $I->sendGet("comment/$root");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['childCount' => 4, 'replies' => ['total' => 4, 'hasMore' => true]]);
        // The preview shows the newest two replies, oldest first
        Assert::assertEquals(['Reply C', 'Reply D'], $I->grabDataFromResponseByJsonPath('$.replies.items[*].message'));
        // … and a reply carries no reply block of its own (comments nest one level). A
        // JSONPath match on a null value yields `[null]`, so assert the value, not emptiness.
        Assert::assertNull($I->grabDataFromResponseByJsonPath('$.replies.items[0].replies')[0]);
    }

    public function testCreateUpdateValidationContract(ApiTester $I)
    {
        $I->wantTo('see the 422 validation contract');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        // An empty body still validates — the response must carry the field errors
        $I->sendPost('comment?contentId=1', []);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContainsJson(['errors' => ['message' => ['The comment must not be empty!']]]);

        $root = $this->createComment($I, 1, 'Root');
        $reply = $this->createComment($I, 1, 'Reply', $root);

        // Comments nest at most one level — enforced by the model, surfaced as a 422
        $I->sendPost("comment?contentId=1&parentCommentId=$reply", ['message' => 'Nested']);
        $I->seeResponseCodeIs(422);
        // Error keys are camelCased attribute names, i.e. the field names the client sent
        $I->seeResponseContainsJson(['errors' => ['parentCommentId' => ['Comments can only be nested one level deep.']]]);

        // Update — stored datetimes have one-second resolution, so wait before editing
        sleep(1);
        $I->sendPut("comment/$root", ['message' => 'Edited']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $root, 'message' => 'Edited']);
        Assert::assertNotSame(
            $I->grabDataFromResponseByJsonPath('$.createdAt')[0],
            $I->grabDataFromResponseByJsonPath('$.updatedAt')[0],
            'A client derives "edited" from these two timestamps',
        );

        $I->sendPut("comment/$root", ['message' => '']);
        $I->seeResponseCodeIs(422);

        $I->sendPost('comment?contentId=99999', ['message' => 'No such content']);
        $I->seeResponseCodeIs(404);
    }

    public function testPermissionsAsModerator(ApiTester $I)
    {
        $I->wantTo('read what I may do with someone else\'s comment');

        // Admin may moderate a foreign comment but not edit it (`canManageAllContent` is not
        // granted by default). This is what the entry's context menu asks for when it opens -
        // the comment payload itself carries nothing caller-specific.
        $commentId = $this->seedComment(10, 2, 'By User1');

        $I->amLoggedInAs(1);
        $I->sendGet("comment/$commentId/permissions");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['canEdit' => false, 'canDelete' => true]);
    }

    public function testPermissionsAsAuthor(ApiTester $I)
    {
        $I->wantTo('read what I may do with my own comment');

        $ownCommentId = $this->seedComment(10, 2, 'By User1');
        // On Admin's private profile post, which User1 cannot see at all
        $invisibleCommentId = $this->seedComment(1, 1, 'By Admin');

        $I->amLoggedInAs(2);
        $I->sendGet("comment/$ownCommentId/permissions");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['canEdit' => true, 'canDelete' => true]);

        $I->sendGet("comment/$invisibleCommentId/permissions");
        $I->seeResponseCodeIs(403);

        $I->sendGet('comment/99999/permissions');
        $I->seeResponseCodeIs(404);
    }

    public function testPermissionsRejectGuests(ApiTester $I)
    {
        $I->wantTo('see the permissions endpoint refuse guests');

        // Guests have no permissions to report, so the endpoint is authenticated-only even
        // while the comment itself is guest-readable - the client knows it is a guest and
        // never asks.
        $commentId = $this->seedComment(10, 2, 'Public comment');
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        try {
            $I->sendGet("comment/$commentId");
            $I->seeResponseCodeIs(200);

            $I->sendGet("comment/$commentId/permissions");
            $I->seeResponseCodeIs(401);
        } finally {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);
        }
    }

    public function testDeleteOwnComment(ApiTester $I)
    {
        $I->wantTo('delete my own comment');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $id = $this->createComment($I, 10, 'Mine to delete');

        // 204 — there is nothing left to represent
        $I->sendDelete("comment/$id");
        $I->seeResponseCodeIs(204);
        $I->sendGet("comment/$id");
        $I->seeResponseCodeIs(404);
    }

    public function testModeratedDeleteNotifiesTheAuthor(ApiTester $I)
    {
        $I->wantTo('remove a foreign comment as moderator, notifying its author');

        $commentId = $this->seedComment(10, 2, 'To be moderated');

        $I->amLoggedInAs(1);
        $this->withCsrf($I);
        $I->sendDelete('comment/' . $commentId, ['notify' => 1, 'message' => 'Against the rules']);
        $I->seeResponseCodeIs(204);

        $I->seeRecord(Notification::class, ['class' => CommentDeleted::class, 'user_id' => 2]);
        Assert::assertNull(Comment::findOne(['id' => $commentId]));
    }

    public function testDeleteWithoutPermission(ApiTester $I)
    {
        $I->wantTo('be refused when deleting a comment I may not delete');

        // Admin's comment on Admin's private profile post
        $commentId = $this->seedComment(1, 1, 'Not yours');

        $I->amLoggedInAs(2);
        $this->withCsrf($I);
        $I->sendDelete('comment/' . $commentId);
        $I->seeResponseCodeIs(403);
        Assert::assertNotNull(Comment::findOne(['id' => $commentId]));
    }

    public function testGuestAccess(ApiTester $I)
    {
        $I->wantTo('see guest access follow the platform settings');

        $commentId = $this->seedComment(10, 2, 'Public comment');
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        $I->sendGet('comment/content/10/window');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['results' => [['message' => 'Public comment']]]);

        // A guest reads the very same payload a member does - there is nothing
        // caller-specific left in it
        $I->sendGet('comment/' . $commentId);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['message' => 'Public comment']);

        // Content that is not guest-visible stays denied
        $I->sendGet('comment/content/1/window');
        $I->seeResponseCodeIs(403);

        // Mutations are never guest-accessible
        $I->sendPost('comment?contentId=10', ['message' => 'Guest comment']);
        $I->seeResponseCodeIs(401);
    }

    public function testGuestHideCommentsGate(ApiTester $I)
    {
        $I->wantTo('see the guestHideComments setting hide comments from guests');

        $commentId = $this->seedComment(10, 2, 'Hidden from guests');
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);
        Yii::$app->getModule('comment')->guestHideComments = true;

        $I->sendGet('comment/content/10/window');
        $I->seeResponseCodeIs(403);

        $I->sendGet('comment/' . $commentId);
        $I->seeResponseCodeIs(403);
    }

    /**
     * The batch extension point modules use to attach their own data per comment — it must
     * fire once per response, with the window's roots AND their reply previews in one batch.
     */
    public function testSerializeEventExtensions(ApiTester $I)
    {
        $I->wantTo('attach module extension data to serialized comments');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $root = $this->createComment($I, 1, 'Root for extensions');
        $reply = $this->createComment($I, 1, 'Reply for extensions', $root);

        $firings = [];
        $handler = function (SerializeEvent $event) use (&$firings) {
            if ($event->type !== Comment::class) {
                return;
            }
            $firings[] = array_map(fn(Comment $c) => $c->id, $event->records);
            foreach ($event->records as $record) {
                $event->addData($record->id, 'testmodule', ['mark' => 'c' . $record->id]);
            }
        };
        Event::on(SerializeEvent::class, SerializeEvent::EVENT_SERIALIZE, $handler);

        try {
            $I->sendGet('comment/content/1/window');
            $I->seeResponseCodeIs(200);

            Assert::assertCount(1, $firings, 'One firing for the whole window');
            Assert::assertContains($root, $firings[0]);
            Assert::assertContains($reply, $firings[0], 'Reply previews are part of the same batch');

            $I->seeResponseContainsJson(['results' => [[
                'id' => $root,
                'extensions' => ['testmodule' => ['mark' => 'c' . $root]],
                'replies' => ['items' => [['id' => $reply, 'extensions' => ['testmodule' => ['mark' => 'c' . $reply]]]]],
            ]]]);
        } finally {
            Event::off(SerializeEvent::class, SerializeEvent::EVENT_SERIALIZE, $handler);
        }
    }
}
