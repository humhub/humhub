<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\models\RecordMap;
use humhub\modules\comment\notifications\NewCommentNotification;
use humhub\modules\content\notifications\ContentCreatedNotification;
use humhub\modules\like\notifications\NewLikeNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestConfigurableGroupingNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\Group;
use humhub\modules\user\notifications\FollowedNotification;
use humhub\modules\user\notifications\MentionedNotification;
use InvalidArgumentException;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * {@see Grouping} and the group queries {@see \humhub\modules\notification\services\GroupingService}
 * builds from it.
 */
class GroupingTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    /**
     * @var string a minute into the current 15-minute bucket
     */
    private string $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = date('Y-m-d H:i:s', intdiv(time(), 900) * 900 + 60);
    }

    protected function tearDown(): void
    {
        TestConfigurableGroupingNotification::$grouping = null;
        parent::tearDown();
    }

    public function testModifiersReturnNewInstances()
    {
        $base = Grouping::byContent();
        $modified = $base->andSource()->unseenOnly()->withThreshold(3)->withTimeBucket(60);

        $this->assertNotSame($base, $modified);
        $this->assertSame([], $base->getMatched());
        $this->assertFalse($base->isUnseenOnly());
        $this->assertSame(2, $base->getThreshold());
        $this->assertSame(900, $base->getTimeBucket());

        $this->assertSame(['content'], $modified->getRequired());
        $this->assertSame(['source'], $modified->getMatched());
        $this->assertTrue($modified->isUnseenOnly());
        $this->assertSame(3, $modified->getThreshold());
        $this->assertSame(60, $modified->getTimeBucket());
        $this->assertSame(['source'], $modified->andSource()->getMatched(), 'a column is matched once');
    }

    public function testRejectsAThresholdBelowTwo()
    {
        $this->expectException(InvalidArgumentException::class);
        Grouping::bySource()->withThreshold(1);
    }

    public function testRejectsAnEmptyTimeBucket()
    {
        $this->expectException(InvalidArgumentException::class);
        Grouping::bySource()->withTimeBucket(0);
    }

    public function testCoreGroupings()
    {
        $this->assertEquals(Grouping::byContent(), NewCommentNotification::grouping());
        $this->assertEquals(Grouping::byContent()->andSource(), NewLikeNotification::grouping());
        $this->assertEquals(Grouping::byContainer()->andOriginator()->andContentType(), ContentCreatedNotification::grouping());
        $this->assertEquals(Grouping::byClass(), FollowedNotification::grouping());
        $this->assertNull(MentionedNotification::grouping());
    }

    public function testByContentRequiresAContent()
    {
        TestConfigurableGroupingNotification::$grouping = Grouping::byContent();

        $this->insert(2);
        $this->insert(3);
        $this->assertSame([1, 1], $this->groupSizes(), 'without a content nothing is grouped');

        $post = Post::findOne(['id' => 1]);
        $this->insert(2, contentId: $post->content->id);
        $this->insert(3, contentId: $post->content->id);
        $this->assertSame([2, 1, 1], $this->groupSizes());
    }

    public function testAndSourceMatchesNoSourceWithNoSource()
    {
        TestConfigurableGroupingNotification::$grouping = Grouping::byContent()->andSource();
        $post = Post::findOne(['id' => 1]);
        $group = Group::findOne(['id' => 1]);

        // two about the content itself, one about another record
        $this->insert(2, contentId: $post->content->id);
        $this->insert(3, contentId: $post->content->id, sourceRecordId: RecordMap::getId($group));
        $this->insert(4, contentId: $post->content->id);

        $this->assertSame([2, 1], $this->groupSizes());
    }

    public function testBySourceUnseenOnly()
    {
        TestConfigurableGroupingNotification::$grouping = Grouping::bySource()->unseenOnly();
        $sourceId = RecordMap::getId(Group::findOne(['id' => 1]));

        $first = $this->insert(2, sourceRecordId: $sourceId);
        Notification::updateAll(['seen_at' => $this->now], ['id' => $first->record->id]);
        $this->insert(3, sourceRecordId: $sourceId);
        $this->assertSame([1, 1], $this->groupSizes(), 'a seen notification is not grouped with');

        $this->insert(4, sourceRecordId: $sourceId);
        $this->assertSame([2, 1], $this->groupSizes());
    }

    public function testThresholdAndOriginator()
    {
        TestConfigurableGroupingNotification::$grouping = Grouping::byClass()->andOriginator()->withThreshold(3);

        $this->insert(2);
        $this->insert(2);
        $this->insert(3);
        $this->assertSame([1, 1, 1], $this->groupSizes(), 'below the threshold');

        $this->insert(2);
        $this->assertSame([3, 1], $this->groupSizes());
    }

    public function testTimeBucket()
    {
        TestConfigurableGroupingNotification::$grouping = Grouping::byClass()->withTimeBucket(60);
        $bucket = intdiv(strtotime($this->now), 60) * 60;

        $this->insert(2, createdAt: date('Y-m-d H:i:s', $bucket - 1));
        $this->insert(3, createdAt: date('Y-m-d H:i:s', $bucket + 1));
        $this->assertSame([1, 1], $this->groupSizes());

        $this->insert(4, createdAt: date('Y-m-d H:i:s', $bucket + 2));
        $this->assertSame([2, 1], $this->groupSizes());
    }

    public function testByContainerAndContentType()
    {
        TestConfigurableGroupingNotification::$grouping = Grouping::byContainer()->andContentType();
        $post = Post::findOne(['id' => 1]);
        $other = Post::find()->joinWith('content')
            ->andWhere(['!=', 'content.contentcontainer_id', $post->content->contentcontainer_id])
            ->one();

        $this->insert(2, contentId: $post->content->id, containerId: $post->content->contentcontainer_id);
        $this->insert(3, contentId: $other->content->id, containerId: $other->content->contentcontainer_id);
        $this->assertSame([1, 1], $this->groupSizes(), 'another container');

        $this->insert(4, contentId: $post->content->id, containerId: $post->content->contentcontainer_id);
        $this->assertSame([2, 1], $this->groupSizes());
    }

    private function insert(int $originatorId, ?int $contentId = null, ?int $sourceRecordId = null, ?int $containerId = null, ?string $createdAt = null): \humhub\modules\notification\components\BaseNotification
    {
        $record = new Notification([
            'class' => TestConfigurableGroupingNotification::class,
            'user_id' => 1,
            'originator_id' => $originatorId,
            'content_id' => $contentId,
            'contentcontainer_id' => $containerId,
            'source_record_id' => $sourceRecordId,
            'created_at' => $createdAt ?? $this->now,
        ]);
        $this->assertTrue($record->save());
        $notification = NotificationManager::fromRecord($record);
        $notification->getGroupingService()->afterInsert();

        return $notification;
    }

    /**
     * @return int[] the sizes of the recipient's groups, largest first
     */
    private function groupSizes(): array
    {
        $sizes = array_map(
            fn(Notification $row) => (int)$row->group_count,
            Notification::find()->forUser(1)->andWhere(['notification.class' => TestConfigurableGroupingNotification::class])->grouped()->all(),
        );
        rsort($sizes);

        return $sizes;
    }
}
