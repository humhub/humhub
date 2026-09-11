<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\components;

use humhub\components\api\SerializeEvent;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\serializers\CommentSerializer;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * @see CommentSerializer
 */
class CommentSerializerTest extends HumHubDbTestCase
{
    private function content(int $postId = 11)
    {
        return Post::findOne(['id' => $postId])->content;
    }

    private function createComment(string $message, int $contentId = 11, ?int $parentCommentId = null): Comment
    {
        $comment = new Comment([
            'message' => $message,
            'content_id' => $contentId,
            'parent_comment_id' => $parentCommentId,
        ]);
        $this->assertTrue($comment->save(), implode(' ', $comment->getFirstErrors()));

        return $comment;
    }

    public function testCommentShape()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Hello **world**');

        $data = CommentSerializer::comment($comment);

        $this->assertSame($comment->id, $data['id']);
        $this->assertSame('Hello **world**', $data['message'], 'The API ships raw markdown, never rendered HTML');
        $this->assertSame(11, $data['contentId']);
        $this->assertNull($data['parentCommentId']);
        $this->assertNotEmpty($data['recordId']);
        // Absolute permalink; the route shape depends on the installation's URL format
        $this->assertStringStartsWith('http', $data['url']);
        $this->assertStringContainsString('comment', $data['url']);
        $this->assertStringContainsString((string)$comment->id, $data['url']);
        $this->assertSame(0, $data['childCount']);

        // camelCase author shape, no snake_case leftovers
        $this->assertSame('Sara Tester', $data['createdBy']['displayName']);
        $this->assertNotEmpty($data['createdBy']['imageUrl']);
        $this->assertArrayNotHasKey('display_name', $data['createdBy']);

        // ISO-8601 with offset, both timestamps present (a client derives "edited" from them)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $data['createdAt']);
        $this->assertSame($data['createdAt'], $data['updatedAt'], 'A fresh comment was never edited');

        $this->assertSame([], $data['files']);

        // Root comments carry a reply preview, replies do not
        $this->assertSame(['total' => 0, 'items' => [], 'hasMore' => false], $data['replies']);

        // Fields a client derives itself must NOT be in the payload
        foreach (['isEdited', 'blocked', 'canAdminDelete', 'attachmentsHtml', 'permalink'] as $absent) {
            $this->assertArrayNotHasKey($absent, $data);
        }

