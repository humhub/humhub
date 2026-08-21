<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\services;

use humhub\modules\comment\components\SerializeCommentsEvent;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\services\CommentJsonService;
use humhub\modules\content\models\Content;
use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\services\IsOnlineService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\web\ForbiddenHttpException;

class CommentJsonServiceTest extends HumHubDbTestCase
{
    protected function _after()
    {
        Event::off(CommentJsonService::class, CommentJsonService::EVENT_SERIALIZE_COMMENTS);
        parent::_after();
    }

    public function testSerializeCommentShape()
    {
        $this->becomeUser('User2');

        $comment = $this->createComment('Hello world');

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertSame($comment->id, $data['id']);
        $this->assertSame($comment->content_id, $data['contentId']);
        $this->assertNull($data['parentCommentId']);
        $this->assertIsInt($data['recordId']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $data['createdAt'],
        );
        $this->assertFalse($data['isEdited']);
        $this->assertNull($data['updatedAt']);
        $this->assertFalse($data['blocked']);
        $this->assertSame($comment->createdBy->displayName, $data['author']['displayName']);
        $this->assertSame($comment->createdBy->guid, $data['author']['guid']);
        $this->assertNotEmpty($data['author']['guid']);
        $this->assertNotEmpty($data['author']['url']);
        $this->assertNotEmpty($data['author']['imageUrl']);
        $this->assertSame($comment->createdBy->contentcontainer_id, $data['author']['contentContainerId']);
        $this->assertSame(
            'Profile picture of ' . $comment->createdBy->displayName,
            $data['author']['imageAlt'],
        );
        $this->assertArrayHasKey('online', $data['author']);
        $this->assertSame('Hello world', $data['message']);
        // Static config bucket (identical for every comment, no exclude/include/preset
        // configured for this call site) plus the marker/widget attributes the client's
        // display widget auto-boots from - see RichText::outputMarkdownAndRenderOptions().
        $this->assertSame([], $data['messageRenderOptions']->exclude);
        $this->assertSame([], $data['messageRenderOptions']->include);
        $this->assertTrue($data['messageRenderOptions']->{'ui-richtext'});
        $this->assertSame('ui.richtext.prosemirror.RichText', $data['messageRenderOptions']->{'ui-widget'});
        $this->assertIsString($data['attachmentsHtml']);
        $this->assertTrue($data['canEdit']);
        $this->assertTrue($data['canDelete']);
        $this->assertFalse($data['canAdminDelete']);
        $this->assertStringContainsString('http', $data['permalink']);
        $this->assertSame(['total' => 0, 'items' => [], 'hasMore' => false], $data['children']);
        // No SerializeCommentsEvent handler attached in this test - default shape is an
        // empty object (not an empty array) on the wire, see testExtensions* below.
        $this->assertEquals((object)[], $data['extensions']);
    }

    public function testLikesShapeAvailableAndReflectsLikeState()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Likeable comment');

        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertSame(['count' => 0, 'liked' => false], $data['likes']);

