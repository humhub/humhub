<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\stream\actions\GlobalContentStream;
use humhub\modules\stream\models\filters\DefaultStreamFilter;
use humhub\modules\user\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class GlobalContentStreamTest extends HumHubDbTestCase
{
    public function testPublicAndPrivateContent(): void
    {
        self::becomeUser('User2');

        $w1 = $this->createPublicPost();
        $w2 = $this->createPrivatePost();

        $ids = $this->getStreamActionIds(2);
        $this->assertContains($w1, $ids);
        $this->assertContains($w2, $ids);

        // Test again with guest
        self::logout();
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        // Test with guest access enabled
        $userModule->settings->set('auth.allowGuestAccess', true);
        $ids = $this->getStreamActionIds(2);
        $this->assertContains($w1, $ids);
        $this->assertNotContains($w2, $ids);

        // Test with guest access disabled
        $userModule->settings->set('auth.allowGuestAccess', false);
        $ids = $this->getStreamActionIds(2);
        $this->assertNotContains($w1, $ids);
        $this->assertNotContains($w2, $ids);
    }

    public function testDraftContent(): void
    {
        self::becomeUser('User2');
        $draft1Id = $this->createPost('Some Draft', ['visibility' => Content::VISIBILITY_PRIVATE, 'state' => Content::STATE_DRAFT]);

        self::becomeUser('User2');
        $ids = $this->getStreamActionIds(2);

        // Check draft is first for Author
        $this->assertSame($ids[0], $draft1Id);

        // Check draft is not visible for other users
        self::becomeUser('Admin');
        $ids = $this->getStreamActionIds(5);
        $this->assertNotContains($draft1Id, $ids);
    }

    public function testHiddenContent(): void
    {
        self::becomeUser('User2');

        $hiddenPostId = $this->createPost('Hidden Post', ['hidden' => 1]);
        $visiblePostId = $this->createPost('Regular Post');

        // Not in Stream
        $ids = $this->getStreamActionIds(2);
        $this->assertSame($ids[0], $visiblePostId);
        $this->assertNotSame($ids[1] ?? null, $hiddenPostId);

        // Single Stream Entry Request
        $hiddenPostId2 = $this->createPost('Hidden Post 2', ['hidden' => 1]);
        $ids = $this->getStreamActionIds(1);
        $this->assertSame($ids[0], $hiddenPostId2);

        // Show Hidden Only Filter
        $ids = $this->getStreamActionIds(2, [DefaultStreamFilter::FILTER_HIDDEN]);
        $this->assertSame($ids[0], $hiddenPostId2);
        $this->assertSame($ids[1], $hiddenPostId);
    }


    public function testDeletedContent(): void
    {
        self::becomeUser('User2');
        $deleteId = $this->createPost('Something to delete', ['visibility' => Content::VISIBILITY_PRIVATE]);

        $content = Content::findOne(['id' => $deleteId]);
        $content->softDelete();

        $ids = $this->getStreamActionIds(3);

        // Deleted Content should not appear in stream
        $this->assertNotContains($deleteId, $ids);
    }

    private function getStreamActionIds($limit = 4, $filters = []): array
    {
        $action = new GlobalContentStream('stream', Yii::$app->controller, [
            'limit' => $limit,
            'filters' => $filters
        ]);

        $wallEntries = $action->getStreamQuery()->all();

        return array_map(static function ($entry) {
            return $entry->id;
        }, $wallEntries);
    }

    private function createPrivatePost(): int
    {
        return $this->createPost('Private Post', ['visibility' => Content::VISIBILITY_PRIVATE]);
    }

    private function createPublicPost(): int
    {
        return $this->createPost('Public Post', ['visibility' => Content::VISIBILITY_PUBLIC]);
    }

    private function createPost($message, $content = []): int
    {
        if (!isset($content['visibility'])) {
            $content['visibility'] = Content::VISIBILITY_PRIVATE;
        }
        if (!isset($content['state'])) {
            $content['state'] = Content::STATE_PUBLISHED;
        }

        $post = new Post();
        $post->message = $message;
        $post->content->setAttributes($content, false);
        $post->save();

        return $post->content->id;
    }
}