        // Neither may anything that depends on WHO is asking - that is what makes one
        // serialization servable to every reader (see the serializer's own docblock):
        // permissions come from `comment/<id>/permissions`, like state from `like/states`,
        // presence separately.
        foreach (['canEdit', 'canDelete', 'likes'] as $callerSpecific) {
            $this->assertArrayNotHasKey($callerSpecific, $data);
        }
        $this->assertArrayNotHasKey('online', $data['createdBy']);
    }

    public function testUpdatedAtDiffersAfterAnEdit()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Before');

        // Stored datetimes have one-second resolution
        sleep(1);
        $comment->message = 'After';
        $this->assertTrue($comment->save());

        $data = CommentSerializer::comment(Comment::findOne(['id' => $comment->id]));
        $this->assertNotSame($data['createdAt'], $data['updatedAt']);
    }

    public function testTheShapeIsIdenticalForEveryReader()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('By User2');

        $asAuthor = CommentSerializer::comment(Comment::findOne(['id' => $comment->id]));

        $this->becomeUser('User3');
        $asOtherUser = CommentSerializer::comment(Comment::findOne(['id' => $comment->id]));

        Yii::$app->user->logout();
        $asGuest = CommentSerializer::comment(Comment::findOne(['id' => $comment->id]));

        // The whole point of the caller-context split: one serialization, servable to
        // everyone who may read the content - author, another member and a guest alike.
        // Compared as JSON, i.e. as the wire sees it: the payload carries `stdClass`
        // instances (`messageRenderOptions`, `extensions`, so they serialize as `{}`), which
        // a strict array comparison would call different for being different instances.
        $this->assertSame(json_encode($asAuthor), json_encode($asOtherUser));
        $this->assertSame(json_encode($asAuthor), json_encode($asGuest));
    }

    public function testReplyPreviewAndNesting()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root');
        $reply = $this->createComment('Reply', 11, $root->id);

        $data = CommentSerializer::comment(Comment::findOne(['id' => $root->id]));

        $this->assertSame(1, $data['childCount']);
        $this->assertSame(1, $data['replies']['total']);
        $this->assertFalse($data['replies']['hasMore']);
        $this->assertSame($reply->id, $data['replies']['items'][0]['id']);
        $this->assertNull(
            $data['replies']['items'][0]['replies'],
            'Comments nest one level, so a reply carries no reply block of its own',
        );
    }

    public function testWindowCountsAndOrder()
    {
        $this->becomeUser('User2');
        $roots = [];
        for ($i = 1; $i <= 5; $i++) {
            $roots[$i] = $this->createComment('Root ' . $i);
        }
        $this->createComment('A reply', 11, $roots[2]->id);

        $window = CommentSerializer::window($this->content());

        // The newest `commentsPreviewMax` roots, oldest first within the window
        $ids = array_column($window['results'], 'id');
        $this->assertSame([$roots[4]->id, $roots[5]->id], $ids);

        $this->assertSame(6, $window['total'], 'total counts replies too (the badge count)');
        $this->assertSame(5, $window['rootTotal'], 'rootTotal counts root comments only');
        $this->assertSame(3, $window['prevCount']);
        $this->assertSame(0, $window['nextCount']);
    }

    public function testWindowCursorPagingAndPageSizeClamp()
    {
        $this->becomeUser('User2');
        $roots = [];
        for ($i = 1; $i <= 4; $i++) {
            $roots[$i] = $this->createComment('Root ' . $i);
        }

        $previous = CommentSerializer::window($this->content(), null, $roots[3]->id, 'previous', 1);
        $this->assertSame([$roots[1]->id, $roots[2]->id], array_column($previous['results'], 'id'));

        // A non-positive page size must not drop the LIMIT clause and return the whole thread
        $clamped = CommentSerializer::window($this->content(), null, $roots[4]->id, 'previous', 0);
        $this->assertSame([$roots[3]->id], array_column($clamped['results'], 'id'));
    }

    public function testWindowScopedToOneThread()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root');
        $replyA = $this->createComment('Reply A', 11, $root->id);
        $replyB = $this->createComment('Reply B', 11, $root->id);

        $window = CommentSerializer::window($this->content(), Comment::findOne(['id' => $root->id]));

        $this->assertSame([$replyA->id, $replyB->id], array_column($window['results'], 'id'));
        $this->assertSame(2, $window['total'], 'total is scoped to the thread');
        $this->assertSame(1, $window['rootTotal'], 'rootTotal stays content-global');
    }

    /**
     * The batch event is what lets modules attach data without a query per comment — it must
     * fire ONCE for a whole window, with the roots AND their loaded reply previews in the
     * same flat batch.
     */
    public function testSerializeEventFiresOncePerWindowWithTheWholeBatch()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root');
        $reply = $this->createComment('Reply', 11, $root->id);

        $firings = [];
        $handler = function (SerializeEvent $event) use (&$firings) {
            $firings[] = array_map(fn(Comment $c) => $c->id, $event->records);
            foreach ($event->records as $record) {
                $event->addData($record->id, 'testmodule', ['mark' => 'c' . $record->id]);
            }
        };
        Event::on(SerializeEvent::class, SerializeEvent::EVENT_SERIALIZE, $handler);

        try {
            $window = CommentSerializer::window($this->content());
        } finally {
            Event::off(SerializeEvent::class, SerializeEvent::EVENT_SERIALIZE, $handler);
        }

        $this->assertCount(1, $firings, 'One firing for the whole window, not one per comment');
        $this->assertContains($root->id, $firings[0]);
        $this->assertContains($reply->id, $firings[0], 'Reply previews belong to the same batch');

        $serializedRoot = $window['results'][array_search($root->id, array_column($window['results'], 'id'))];
        $this->assertSame(['mark' => 'c' . $root->id], $serializedRoot['extensions']['testmodule']);
        $this->assertSame(
            ['mark' => 'c' . $reply->id],
            $serializedRoot['replies']['items'][0]['extensions']['testmodule'],
        );
    }

    public function testExtensionsSerializeAsAnObjectWhenEmpty()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('No extensions');

        $data = CommentSerializer::comment($comment);
        $this->assertStringContainsString('"extensions":{}', json_encode($data));
    }
}
