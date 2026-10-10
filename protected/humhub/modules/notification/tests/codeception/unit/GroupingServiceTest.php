<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\content\models\Content;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestGroupedNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class GroupingServiceTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    /**
     * @var string a minute into the current 15-minute bucket, so that records created "now" never
     * straddle a bucket boundary
     */
    private string $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = date('Y-m-d H:i:s', intdiv(time(), 900) * 900 + 60);
    }

    public function testGroupsAtThresholdAndReKeysToTheNewest()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $this->assertKeys([$a->record->id => $a->record->id]);

        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $this->assertKeys([$a->record->id => $b->record->id, $b->record->id => $b->record->id]);
        $this->assertSame($b->record->id, $b->record->grouping_key);

        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content);
        $this->assertKeys([
            $a->record->id => $c->record->id,
            $b->record->id => $c->record->id,
            $c->record->id => $c->record->id,
        ]);
    }

    public function testNoGroupingWithoutAQuery()
    {
        $content = $this->content(1);
        $a = $this->insert(TestNotification::class, 1, 2, $content);
        $b = $this->insert(TestNotification::class, 1, 3, $content);
        $this->assertKeys([$a->record->id => $a->record->id, $b->record->id => $b->record->id]);
    }

    public function testOtherRecipientIsNeverGrouped()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $b = $this->insert(TestGroupedNotification::class, 2, 3, $content);
        $this->assertKeys([$a->record->id => $a->record->id, $b->record->id => $b->record->id]);
    }

    public function testOtherContentIsNotGrouped()
    {
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $this->content(1));
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $this->content(2));
        $this->assertKeys([$a->record->id => $a->record->id, $b->record->id => $b->record->id]);
    }

    public function testOutsideTheTimeBucketIsNotGrouped()
    {
        $content = $this->content(1);
        $hourAgo = date('Y-m-d H:i:s', strtotime($this->now) - 3600);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content, $hourAgo);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $this->assertKeys([$a->record->id => $a->record->id, $b->record->id => $b->record->id]);

        $tenMinutesLater = date('Y-m-d H:i:s', strtotime($this->now) + 600);
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content, $tenMinutesLater);
        $this->assertKeys([
            $a->record->id => $a->record->id,
            $b->record->id => $c->record->id,
            $c->record->id => $c->record->id,
        ]);
    }

    public function testDeletingBelowThresholdDestroysTheGroup()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $this->assertKeys([$a->record->id => $b->record->id]);

        $this->assertSame(1, $b->record->delete());
        $this->assertKeys([$a->record->id => $a->record->id]);
    }

    public function testDeletingTheNonHeadOfAPairDestroysTheGroup()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);

        $this->assertSame(1, Notification::findOne(['id' => $a->record->id])->delete());
        $this->assertKeys([$b->record->id => $b->record->id]);
    }

    public function testDeletingAnUngroupedNotificationIsANoOp()
    {
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $this->content(1));
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $this->content(2));

        $this->assertSame(1, $a->record->delete());
        $this->assertKeys([$b->record->id => $b->record->id]);
    }

    public function testDeletingTheHeadReKeysToTheNewestRemaining()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content);

        $this->assertSame(1, $c->record->delete());
        $this->assertKeys([$a->record->id => $b->record->id, $b->record->id => $b->record->id]);

        // a non-head of a group of three
        $d = $this->insert(TestGroupedNotification::class, 1, 4, $content);
        $this->assertKeys([$a->record->id => $d->record->id, $b->record->id => $d->record->id]);
        $this->assertSame(1, Notification::findOne(['id' => $a->record->id])->delete());
        $this->assertKeys([$b->record->id => $d->record->id, $d->record->id => $d->record->id]);
    }

    public function testDeletingTheHeadReKeysToTheHighestIdNotTheLatestCreatedAt()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        // inserted last, but with an older `created_at` inside the same bucket
        $older = date('Y-m-d H:i:s', strtotime($this->now) - 30);
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content, $older);
        $d = $this->insert(TestGroupedNotification::class, 1, 8, $content);

        $this->assertSame(1, $d->record->delete());
        $this->assertKeys([
            $a->record->id => $c->record->id,
            $b->record->id => $c->record->id,
            $c->record->id => $c->record->id,
        ]);
    }

    public function testDeletingAGroupedRowActsOnItsOwnRow()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content);

        // row A as a grouped query would represent the group
        $row = Notification::findOne(['id' => $a->record->id]);
        $row->group_max_id = $c->record->id;
        $row->group_count = 3;
        $this->assertSame(1, $row->delete());

        $this->assertNull(Notification::findOne(['id' => $a->record->id]));
        $this->assertKeys([$b->record->id => $c->record->id, $c->record->id => $c->record->id]);
    }

    public function testLoadsTheNewestMemberWithTheGroupCount()
    {
        $content = $this->content(1);
        $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content);

        $rows = Notification::find()->forUser(1)->listed()->grouped()->all();
        $this->assertCount(1, $rows);

        $n = NotificationManager::load($rows[0]);
        $this->assertSame($c->record->id, $n->record->id);
        $this->assertSame(3, $n->groupCount);
        $this->assertSame(4, $n->originator->id);
        $this->assertStringContainsString(User::findOne(['id' => 4])->displayName, $n->asWeb());
        $this->assertStringContainsString('did 3 things', $n->asWeb());
    }

    public function testGroupedUsersExcludeOriginatorAndRecipient()
    {
        $content = $this->content(1);
        $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $head = $this->insert(TestGroupedNotification::class, 1, 1, $content);

        $users = $head->getGroupingService()->getOtherGroupedUsers();
        // neither the originator of the head (the recipient himself here) nor the recipient
        $this->assertSame([3, 2], array_map(fn(User $user) => $user->id, $users));

        $head = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $this->assertSame([2], array_map(fn(User $user) => $user->id, $head->getGroupingService()->getOtherGroupedUsers()));
        $this->assertSame(1, $head->getGroupingService()->countOtherGroupedUsers());
    }

    public function testAfterUpdateRegroupsAMovedNotification()
    {
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $this->content(1));
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $this->content(1));
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $this->content(2));
        $this->assertKeys([$a->record->id => $b->record->id, $c->record->id => $c->record->id]);

        $record = Notification::findOne(['id' => $a->record->id]);
        $record->updateAttributes(['content_id' => $this->content(2)->id]);
        NotificationManager::load($record)->getGroupingService()->afterUpdate();

        $this->assertKeys([
            $a->record->id => $a->record->id,
            $b->record->id => $b->record->id,
            $c->record->id => $a->record->id,
        ]);
    }

    public function testAfterUpdateWithoutAQueryIsANoOp()
    {
        $a = $this->insert(TestNotification::class, 1, 2, $this->content(1));
        $b = $this->insert(TestNotification::class, 1, 3, $this->content(1));

        $a->getGroupingService()->afterUpdate();
        $this->assertKeys([$a->record->id => $a->record->id, $b->record->id => $b->record->id]);
    }

    public function testAfterUpdateMovingTheHeadKeepsTheRemainingGroup()
    {
        $content = $this->content(1);
        $a = $this->insert(TestGroupedNotification::class, 1, 2, $content);
        $b = $this->insert(TestGroupedNotification::class, 1, 3, $content);
        $c = $this->insert(TestGroupedNotification::class, 1, 4, $content);

        $record = Notification::findOne(['id' => $c->record->id]);
        $record->updateAttributes(['content_id' => $this->content(2)->id]);
        NotificationManager::load($record)->getGroupingService()->afterUpdate();

        $this->assertKeys([
            $a->record->id => $b->record->id,
            $b->record->id => $b->record->id,
            $c->record->id => $c->record->id,
        ]);
    }

    private function insert(string $class, int $userId, ?int $originatorId, Content $content, ?string $createdAt = null): BaseNotification
    {
        $record = new Notification([
            'class' => $class,
            'user_id' => $userId,
            'originator_id' => $originatorId,
            'content_id' => $content->id,
            'contentcontainer_id' => $content->contentcontainer_id,
            'created_at' => $createdAt ?? $this->now,
        ]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);
        $n->getGroupingService()->afterInsert();

        return $n;
    }

    private function content(int $postId): Content
    {
        return Post::findOne(['id' => $postId])->content;
    }

    /**
     * @param array<int, int> $expected `grouping_key` by notification id
     */
    private function assertKeys(array $expected): void
    {
        $actual = Notification::find()->select(['grouping_key', 'id'])->andWhere(['id' => array_keys($expected)])->indexBy('id')->column();
        ksort($expected);
        ksort($actual);
        $this->assertSame($expected, array_map('intval', $actual));
    }
}
