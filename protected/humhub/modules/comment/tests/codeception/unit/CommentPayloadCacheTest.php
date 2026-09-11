<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\services\CommentPayloadCache;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Server-side cache of the serialized comment payloads
 * ({@see CommentPayloadCache}) — possible because those payloads are caller-neutral, see
 * `docs/develop/concept-api.md`.
 */
class CommentPayloadCacheTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        Yii::$app->getModule('comment')->payloadCacheTtl = 3600;
    }

    private function content(): Content
    {
        return Post::findOne(['id' => 11])->content;
    }

    private function createComment(string $message, ?int $parentCommentId = null): Comment
    {
        $comment = new Comment([
            'message' => $message,
            'content_id' => 11,
            'parent_comment_id' => $parentCommentId,
        ]);
        $this->assertTrue($comment->save(), implode(' ', $comment->getFirstErrors()));

        return $comment;
    }

    public function testWindowIsServedFromTheCache()
    {
        $this->becomeUser('User2');
        $this->createComment('Cached');

        // Resolved once, outside the measurement: loading the content itself queries, and
        // what is measured here is the serialization the cache is supposed to skip.
        $content = $this->content();

        $first = CommentPayloadCache::window($content);
        $queriesAfterWarmup = $this->countQueries(fn() => CommentPayloadCache::window($content));

        $this->assertSame(json_encode($first), json_encode(CommentPayloadCache::window($content)));
        $this->assertSame(0, $queriesAfterWarmup, 'A cache hit must not query the database at all');
    }

    public function testDifferentWindowsAreCachedSeparately()
    {
        $this->becomeUser('User2');
        $first = $this->createComment('One');
        $this->createComment('Two');

        $unanchored = CommentPayloadCache::window($this->content());
        $paged = CommentPayloadCache::window($this->content(), null, $first->id, 'next', 1);

        $this->assertNotSame(json_encode($unanchored), json_encode($paged));
        // ... and each keeps answering with its own payload
        $this->assertSame(json_encode($unanchored), json_encode(CommentPayloadCache::window($this->content())));
        $this->assertSame(
            json_encode($paged),
            json_encode(CommentPayloadCache::window($this->content(), null, $first->id, 'next', 1)),
        );
    }

    public function testACreatedCommentInvalidatesTheContent()
    {
        $this->becomeUser('User2');
        $this->createComment('First');

        $before = CommentPayloadCache::window($this->content());
        $this->createComment('Second');
        $after = CommentPayloadCache::window($this->content());

        $this->assertSame(1, $before['rootTotal']);
        $this->assertSame(2, $after['rootTotal'], 'A new comment must be visible immediately');
    }

    public function testAnEditedCommentInvalidatesTheContent()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Before');

        CommentPayloadCache::window($this->content());

        $comment->message = 'After';
        $this->assertTrue($comment->save());

        $messages = array_column(CommentPayloadCache::window($this->content())['results'], 'message');
        $this->assertContains('After', $messages);
        $this->assertNotContains('Before', $messages);
    }

    public function testADeletedCommentInvalidatesTheContent()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Doomed');

        $this->assertSame(1, CommentPayloadCache::window($this->content())['rootTotal']);

        $comment->delete();

        $this->assertSame(0, CommentPayloadCache::window($this->content())['rootTotal']);
    }

    public function testAReplyInvalidatesItsRootsPreview()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root');

        $before = CommentPayloadCache::window($this->content());
        $this->assertSame(0, $before['results'][0]['childCount']);

        $this->createComment('Reply', $root->id);

        $after = CommentPayloadCache::window($this->content());
        $this->assertSame(1, $after['results'][0]['childCount'], 'The root preview must reflect the new reply');
        $this->assertSame(1, $after['results'][0]['replies']['total']);
    }

    public function testASingleCommentIsCachedAndInvalidated()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Single');

        $first = CommentPayloadCache::comment($comment);
        $this->assertSame(0, $this->countQueries(fn() => CommentPayloadCache::comment($comment)));
        $this->assertSame(json_encode($first), json_encode(CommentPayloadCache::comment($comment)));

        $comment->message = 'Single, edited';
        $this->assertTrue($comment->save());

        $this->assertSame('Single, edited', CommentPayloadCache::comment($comment)['message']);
    }

    public function testCommentsOfAnotherContentAreUnaffected()
    {
        $this->becomeUser('User2');
        $this->createComment('On content 11');

        $otherPost = new Post(['message' => 'Other content']);
        $otherPost->content->container = Yii::$app->user->getIdentity();
        $this->assertTrue($otherPost->save());

        $content = $this->content();
        $warm = CommentPayloadCache::window($content);

        // Commenting elsewhere must not retire this content's entries
        $otherComment = new Comment(['message' => 'Elsewhere', 'content_id' => $otherPost->content->id]);
        $this->assertTrue($otherComment->save());

        $this->assertSame(0, $this->countQueries(fn() => CommentPayloadCache::window($content)));
        $this->assertSame(json_encode($warm), json_encode(CommentPayloadCache::window($content)));
    }

    public function testTtlZeroDisablesTheCache()
    {
        Yii::$app->getModule('comment')->payloadCacheTtl = 0;
        $this->becomeUser('User2');
        $this->createComment('Uncached');

        $content = $this->content();
        CommentPayloadCache::window($content);

        $this->assertGreaterThan(
            0,
            $this->countQueries(fn() => CommentPayloadCache::window($content)),
            'With the cache disabled every call serializes again',
        );
    }

    /**
     * Number of SELECTs a callable causes.
     *
     * `Com_select` rather than `Questions`: the latter does not count every prepared-statement
     * execution in this session, which understates a serialization. `SHOW STATUS` itself is
     * not a SELECT, so the two bracketing reads need no offset.
     */
    private function countQueries(callable $callable): int
    {
        $before = Yii::$app->db->createCommand('SHOW SESSION STATUS LIKE "Com_select"')->queryOne();
        $callable();
        $after = Yii::$app->db->createCommand('SHOW SESSION STATUS LIKE "Com_select"')->queryOne();

        return max(0, (int)$after['Value'] - (int)$before['Value']);
    }
}
