<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\serializers\NotificationSerializer;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestGroupedNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestHighPriorityNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Pins the shape the notification islands consume (see `NotificationSerializer`'s own
 * docblock): the sentence comes from the server, everything around it is data the client
 * renders itself.
 *
 * The queue of the test application is synchronous, so a dispatch writes its rows inline.
 */
class NotificationSerializerTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testSerializesTheEntryDataAroundTheServerRenderedSentence()
    {
        // post 1: on the admin's profile, not in a space
        TestNotification::send([1], Post::findOne(['id' => 1]), User::findOne(['id' => 2]));
        $record = $this->latest(TestNotification::class);
        $notification = NotificationManager::load($record);

        $result = NotificationSerializer::notification($notification);

        $this->assertSame(
            ['id', 'html', 'url', 'isNew', 'createdAt', 'groupKey', 'count', 'priority', 'originator', 'space'],
            array_keys($result),
        );
        $this->assertSame((int)$record->id, $result['id']);
        $this->assertSame($notification->asWeb(), $result['html']);
        $this->assertStringContainsString('<strong>', $result['html']);
        // The `notification/entry` redirect, absolute like every other URL of the API. Asserted
        // on the decoded route because the unit environment runs without pretty URLs.
        $this->assertStringStartsWith('http', $result['url']);
        $this->assertStringContainsString('notification/entry', urldecode($result['url']));
        $this->assertStringContainsString('id=' . $record->id, urldecode($result['url']));
        $this->assertTrue($result['isNew']);
        // ISO-8601 in UTC, per the v2 conventions.
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $result['createdAt']);
        // The same opaque value the live event carries as `notificationGroup`.
        $this->assertSame(NotificationListService::encodeCursor((int)$record->grouping_key), $result['groupKey']);
        $this->assertSame(1, $result['count']);
        // TestNotification is in the social group, which is low priority.
        $this->assertSame('low', $result['priority']);
        $this->assertSame(2, $result['originator']['id']);
        $this->assertArrayHasKey('imageUrl', $result['originator']);
        $this->assertNull($result['space']);
    }

    public function testSerializesTheSpaceOfASpaceBoundNotification()
    {
        // post 10: in Space 2
        TestContentNotification::send([1], Post::findOne(['id' => 10]));
        $result = NotificationSerializer::notification(NotificationManager::load($this->latest(TestContentNotification::class)));

        $this->assertSame('Space 2', $result['space']['name']);
        $this->assertSame(
            ['id', 'guid', 'name', 'url', 'color', 'imageUrl', 'contentContainerId'],
            array_keys($result['space']),
        );
        $this->assertNull($result['originator']);
        $this->assertSame('normal', $result['priority']);
    }

    public function testHighPriority()
    {
        TestHighPriorityNotification::send([1]);

        $result = NotificationSerializer::notification(NotificationManager::load($this->latest(TestHighPriorityNotification::class)));

        $this->assertSame('high', $result['priority']);
    }

    public function testGroupedPairIsOneEntryOfTheNewestMember()
    {
        // post 2: public post on the admin's profile
        $post = Post::findOne(['id' => 2]);
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 2]));
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 3]));

        $newest = $this->latest(TestGroupedNotification::class);
        $result = $this->serializeGroup((int)$newest->grouping_key);

        $this->assertSame((int)$newest->id, $result['id']);
        $this->assertSame(2, $result['count']);
        $this->assertSame(NotificationListService::encodeCursor((int)$newest->grouping_key), $result['groupKey']);
        $this->assertSame(3, $result['originator']['id']);
        $this->assertTrue($result['isNew']);

        // A group stays new while any member is unseen ...
        Notification::updateAll(['seen_at' => date('Y-m-d H:i:s')], ['id' => $newest->id]);
        $this->assertTrue($this->serializeGroup((int)$newest->grouping_key)['isNew']);

        // ... and is seen once all of them are.
        Notification::updateAll(['seen_at' => date('Y-m-d H:i:s')], ['grouping_key' => $newest->grouping_key]);
        $this->assertFalse($this->serializeGroup((int)$newest->grouping_key)['isNew']);
    }

    public function testSeenNotificationIsNotNew()
    {
        TestNotification::send([1]);
        $record = $this->latest(TestNotification::class);
        $record->updateAttributes(['seen_at' => date('Y-m-d H:i:s')]);

        $this->assertFalse(NotificationSerializer::notification(NotificationManager::load($record))['isNew']);
    }

    private function serializeGroup(int $groupingKey): array
    {
        $row = Notification::find()
            ->forUser(1)
            ->grouped()
            ->andWhere(['notification.grouping_key' => $groupingKey])
            ->one();
        $this->assertNotNull($row);

        return NotificationSerializer::notification(NotificationManager::load($row));
    }

    private function latest(string $class): Notification
    {
        $record = Notification::find()
            ->forUser(1)
            ->andWhere(['notification.class' => $class])
            ->orderBy(['notification.id' => SORT_DESC])
            ->one();
        $this->assertNotNull($record, 'a ' . $class . ' was dispatched');

        return $record;
    }
}
