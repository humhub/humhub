<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\models\RecordMap;
use humhub\modules\content\models\Content;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestDirectNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class NotificationListServiceTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    private ?NotificationManager $originalManager = null;

    protected function _before()
    {
        parent::_before();
        // id 2
        self::becomeUser('User1');
    }

    protected function _after()
    {
        if ($this->originalManager !== null) {
            Yii::$app->set('notification', $this->originalManager);
            $this->originalManager = null;
        }
        parent::_after();
    }

    public function testPagesWithAnOpaqueCursor()
    {
        $ids = $this->insert(array_fill(0, 5, TestNotification::class));
        $service = new NotificationListService();

        $page1 = $service->page(2);
        $this->assertSame([$ids[4], $ids[3]], $this->ids($page1));
        $this->assertSame(NotificationListService::encodeCursor($ids[3]), $page1['nextCursor']);
        $this->assertSame(5, $page1['unseenCount']);

        $page2 = $service->page(2, $page1['nextCursor']);
        $this->assertSame([$ids[2], $ids[1]], $this->ids($page2));

        $page3 = $service->page(2, $page2['nextCursor']);
        $this->assertSame([$ids[0]], $this->ids($page3));
        $this->assertNull($page3['nextCursor']);

        // an unreadable cursor is no cursor
        $this->assertSame([$ids[4], $ids[3]], $this->ids($service->page(2, 'garbage')));
    }

    public function testOnlyTheCallersListedNotifications()
    {
        $mine = $this->insert([TestNotification::class, TestNotification::class]);
        $foreign = $this->insert([TestNotification::class], 3);
        Notification::updateAll(['listed' => 0], ['id' => $mine[0]]);

        $page = (new NotificationListService())->page(50);

        $this->assertSame([$mine[1]], $this->ids($page));
        $this->assertNotContains($foreign[0], $this->ids($page));
        $this->assertSame(1, $page['unseenCount']);
    }

    public function testSeenFilter()
    {
        $ids = $this->insert(array_fill(0, 4, TestNotification::class));
        Notification::updateAll(['seen_at' => date('Y-m-d H:i:s')], ['id' => [$ids[0], $ids[1]]]);
        $service = new NotificationListService();

        $this->assertSame([$ids[1], $ids[0]], $this->ids($service->page(50, null, null, 'seen')));
        $this->assertSame([$ids[3], $ids[2]], $this->ids($service->page(50, null, null, 'unseen')));
        $this->assertSame([$ids[3], $ids[2], $ids[1], $ids[0]], $this->ids($service->page(50)));
    }

    public function testCategoryFilter()
    {
        $this->setRegisteredNotifications([TestNotification::class, TestDirectNotification::class]);
        [$social, $direct] = $this->insert([TestNotification::class, TestDirectNotification::class]);
        $service = new NotificationListService();

        $this->assertSame([$social], $this->ids($service->page(50, null, ['social'])));
        $this->assertSame([$direct], $this->ids($service->page(50, null, ['direct'])));
        $this->assertSame([$direct, $social], $this->ids($service->page(50, null, ['social', 'direct'])));
        // ids matching no category, or none at all, are a filter of their own: an empty list
        $this->assertSame([], $this->ids($service->page(50, null, ['no-such-category'])));
        $this->assertSame([], $this->ids($service->page(50, null, [])));
        // the badge is independent of the filter
        $this->assertSame(2, $service->page(50, null, [])['unseenCount']);
    }

    public function testNotificationsAboutUnpublishedContentAreLeftOut()
    {
        // post 2: public post on the admin's profile
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send([2], $post);
        $about = (int)Notification::find()->forUser(2)->andWhere(['class' => TestContentNotification::class])->select('id')->scalar();
        [$other] = $this->insert([TestNotification::class]);
        $service = new NotificationListService();

        $this->assertSame([$other, $about], $this->ids($service->page(50)));

        Content::updateAll(['state' => Content::STATE_DRAFT], ['id' => $post->content->id]);

        $page = $service->page(50);
        $this->assertSame([$other], $this->ids($page));
        $this->assertSame(1, $page['unseenCount']);
    }

    public function testUnresolvableEntryIsDeletedAndSkipped()
    {
        [$good, $broken] = $this->insert([TestNotification::class, 'humhub\\modules\\gone\\notifications\\GoneNotification']);

        $page = (new NotificationListService())->page(2);

        $this->assertSame([$good], $this->ids($page));
        $this->assertNull(Notification::findOne(['id' => $broken]));
        $this->assertNotNull(Notification::findOne(['id' => $good]));
        // the page was full as asked for: a dropped entry does not end the paging
        $this->assertSame(NotificationListService::encodeCursor($good), $page['nextCursor']);
    }

    public function testEntryWhoseHeadsSourceIsGoneLosesOnlyTheHead()
    {
        $ids = $this->insert(array_fill(0, 3, TestNotification::class));
        Notification::updateAll(['grouping_key' => $ids[2]], ['id' => $ids]);
        // a record map entry whose record does not exist
        $map = new RecordMap(['model' => User::class, 'pk' => 999999]);
        $this->assertTrue($map->save());
        Notification::updateAll(['source_record_id' => $map->id], ['id' => $ids[2]]);
        $service = new NotificationListService();

        $this->assertSame([], $this->ids($service->page(50)));
        $this->assertNull(Notification::findOne(['id' => $ids[2]]));
        $this->assertNotNull(Notification::findOne(['id' => $ids[0]]));
        $this->assertNotNull(Notification::findOne(['id' => $ids[1]]));

        // the rest of the group renders again, represented by its new newest member
        $page = $service->page(50);
        $this->assertSame([$ids[1]], $this->ids($page));
        $this->assertSame(2, $page['results'][0]['count']);
    }

    public function testUnseenCountCountsAGroupOnce()
    {
        $ids = $this->insert(array_fill(0, 5, TestNotification::class));
        // one group of three, two single entries
        Notification::updateAll(['grouping_key' => $ids[2]], ['id' => [$ids[0], $ids[1], $ids[2]]]);
        $user = User::findOne(['id' => 2]);

        $this->assertSame(3, NotificationListService::unseenCount($user));

        // a group with one unseen member is unseen
        Notification::updateAll(['seen_at' => date('Y-m-d H:i:s')], ['id' => [$ids[1], $ids[2]]]);
        $this->assertSame(3, NotificationListService::unseenCount($user));

        Notification::updateAll(['seen_at' => date('Y-m-d H:i:s')], ['id' => [$ids[0], $ids[3]]]);
        $this->assertSame(1, NotificationListService::unseenCount($user));

        $page = (new NotificationListService())->page(50);
        $this->assertSame([$ids[4], $ids[3], $ids[2]], $this->ids($page));
        // the grouped entry is represented by its newest member and counts its members
        $this->assertSame(3, $page['results'][2]['count']);
    }

    /**
     * Inserts one row per class for the user, oldest first, each its own group.
     *
     * @return int[] the ids in insertion order
     */
    private function insert(array $classes, int $userId = 2): array
    {
        $maxId = (int)Notification::find()->max('id');
        $rows = [];
        foreach ($classes as $i => $class) {
            $rows[] = [$class, $userId, 1, 1, date('Y-m-d H:i:s', strtotime('2026-01-01 10:00:00') + 60 * $i)];
        }
        Yii::$app->db->createCommand()
            ->batchInsert('notification', ['class', 'user_id', 'priority', 'listed', 'created_at'], $rows)
            ->execute();
        Yii::$app->db->createCommand('UPDATE notification SET grouping_key = id WHERE grouping_key IS NULL')->execute();

        return array_map('intval', Notification::find()
            ->select('id')
            ->where(['>', 'id', $maxId])
            ->andWhere(['user_id' => $userId])
            ->orderBy('id')
            ->column());
    }

    /**
     * @return int[]
     */
    private function ids(array $page): array
    {
        return array_map(fn(array $entry): int => $entry['id'], $page['results']);
    }

    /**
     * Installs a notification manager whose modules register exactly the given classes.
     */
    private function setRegisteredNotifications(array $classes): void
    {
        $this->originalManager ??= Yii::$app->notification;

        $manager = new class (['targets' => $this->originalManager->targets]) extends NotificationManager {
            public array $classes = [];

            protected function findNotificationClasses(): array
            {
                return $this->classes;
            }
        };
        $manager->classes = $classes;

        Yii::$app->set('notification', $manager);
    }
}
