<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\models\Content;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\space\models\Space;
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

        $w1 = $this->createPublicTestContent();
        $w2 = $this->createPrivateTestContent();

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
        $draft1Id = $this->createTestContent('Some Draft', ['visibility' => Content::VISIBILITY_PRIVATE, 'state' => Content::STATE_DRAFT]);

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

        $hiddenTestContentId = $this->createTestContent('Hidden TestContent', ['hidden' => 1]);
        $visibleTestContentId = $this->createTestContent('Regular TestContent');

        // Not in Stream
        $ids = $this->getStreamActionIds(2);
        $this->assertSame($ids[0], $visibleTestContentId);
        $this->assertNotSame($ids[1] ?? null, $hiddenTestContentId);

        // Single Stream Entry Request
        $hiddenTestContentId2 = $this->createTestContent('Hidden TestContent 2', ['hidden' => 1]);
        $ids = $this->getStreamActionIds(1);
        $this->assertSame($ids[0], $hiddenTestContentId2);

        // Show Hidden Only Filter
        $ids = $this->getStreamActionIds(2, [DefaultStreamFilter::FILTER_HIDDEN]);
        $this->assertSame($ids[0], $hiddenTestContentId2);
        $this->assertSame($ids[1], $hiddenTestContentId);
    }


    public function testDeletedContent(): void
    {
        self::becomeUser('User2');
        $deleteId = $this->createPrivateTestContent();

        $content = Content::findOne(['id' => $deleteId]);
        $content->softDelete();

        $ids = $this->getStreamActionIds(3);

        // Deleted Content should not appear in stream
        $this->assertNotContains($deleteId, $ids);
    }

    public function testLimitToGlobalContent(): void
    {
        self::becomeUser('User2');

        $globalContentId = $this->createPublicTestContent();
        $containerContentId = $this->createTestContent('Container TestContent', ['visibility' => Content::VISIBILITY_PUBLIC], Space::findOne(2));

        $ids = $this->getStreamActionIds(2);
        $this->assertContains($globalContentId, $ids);
        $this->assertNotContains($containerContentId, $ids);
    }

    private function getStreamActionIds($limit = 4, $filters = []): array
    {
        $action = new GlobalContentStream('stream', Yii::$app->controller, [
            'limit' => $limit,
            'filters' => $filters,
        ]);

        $wallEntries = $action->getStreamQuery()->all();

        return array_map(static function ($entry) {
            return $entry->id;
        }, $wallEntries);
    }

    private function createPrivateTestContent(): int
    {
        return $this->createTestContent('Private TestContent', ['visibility' => Content::VISIBILITY_PRIVATE]);
    }

    private function createPublicTestContent(): int
    {
        return $this->createTestContent('Public TestContent', ['visibility' => Content::VISIBILITY_PUBLIC]);
    }

    private function createTestContent($message, $content = [], ?Space $space = null): int
    {
        if (!isset($content['visibility'])) {
            $content['visibility'] = Content::VISIBILITY_PRIVATE;
        }
        if (!isset($content['state'])) {
            $content['state'] = Content::STATE_PUBLISHED;
        }

        $testContent = new TestContent();
        $testContent->message = $message;
        if ($space) {
            $testContent->content->setContainer($space);
        }
        $testContent->content->setAttributes($content, false);
        $testContent->save();

        return $testContent->content->id;
    }
}
