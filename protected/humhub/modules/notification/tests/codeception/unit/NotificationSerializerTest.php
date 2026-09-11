<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\serializers\NotificationSerializer;
use humhub\modules\notification\tests\codeception\unit\rendering\notifications\PlainTestNotification;
use humhub\modules\notification\tests\codeception\unit\rendering\notifications\TestNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Pins the shape the notification islands consume (see `NotificationSerializer`'s own
 * docblock): the sentence comes from the server, everything around it is data the client
 * renders itself.
 */
class NotificationSerializerTest extends HumHubDbTestCase
{
    public function testSerializesTheEntryDataAroundTheServerRenderedSentence()
    {
        $user = User::findOne(['id' => 1]);
        $notification = TestNotification::instance()
            ->from(User::findOne(['id' => 2]))
            ->about(Post::findOne(['id' => 1]));
        $this->assertTrue($notification->saveRecord($user));

        $result = NotificationSerializer::notification($notification);

        $this->assertSame(
            ['id', 'html', 'url', 'isNew', 'createdAt', 'groupKey', 'originator', 'space'],
            array_keys($result),
        );
        $this->assertGreaterThan(0, $result['id']);
        $this->assertSame((int)$notification->record->id, $result['id']);
        $this->assertSame('<h1>TestedMailViewNotificationHTML</h1>', $result['html']);
        // The `notification/entry` redirect, relative - the same target the legacy entry
        // markup linked to. Asserted on the decoded route because the unit environment runs
        // without pretty URLs (`?r=notification%2Fentry`).
        $this->assertStringContainsString('notification/entry', urldecode($result['url']));
        $this->assertStringContainsString('id=' . $notification->record->id, urldecode($result['url']));
        $this->assertStringNotContainsString('http', $result['url']);
        $this->assertTrue($result['isNew']);
        // ISO-8601 in UTC, per the v2 conventions.
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $result['createdAt']);
        $this->assertSame(2, $result['originator']['id']);
        $this->assertArrayHasKey('imageUrl', $result['originator']);
    }

    public function testFallsBackToTheTextRepresentationWithoutAnHtmlImplementation()
    {
        $notification = PlainTestNotification::instance()->about(Post::findOne(['id' => 1]));
        $notification->saveRecord(User::findOne(['id' => 1]));

        $this->assertNull($notification->html());

        $result = NotificationSerializer::notification($notification);

        $this->assertSame($notification->text(), $result['html']);
    }

    public function testSerializesTheSpaceOfASpaceBoundNotification()
    {
        $notification = TestNotification::instance()->about(Post::findOne(['id' => 10]));
        $notification->saveRecord(User::findOne(['id' => 1]));

        $result = NotificationSerializer::notification($notification);

        $this->assertSame('Space 2', $result['space']['name']);
        $this->assertSame(
            ['id', 'guid', 'name', 'url', 'color', 'imageUrl', 'contentContainerId'],
            array_keys($result['space']),
        );
        // No own profile image in the fixtures: the client renders the coloured acronym tile,
        // which is exactly what a `null` here selects (see SpaceSerializer's docblock).
        $this->assertNull($result['space']['imageUrl']);
    }

    public function testHasNoSpaceForAProfileNotification()
    {
        // Post 1 lives on a user profile, not in a space.
        $notification = TestNotification::instance()->about(Post::findOne(['id' => 1]));
        $notification->saveRecord(User::findOne(['id' => 1]));

        $this->assertNull(NotificationSerializer::notification($notification)['space']);
    }

    public function testGroupKeyIsTheCompositeKeyTheLiveEventCarries()
    {
        $notification = TestNotification::instance()->about(Post::findOne(['id' => 1]));
        $notification->saveRecord(User::findOne(['id' => 1]));

        // Without a group key there is nothing to dedupe against.
        $this->assertNull(NotificationSerializer::notification($notification)['groupKey']);

        $grouped = new class extends TestNotification {
            public function getGroupKey()
            {
                return 'group-42';
            }
        };
        $grouped->about(Post::findOne(['id' => 2]));
        $grouped->saveRecord(User::findOne(['id' => 1]));

        $this->assertSame(
            $grouped::class . ':group-42',
            NotificationSerializer::notification($grouped)['groupKey'],
        );
    }

    public function testSeenNotificationIsNotNew()
    {
        $notification = TestNotification::instance()->about(Post::findOne(['id' => 1]));
        $notification->saveRecord(User::findOne(['id' => 1]));
        $notification->record->updateAttributes(['seen' => 1]);

        $this->assertFalse(NotificationSerializer::notification($notification)['isNew']);
    }
}