        (new LikeService($comment))->like();

        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertSame(['count' => 1, 'liked' => true], $data['likes']);
    }

    public function testIsEditedFlagReflectsUpdate()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Original message');

        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertFalse($data['isEdited']);
        $this->assertNull($data['updatedAt']);

        // Wait a second so created_at != updated_at (same-second updates are not
        // detected, see Comment::isUpdated()).
        sleep(1);
        $comment->message = 'Updated message';
        $this->assertTrue($comment->save());
        $comment->refresh();

        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertTrue($data['isEdited']);
        // Mirrors UpdatedIcon::getByDated($comment->updated_at)'s tooltip source value.
        $this->assertSame(date(DATE_ATOM, strtotime($comment->updated_at)), $data['updatedAt']);
    }

    public function testBlockedAuthorMasksAuthorMessageAndAttachments()
    {
        $this->becomeUser('User1');
        $comment = $this->createComment('Message of blocked author');

        $this->becomeUser('User2');
        $comment->createdBy->blockForUser(Yii::$app->user->getIdentity());

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertTrue($data['blocked']);
        $this->assertNull($data['author']);
        $this->assertNull($data['message']);
        $this->assertNull($data['messageRenderOptions']);
        $this->assertNull($data['attachmentsHtml']);
        // Not masked by the blocked-author rule:
        $this->assertNotNull($data['permalink']);
    }

    public function testShowBlockedRevealsMaskedFields()
    {
        $this->becomeUser('User1');
        $comment = $this->createComment('Message of blocked author');

        $this->becomeUser('User2');
        $comment->createdBy->blockForUser(Yii::$app->user->getIdentity());

        $data = CommentJsonService::create($comment)->serializeComment($comment, showBlocked: true);

        $this->assertFalse($data['blocked']);
        $this->assertNotNull($data['author']);
        $this->assertSame('Message of blocked author', $data['message']);
        $this->assertNotNull($data['messageRenderOptions']);
    }

    public function testChildrenPreviewKeepsSingleLeftoverComment()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root comment');
        $this->createComment('Reply 1', $root);
        $this->createComment('Reply 2', $root);
        $this->createComment('Reply 3', $root);

        // Default commentsPreviewMax is 2, but with exactly one comment beyond the
        // limit all 3 are shown directly instead of behind a "show more" link.
        $data = CommentJsonService::create($root)->serializeComment($root);

        $this->assertSame(3, $data['children']['total']);
        $this->assertCount(3, $data['children']['items']);
        $this->assertFalse($data['children']['hasMore']);
    }

    public function testChildrenPreviewReportsHasMoreBeyondLeftoverRule()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root comment');
        for ($i = 1; $i <= 4; $i++) {
            $this->createComment("Reply $i", $root);
        }

        $data = CommentJsonService::create($root)->serializeComment($root);

        $this->assertSame(4, $data['children']['total']);
        $this->assertCount(2, $data['children']['items']);
        $this->assertTrue($data['children']['hasMore']);
    }

    public function testChildCommentHasNullChildren()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root comment');
        $reply = $this->createComment('Reply', $root);

        $data = CommentJsonService::create($reply)->serializeComment($reply);

        $this->assertNull($data['children']);
        $this->assertSame($root->id, $data['parentCommentId']);
    }

    public function testWindowKeepsSingleLeftoverComment()
    {
        $this->becomeUser('User2');
        $this->createComment('Test comment1');
        $this->createComment('Test comment2');
        $this->createComment('Test comment3');

        $window = CommentJsonService::create($this->post())->serializeWindow();
        $this->assertCount(3, $window['comments']);
        $this->assertSame(3, $window['total']);
        $this->assertSame(0, $window['prevCount']);
        $this->assertSame(0, $window['nextCount']);

        $this->createComment('Test comment4');

        // A second comment beyond the limit cuts the window down to the limit (2)
        // instead of keeping the leftover.
        $window = CommentJsonService::create($this->post())->serializeWindow();
        $this->assertCount(2, $window['comments']);
        $this->assertSame(['Test comment3', 'Test comment4'], $this->plainMessages($window));
        $this->assertSame(4, $window['total']);
        $this->assertSame(2, $window['prevCount']);
        $this->assertSame(0, $window['nextCount']);
    }

    /**
     * `total` counts ALL comments of the content (roots and replies alike, same as the
     * comment-count badge) - `rootTotal` is the root-list-only complement the client's own
     * "show next" pagination gate keys off instead (see CommentList.vue's own docblock,
     * "Root-vs-all total" - this discrepancy, unhandled client-side, used to render a
     * permanently-dead "Show next <reply count> comments" link on any thread with replies).
     */
    public function testWindowRootTotalCountsOnlyRootCommentsWhileTotalCountsAll()
    {
        $this->becomeUser('User2');
        $root1 = $this->createComment('Root 1');
        $root2 = $this->createComment('Root 2');
        $this->createComment('Reply 1', $root1);
        $this->createComment('Reply 2', $root1);
        $this->createComment('Reply 3', $root2);

        $window = CommentJsonService::create($this->post())->serializeWindow();

        $this->assertSame(5, $window['total']);
        $this->assertSame(2, $window['rootTotal']);
    }

    public function testWindowAnchoredAroundPermalinkedComment()
    {
        $this->becomeUser('User2');

        $roots = [];
        for ($i = 1; $i <= 8; $i++) {
            $roots[$i] = $this->createComment('Root comment ' . $i);
        }

        // Anchored window stays focused around the anchor (commentsPreviewMax
        // previous comments + the anchor + 1 next) instead of loading everything.
        $window = CommentJsonService::create($this->post())->serializeWindow(commentId: $roots[5]->id);

        $messages = $this->plainMessages($window);
        $this->assertSame(
            ['Root comment 3', 'Root comment 4', 'Root comment 5', 'Root comment 6'],
            $messages,
        );
        $this->assertSame(2, $window['prevCount']);
        $this->assertSame(2, $window['nextCount']);
        $this->assertSame(8, $window['total']);
    }

    public function testWindowCursorPaginationBothDirections()
    {
        $this->becomeUser('User2');

        $roots = [];
        for ($i = 1; $i <= 5; $i++) {
            $roots[$i] = $this->createComment('Root comment ' . $i);
        }

        // Initial (compact) window: last 2 comments, 3 remaining before them.
        $window = CommentJsonService::create($this->post())->serializeWindow();
        $this->assertSame(['Root comment 4', 'Root comment 5'], $this->plainMessages($window));
        $this->assertSame(3, $window['prevCount']);

        // "Show previous" from the oldest loaded comment (4): only one comment
        // remains beyond the requested page size (2), so it's included directly.
        $firstLoadedId = $window['comments'][0]['id'];
        $prevWindow = CommentJsonService::create($this->post())->serializeWindow(
            commentId: $firstLoadedId,
            direction: 'previous',
            pageSize: 2,
        );
        $this->assertSame(['Root comment 1', 'Root comment 2', 'Root comment 3'], $this->plainMessages($prevWindow));

        // "Show next" from the oldest comment (1) pages forward.
        $nextWindow = CommentJsonService::create($this->post())->serializeWindow(
            commentId: $roots[1]->id,
            direction: 'next',
            pageSize: 2,
        );
        $this->assertSame(['Root comment 2', 'Root comment 3'], $this->plainMessages($nextWindow));
        $this->assertSame(1, $nextWindow['prevCount']);
        $this->assertSame(2, $nextWindow['nextCount']);
    }

    public function testWindowPageSizeIsClampedToModuleMax()
    {
        $this->becomeUser('User2');
        for ($i = 1; $i <= 3; $i++) {
            $this->createComment('Root comment ' . $i);
        }

        $module = Yii::$app->getModule('comment');
        $originalMax = $module->commentsBlockLoadSize;
        $module->commentsBlockLoadSize = 1;

        try {
            $window = CommentJsonService::create($this->post())->serializeWindow(
                commentId: 0,
                direction: 'next',
                pageSize: 100,
            );
            $this->assertCount(1, $window['comments']);
        } finally {
            $module->commentsBlockLoadSize = $originalMax;
        }
    }

    /**
     * A non-positive pageSize must never reach CommentListService::getSiblings() as-is:
     * yii's QueryBuilder drops the `LIMIT` clause entirely for values <= 0, which would
     * fetch every comment of the thread in one guest-reachable request instead of a
     * bounded page.
     */
    public function testWindowPageSizeIsClampedToAtLeastOneForNonPositiveValues()
    {
        $this->becomeUser('User2');

        $roots = [];
        for ($i = 1; $i <= 6; $i++) {
            $roots[$i] = $this->createComment('Root comment ' . $i);
        }

        foreach ([-3, 0] as $pageSize) {
            $window = CommentJsonService::create($this->post())->serializeWindow(
                commentId: 0,
                direction: 'next',
                pageSize: $pageSize,
            );

            $this->assertCount(1, $window['comments'], "pageSize=$pageSize must clamp to exactly 1 item");
            $this->assertSame('Root comment 1', $this->plainMessages($window)[0]);
            $this->assertSame(0, $window['prevCount']);
            $this->assertSame(5, $window['nextCount']);
            $this->assertSame(6, $window['total']);
        }
    }

    public function testCreatedAtMatchesExactInstant()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Timestamp check');

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertSame(date(DATE_ATOM, strtotime($comment->created_at)), $data['createdAt']);
    }

    /**
     * Mirrors `user\widgets\Image::run()`'s own gate: no self online-status is shown for
     * the viewer's own comment, even when the feature is otherwise enabled.
     */
    public function testOnlineStatusNullForViewersOwnComment()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('My own comment');

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertNull($data['author']['online']);
    }

    public function testOnlineStatusReflectsIsOnlineServiceForOtherUsersComment()
    {
        $this->becomeUser('User1');
        $comment = $this->createComment('Other user comment');
        $author = $comment->createdBy;

        $this->becomeUser('User2');
        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertFalse($data['author']['online']);

        (new IsOnlineService($author))->updateStatus();

        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertTrue($data['author']['online']);
    }

    public function testOnlineStatusNullWhenFeatureDisabledByAdminSetting()
    {
        $this->becomeUser('User1');
        $comment = $this->createComment('Other user comment, feature disabled');
        $author = $comment->createdBy;
        (new IsOnlineService($author))->updateStatus();

        $userModule = Yii::$app->getModule('user');
        $userModule->settings->set('auth.hideOnlineStatus', true);

        try {
            $this->becomeUser('User2');
            $data = CommentJsonService::create($comment)->serializeComment($comment);
            $this->assertNull($data['author']['online']);
        } finally {
            $userModule->settings->set('auth.hideOnlineStatus', false);
        }
    }

    public function testCanAdminDeleteTrueForSpaceAdminOnOthersComment()
    {
        $this->becomeUser('Admin');
        $space = new Space(['name' => 'Space admin delete test']);
        $this->assertTrue($space->save());

        $author = User::findOne(['username' => 'User2']);
        $spaceAdmin = User::findOne(['username' => 'User3']);
        $space->addMember($author->id);
        $space->addMember($spaceAdmin->id, groupId: Space::USERGROUP_ADMIN);

        $this->becomeUser('User2');
        $post = new Post();
        $post->message = 'Post in admin-managed space';
        $post->content->setContainer($space);
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->assertTrue($post->save());

        $comment = $this->createComment('Comment by author', null, $post);

        // A space admin can delete (and thus admin-delete) another member's comment, but
        // has no general edit right over it (see ContentAddonActiveRecord::canEdit()).
        $this->becomeUser('User3');
        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertTrue($data['canDelete']);
        $this->assertTrue($data['canAdminDelete']);
        $this->assertFalse($data['canEdit']);
    }

    public function testLikesNullWhenContentArchived()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Archived thread comment');
        $comment->content->archive();

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertNull($data['likes']);
    }

    public function testGuestIsRejectedWhenGuestHideCommentsEnabled()
    {
        $post = $this->publicGuestViewablePost();
        $this->becomeUser('User2');
        $this->createComment('Visible to guests?', null, $post);

        $module = Yii::$app->getModule('comment');
        $module->guestHideComments = true;
        self::allowGuestAccess(true);
        $this->logout();

        try {
            $this->expectException(ForbiddenHttpException::class);
            CommentJsonService::create($post)->serializeWindow();
        } finally {
            $module->guestHideComments = false;
        }
    }

    public function testGuestCanReadCommentsWhenGuestHideCommentsDisabled()
    {
        $post = $this->publicGuestViewablePost();
        $this->becomeUser('User2');
        $comment = $this->createComment('Visible to guests', null, $post);

        self::allowGuestAccess(true);
        $this->logout();

        $window = CommentJsonService::create($post)->serializeWindow();
        $this->assertCount(1, $window['comments']);
        $this->assertNotNull($window['comments'][0]['author']);
        // Guests can never like (no identity), and guestHideComments is disabled here.
        $this->assertNull($window['comments'][0]['likes']);
        // rootTotal is unaffected by the guest path - same single root comment either way.
        $this->assertSame(1, $window['rootTotal']);

        $data = CommentJsonService::create($comment)->serializeComment($comment);
        $this->assertFalse($data['blocked']);
    }

    public function testExtensionsAreEmptyObjectWithoutAHandler()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('No extensions here');

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertEquals((object)[], $data['extensions']);
    }

    public function testExtensionsAppearUnderTheHandlersNamespace()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Extend me');

        Event::on(
            CommentJsonService::class,
            CommentJsonService::EVENT_SERIALIZE_COMMENTS,
            function (SerializeCommentsEvent $event) {
                foreach ($event->comments as $eventComment) {
                    $event->addData($eventComment->id, 'reportcontent', ['reported' => true]);
                }
            },
        );

        $data = CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertSame(['reportcontent' => ['reported' => true]], $data['extensions']);
    }

    public function testExtensionsOnlyAttachToTheTargetedCommentLeavingOthersEmpty()
    {
        $this->becomeUser('User2');
        $targeted = $this->createComment('Targeted comment');
        $untouched = $this->createComment('Untouched comment');

        Event::on(
            CommentJsonService::class,
            CommentJsonService::EVENT_SERIALIZE_COMMENTS,
            function (SerializeCommentsEvent $event) use ($targeted) {
                foreach ($event->comments as $eventComment) {
                    if ($eventComment->id === $targeted->id) {
                        $event->addData($eventComment->id, 'reportcontent', ['reported' => true]);
                    }
                }
            },
        );

        $window = CommentJsonService::create($this->post())->serializeWindow();
        $byId = [];
        foreach ($window['comments'] as $serialized) {
            $byId[$serialized['id']] = $serialized;
        }

        $this->assertSame(['reportcontent' => ['reported' => true]], $byId[$targeted->id]['extensions']);
        $this->assertEquals((object)[], $byId[$untouched->id]['extensions']);
    }

    public function testSerializeWindowFiresTheBatchEventExactlyOnce()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root with replies');
        $this->createComment('Reply 1', $root);
        $this->createComment('Reply 2', $root);
        $this->createComment('Standalone root');

        $fireCount = 0;
        Event::on(
            CommentJsonService::class,
            CommentJsonService::EVENT_SERIALIZE_COMMENTS,
            function () use (&$fireCount) {
                $fireCount++;
            },
        );

        CommentJsonService::create($this->post())->serializeWindow();

        $this->assertSame(1, $fireCount);
    }

    public function testSerializeWindowBatchIncludesLoadedChildPreviews()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root with replies');
        $reply1 = $this->createComment('Reply 1', $root);
        $reply2 = $this->createComment('Reply 2', $root);

        $batchIds = [];
        Event::on(
            CommentJsonService::class,
            CommentJsonService::EVENT_SERIALIZE_COMMENTS,
            function (SerializeCommentsEvent $event) use (&$batchIds) {
                foreach ($event->comments as $eventComment) {
                    $batchIds[] = $eventComment->id;
                }
            },
        );

        CommentJsonService::create($this->post())->serializeWindow();

        // Roots AND their loaded child-preview replies are all part of the single batch.
        $this->assertContains($root->id, $batchIds);
        $this->assertContains($reply1->id, $batchIds);
        $this->assertContains($reply2->id, $batchIds);
    }

    public function testSerializeCommentFiresTheBatchEventExactlyOnce()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Single comment path');

        $fireCount = 0;
        Event::on(
            CommentJsonService::class,
            CommentJsonService::EVENT_SERIALIZE_COMMENTS,
            function () use (&$fireCount) {
                $fireCount++;
            },
        );

        CommentJsonService::create($comment)->serializeComment($comment);

        $this->assertSame(1, $fireCount);
    }

    public function testSerializeCommentOfRootAlsoBatchesItsChildPreviewsInOneEvent()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root with a reply');
        $reply = $this->createComment('A reply', $root);

        $fireCount = 0;
        $batchIds = [];
        Event::on(
            CommentJsonService::class,
            CommentJsonService::EVENT_SERIALIZE_COMMENTS,
            function (SerializeCommentsEvent $event) use (&$fireCount, &$batchIds) {
                $fireCount++;
                foreach ($event->comments as $eventComment) {
                    $batchIds[] = $eventComment->id;
                }
            },
        );

        CommentJsonService::create($root)->serializeComment($root);

        $this->assertSame(1, $fireCount);
        $this->assertContains($root->id, $batchIds);
        $this->assertContains($reply->id, $batchIds);
    }

    /**
     * @return string[] plain-text messages of the comments in a window, in order
     */
    private function plainMessages(array $window): array
    {
        return array_map(
            fn(array $comment) => (string)$comment['message'],
            $window['comments'],
        );
    }

    private function createComment(string $message, ?Comment $parent = null, ?Post $post = null): Comment
    {
        $comment = new Comment([
            'message' => $message,
            'content_id' => ($post ?? $this->post())->content->id,
            'parent_comment_id' => $parent?->id,
        ]);
        $this->assertTrue($comment->save(), 'Could not save comment: ' . json_encode($comment->errors));

        return $comment;
    }

    private function post(): Post
    {
        return Post::findOne(['id' => 11]);
    }

    /**
     * Creates a fresh public post in a fully open space, so it can be read by guests.
     */
    private function publicGuestViewablePost(): Post
    {
        $this->becomeUser('Admin');

        $space = new Space();
        $space->name = 'Guest visible space';
        $space->visibility = Space::VISIBILITY_ALL;
        $this->assertTrue($space->save());

        $post = new Post();
        $post->message = 'Guest visible post';
        $post->content->setContainer($space);
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->assertTrue($post->save());

        return $post;
    }
}
