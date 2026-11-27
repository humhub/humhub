<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\services\CommentFileViewUrls;
use humhub\modules\comment\services\CommentPayloadCache;
use humhub\modules\content\models\Content;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\helpers\Url;

/**
 * The `viewUrl` of a comment's files is added per caller, after the payload cache
 * ({@see CommentFileViewUrls}).
 */
class CommentFileViewUrlsTest extends HumHubDbTestCase
{
    private const HANDLER_USER = 'Admin';

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        Yii::$app->getModule('comment')->payloadCacheTtl = 3600;

        // A handler for one user only, like an editor offered to those who may change the file
        Event::on(FileHandlerCollection::class, FileHandlerCollection::EVENT_INIT, [$this, 'registerTestHandler']);
    }

    protected function _after()
    {
        Event::off(FileHandlerCollection::class, FileHandlerCollection::EVENT_INIT, [$this, 'registerTestHandler']);
        parent::_after();
    }

    public function registerTestHandler(Event $event): void
    {
        /** @var FileHandlerCollection $collection */
        $collection = $event->sender;

        if ($collection->type === FileHandlerCollection::TYPE_VIEW
            && !Yii::$app->user->isGuest
            && Yii::$app->user->identity->username === self::HANDLER_USER) {
            $collection->register(new class extends BaseFileHandler {
                public function getLinkAttributes()
                {
                    return ['label' => 'Test'];
                }
            });
        }
    }

    private function content(): Content
    {
        return Post::findOne(['id' => 11])->content;
    }

    private function createComment(string $message, int $fileCount = 1, ?int $parentCommentId = null): Comment
    {
        $comment = new Comment(['message' => $message, 'content_id' => 11, 'parent_comment_id' => $parentCommentId]);
        $this->assertTrue($comment->save(), implode(' ', $comment->getFirstErrors()));

        for ($i = 0; $i < $fileCount; $i++) {
            $file = new File(['file_name' => "file$i.txt", 'mime_type' => 'text/plain']);
            $this->assertTrue($file->save());
            $comment->fileManager->attach($file);
        }

        return $comment;
    }

    private function viewUrlsOf(array $comment): array
    {
        return array_column($comment['files'], 'viewUrl');
    }

    public function testHandlerOfOneUserShowsUpForThatUserOnlyAlthoughTheWindowIsCached()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('With a file');
        $guid = $comment->fileManager->findStreamFiles()[0]->guid;
        $viewUrl = Url::to(['/file/view', 'guid' => $guid]);

        // Cached for the first caller (no handler for them)
        $user = CommentFileViewUrls::forWindow(CommentPayloadCache::window($this->content()));
        $this->assertArrayNotHasKey('viewUrl', $user['results'][0]['files'][0]);

        $this->becomeUser(self::HANDLER_USER);
        $admin = CommentFileViewUrls::forWindow(CommentPayloadCache::window($this->content()));
        $this->assertSame([$viewUrl], $this->viewUrlsOf($admin['results'][0]));

        // and the cached payload itself stays neutral
        $this->assertArrayNotHasKey('viewUrl', CommentPayloadCache::window($this->content())['results'][0]['files'][0]);

        $this->becomeUser('User2');
        $again = CommentFileViewUrls::forWindow(CommentPayloadCache::window($this->content()));
        $this->assertArrayNotHasKey('viewUrl', $again['results'][0]['files'][0]);
    }

    public function testFileOfAReplyAndASingleComment()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root', 0);
        $reply = $this->createComment('Reply', 1, $root->id);
        $viewUrl = Url::to(['/file/view', 'guid' => $reply->fileManager->findStreamFiles()[0]->guid]);

        $this->becomeUser(self::HANDLER_USER);

        $window = CommentFileViewUrls::forWindow(CommentPayloadCache::window($this->content()));
        $this->assertSame([], $window['results'][0]['files']);
        $this->assertSame([$viewUrl], $this->viewUrlsOf($window['results'][0]['replies']['items'][0]));

        $single = CommentFileViewUrls::forComment(CommentPayloadCache::comment(Comment::findOne($reply->id)));
        $this->assertSame([$viewUrl], $this->viewUrlsOf($single));
    }

    public function testFilesAreLoadedInOneQuery()
    {
        $this->becomeUser('User2');
        $this->createComment('One', 2);
        $this->createComment('Two', 3);
        $window = CommentPayloadCache::window($this->content());

        $this->becomeUser(self::HANDLER_USER);
        $queries = $this->countQueries(fn() => CommentFileViewUrls::forWindow($window));

        $this->assertSame(1, $queries);
    }

    public function testWindowWithoutFilesIsLeftAlone()
    {
        $this->becomeUser('User2');
        $this->createComment('No files', 0);
        $window = CommentPayloadCache::window($this->content());

        $result = null;
        $queries = $this->countQueries(function () use ($window, &$result) {
            $result = CommentFileViewUrls::forWindow($window);
        });

        $this->assertSame(0, $queries);
        $this->assertSame($window, $result);
    }

    private function countQueries(callable $callable): int
    {
        $before = Yii::$app->db->createCommand('SHOW SESSION STATUS LIKE "Com_select"')->queryOne();
        $callable();
        $after = Yii::$app->db->createCommand('SHOW SESSION STATUS LIKE "Com_select"')->queryOne();

        return max(0, (int)$after['Value'] - (int)$before['Value']);
    }
}
