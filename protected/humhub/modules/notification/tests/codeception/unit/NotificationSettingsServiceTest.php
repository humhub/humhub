<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\services\NotificationSpaceService;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidArgumentException;

class NotificationSettingsServiceTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testGlobalDefaultWithoutSettingIsTheFirstMode()
    {
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService())->getMode(new MailTarget()));
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService($this->user(1)))->getMode(new MobileTarget()));

        $target = new MailTarget(['modes' => [NotificationSettingsService::MODE_SUMMARY, NotificationSettingsService::MODE_OFF]]);
        $this->assertSame(NotificationSettingsService::MODE_SUMMARY, (new NotificationSettingsService())->getMode($target));
    }

    public function testUserInheritsTheGlobalDefault()
    {
        $target = $this->summaryMailTarget();
        (new NotificationSettingsService())->setMode($target, NotificationSettingsService::MODE_SUMMARY);

        $this->assertSame(NotificationSettingsService::MODE_SUMMARY, (new NotificationSettingsService())->getMode($target));
        $this->assertSame(NotificationSettingsService::MODE_SUMMARY, (new NotificationSettingsService($this->user(1)))->getMode($target));
        $this->assertSame(NotificationSettingsService::MODE_SUMMARY, $target->getMode($this->user(2)));
    }

    public function testMailOffersNoSummaryModeYet()
    {
        $this->assertSame([NotificationSettingsService::MODE_ADAPTIVE, NotificationSettingsService::MODE_OFF], (new MailTarget())->modes);

        $this->expectException(InvalidArgumentException::class);
        (new NotificationSettingsService())->setMode(new MailTarget(), NotificationSettingsService::MODE_SUMMARY);
    }

    public function testStoredSummaryModeFallsBack()
    {
        $target = new MailTarget();
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set('email.mode', NotificationSettingsService::MODE_SUMMARY);
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService($this->user(1)))->getMode($target), 'the first mode without a global default');

        Yii::$app->getModule('notification')->settings->set('email.mode', NotificationSettingsService::MODE_SUMMARY);
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService())->getMode($target));

        (new NotificationSettingsService())->setMode($target, NotificationSettingsService::MODE_OFF);
        $this->assertSame(NotificationSettingsService::MODE_OFF, (new NotificationSettingsService($this->user(1)))->getMode($target), 'the global default');
    }

    public function testSetModeOffForTheUser()
    {
        $target = new MailTarget();
        (new NotificationSettingsService($this->user(1)))->setMode($target, NotificationSettingsService::MODE_OFF);

        $this->assertSame(NotificationSettingsService::MODE_OFF, (new NotificationSettingsService($this->user(1)))->getMode($target));
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService($this->user(2)))->getMode($target));
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService())->getMode($target));
        $this->assertSame('off', Yii::$app->getModule('notification')->settings->user($this->user(1))->get('email.mode'));
    }

    public function testSetModeOffGlobally()
    {
        $target = new MobileTarget();
        (new NotificationSettingsService())->setMode($target, NotificationSettingsService::MODE_OFF);

        $this->assertSame('off', Yii::$app->getModule('notification')->settings->get('mobile.mode'));
        $this->assertSame(NotificationSettingsService::MODE_OFF, (new NotificationSettingsService($this->user(1)))->getMode($target));

        // the user's own setting wins over the global default
        (new NotificationSettingsService($this->user(1)))->setMode($target, NotificationSettingsService::MODE_ADAPTIVE);
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService($this->user(1)))->getMode($target));
    }

    public function testInvalidStoredModeFallsBackToTheGlobalDefault()
    {
        $target = new MobileTarget();
        (new NotificationSettingsService())->setMode($target, NotificationSettingsService::MODE_OFF);
        // e.g. a mode the target no longer supports
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set('mobile.mode', NotificationSettingsService::MODE_SUMMARY);

        $this->assertSame(NotificationSettingsService::MODE_OFF, (new NotificationSettingsService($this->user(1)))->getMode($target));

        Yii::$app->getModule('notification')->settings->set('mobile.mode', 'unknown');
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, (new NotificationSettingsService($this->user(1)))->getMode($target));
    }

    public function testUnknownModeThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        // the mobile channel has no summary mode
        (new NotificationSettingsService($this->user(1)))->setMode(new MobileTarget(), NotificationSettingsService::MODE_SUMMARY);
    }

    public function testDirectGroupIsAlwaysEnabled()
    {
        $target = new MailTarget();
        Yii::$app->getModule('notification')->settings->set('email.group.direct', 0);
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set('email.group.direct', 0);

        $this->assertTrue((new NotificationSettingsService())->isGroupEnabled($target, NotificationGroup::direct()));
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isGroupEnabled($target, NotificationGroup::direct()));
    }

    public function testGroupSwitchedOffGlobally()
    {
        $target = new MailTarget();
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isGroupEnabled($target, NotificationGroup::social()));

        (new NotificationSettingsService())->setGroup($target, NotificationGroup::social(), false);
        (new NotificationSettingsService($this->user(2)))->setGroup($target, NotificationGroup::social(), true);

        $this->assertFalse((new NotificationSettingsService())->isGroupEnabled($target, NotificationGroup::social()));
        $this->assertFalse((new NotificationSettingsService($this->user(1)))->isGroupEnabled($target, NotificationGroup::social()));
        $this->assertTrue((new NotificationSettingsService($this->user(2)))->isGroupEnabled($target, NotificationGroup::social()));
        // other channels and groups are not affected
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isGroupEnabled(new MobileTarget(), NotificationGroup::social()));
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isGroupEnabled($target, NotificationGroup::content()));
    }

    public function testResetRemovesTheUsersKeysOnly()
    {
        $mail = new MailTarget();
        $mobile = new MobileTarget();
        $user1 = new NotificationSettingsService($this->user(1));
        $user1->setMode($mail, NotificationSettingsService::MODE_OFF);
        $user1->setGroup($mobile, NotificationGroup::content(), false);
        (new NotificationSettingsService($this->user(2)))->setMode($mail, NotificationSettingsService::MODE_OFF);
        (new NotificationSettingsService())->setGroup($mail, NotificationGroup::admin(), false);
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);

        $user1->reset();

        $this->assertSame([NotificationSpaceService::IS_TOUCHED_SETTINGS], $this->settingNames(1));
        $this->assertSame(NotificationSettingsService::MODE_ADAPTIVE, $user1->getMode($mail));
        $this->assertTrue($user1->isGroupEnabled($mobile, NotificationGroup::content()));
        // inherited global default still applies
        $this->assertFalse($user1->isGroupEnabled($mail, NotificationGroup::admin()));
        $this->assertSame(NotificationSettingsService::MODE_OFF, (new NotificationSettingsService($this->user(2)))->getMode($mail));
    }

    public function testResetAllUsers()
    {
        $mail = $this->summaryMailTarget();
        foreach ([1, 2] as $id) {
            $service = new NotificationSettingsService($this->user($id));
            $service->setMode($mail, NotificationSettingsService::MODE_OFF);
            $service->setGroup($mail, NotificationGroup::social(), false);
            Yii::$app->getModule('notification')->settings->user($this->user($id))->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);
        }
        (new NotificationSettingsService())->setMode($mail, NotificationSettingsService::MODE_SUMMARY);
        Yii::$app->getModule('activity')->settings->user($this->user(1))->set('x', 1);
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set('unrelated', 'kept');

        NotificationSettingsService::resetAllUsers();

        $this->assertSame(1, Yii::$app->getModule('activity')->settings->user($this->user(1))->get('x'));
        $this->assertSame('kept', Yii::$app->getModule('notification')->settings->user($this->user(1))->get('unrelated'));
        Yii::$app->getModule('notification')->settings->user($this->user(1))->delete('unrelated');

        foreach ([1, 2] as $id) {
            $this->assertSame([], $this->settingNames($id));
            $service = new NotificationSettingsService($this->user($id));
            // the global default is kept
            $this->assertSame(NotificationSettingsService::MODE_SUMMARY, $service->getMode($mail));
            $this->assertTrue($service->isGroupEnabled($mail, NotificationGroup::social()));
            $this->assertFalse(NotificationSpaceService::isTouchedSettings($this->user($id)));
        }
    }

    public function testToArrayForAUser()
    {
        $data = (new NotificationSettingsService($this->user(2)))->toArray();

        $this->assertSame(['channels', 'spaces', 'summary'], array_keys($data));
        $this->assertSame('web', $data['channels'][0]['id']);
        $this->assertTrue($data['channels'][0]['fixed']);
        $this->assertSame([], $data['channels'][0]['groups']);

        $email = $this->channel($data, 'email');
        $this->assertSame(['id', 'title', 'fixed', 'mode', 'modes', 'groups'], array_keys($email));
        $this->assertFalse($email['fixed']);
        $this->assertSame('adaptive', $email['mode']);
        $this->assertSame([
            ['value' => 'adaptive', 'label' => 'Send'],
            ['value' => 'off', 'label' => 'Off'],
        ], $email['modes']);
        $groupIds = array_column($email['groups'], 'id');
        $this->assertSame(['direct', 'social', 'content'], array_slice($groupIds, 0, 3));
        $this->assertNotContains('admin', $groupIds, 'the admin group is not shown to a user without an administrative permission');
        $this->assertSame(['id', 'title', 'description', 'enabled', 'fixed'], array_keys($email['groups'][0]));
        $this->assertTrue($email['groups'][0]['enabled']);
        $this->assertTrue($email['groups'][0]['fixed']);
        $this->assertFalse($email['groups'][1]['fixed']);

        $this->assertTrue($data['spaces']['enabled']);
        $this->assertIsArray($data['spaces']['selected']);
        $this->assertSame(2, $data['summary']['interval'], 'the daily summary by default');
        $this->assertContains(['value' => 0, 'label' => 'Never'], $data['summary']['intervals']);
    }

    public function testToArrayGlobalShowsEveryGroupAndTheDefaultSpaces()
    {
        $space = Space::findOne(['id' => 1]);
        (new NotificationSpaceService())->setSpaces([$space->guid]);
        Yii::$app->getModule('activity')->settings->set('mailSummaryInterval', 3);

        $data = (new NotificationSettingsService())->toArray();

        $this->assertContains('admin', array_column($this->channel($data, 'email')['groups'], 'id'));
        $this->assertSame([$space->id], array_column($data['spaces']['selected'], 'id'));
        $this->assertSame(['id', 'guid', 'name', 'url', 'color', 'imageUrl', 'contentContainerId'], array_keys($data['spaces']['selected'][0]));
        $this->assertSame(3, $data['summary']['interval']);
    }

    public function testToArrayWithoutMailSummaries()
    {
        $module = Yii::$app->getModule('activity');
        $module->enableMailSummaries = false;
        try {
            $this->assertArrayNotHasKey('summary', (new NotificationSettingsService($this->user(2)))->toArray());
        } finally {
            $module->enableMailSummaries = true;
        }
    }

    public function testFromArrayWritesModesGroupsSpacesAndInterval()
    {
        $user = $this->user(2);
        $space = Space::findOne(['id' => 3]);
        $service = new NotificationSettingsService($user);

        $errors = $service->fromArray([
            'channels' => [
                ['id' => 'email', 'mode' => 'off', 'groups' => [['id' => 'social', 'enabled' => false], ['id' => 'direct', 'enabled' => true]]],
            ],
            'spaces' => [$space->id],
            'summary' => ['interval' => 1],
        ]);

        $this->assertSame([], $errors);
        $data = $service->toArray();
        $email = $this->channel($data, 'email');
        $this->assertSame('off', $email['mode']);
        $this->assertFalse($this->group($email, 'social')['enabled']);
        $this->assertTrue($this->group($email, 'content')['enabled']);
        $this->assertSame([$space->id], array_column($data['spaces']['selected'], 'id'));
        $this->assertSame(1, $data['summary']['interval']);
        $this->assertTrue(NotificationSpaceService::isTouchedSettings($user));
        // the global defaults are untouched
        $this->assertSame('adaptive', (new NotificationSettingsService())->getMode(new MailTarget()));
    }

    public function testPartialUpdateKeepsTheDefaultSpaces()
    {
        $user = $this->user(2);
        $space = Space::findOne(['id' => 3]);
        (new NotificationSpaceService())->setSpaces([$space->guid]);
        $this->assertContains($space->id, array_map(fn(Space $s) => $s->id, (new NotificationSpaceService())->getSpaces($user)), 'precondition: a default space');

        $this->assertSame([], (new NotificationSettingsService($user))->fromArray(['channels' => [['id' => 'email', 'mode' => 'off']]]));

        $this->assertFalse(NotificationSpaceService::isTouchedSettings($user));
        $this->assertContains($space->id, array_map(fn(Space $s) => $s->id, (new NotificationSpaceService())->getSpaces($user)));
    }

    public function testFromArrayKeepsTheInheritedIntervalWhenUnchanged()
    {
        $user = $this->user(2);

        $this->assertSame([], (new NotificationSettingsService($user))->fromArray(['summary' => ['interval' => 2]]));

        $this->assertNull(Yii::$app->getModule('activity')->settings->user($user)->get('mailSummaryInterval'));
    }

    public function testFromArrayGlobal()
    {
        $space = Space::findOne(['id' => 2]);
        $service = new NotificationSettingsService();

        $errors = $service->fromArray([
            'channels' => [['id' => 'email', 'mode' => 'off', 'groups' => [['id' => 'admin', 'enabled' => false]]]],
            'spaces' => [(string)$space->id],
            'summary' => ['interval' => '3'],
        ]);

        $this->assertSame([], $errors);
        $this->assertSame('off', Yii::$app->getModule('notification')->settings->get('email.mode'));
        $this->assertEquals('0', Yii::$app->getModule('notification')->settings->get('email.group.admin'));
        $this->assertSame([$space->guid], Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces'));
        $this->assertSame('3', (string)Yii::$app->getModule('activity')->settings->get('mailSummaryInterval'));
    }

    public function testFromArrayRejectsInvalidValuesAndWritesNothing()
    {
        $service = new NotificationSettingsService($this->user(2));

        $errors = $service->fromArray([
            'channels' => [
                // no summary mode until the delivery layer ships
                ['id' => 'email', 'mode' => 'summary', 'groups' => [
                    ['id' => 'social', 'enabled' => false],
                    ['id' => 'direct', 'enabled' => false],
                    ['id' => 'unknown', 'enabled' => false],
                ]],
                ['id' => 'web', 'mode' => 'off'],
                // not active without a push provider
                ['id' => 'mobile', 'mode' => 'off'],
            ],
            'summary' => ['interval' => 42],
            'spaces' => ['x'],
        ]);

        $this->assertSame(['email.mode', 'email.group.direct', 'email.group.unknown', 'web.mode', 'channels', 'spaces', 'summary.interval'], array_keys($errors));
        $this->assertTrue($service->isGroupEnabled(new MailTarget(), NotificationGroup::social()), 'nothing is written');
        $this->assertFalse(NotificationSpaceService::isTouchedSettings($this->user(2)));
    }

    public function testFromArrayIgnoresSpacesTheUserCannotSee()
    {
        $user = $this->user(2);
        $hidden = Space::findOne(['id' => 1]);
        $hidden->updateAttributes(['visibility' => Space::VISIBILITY_NONE]);
        $visible = Space::findOne(['id' => 3]);
        $this->assertFalse(Space::find()->visible($user)->andWhere(['space.id' => $hidden->id])->exists(), 'precondition: the user cannot see the space');

        $service = new NotificationSettingsService($user);
        $this->assertSame([], $service->fromArray(['spaces' => [$hidden->id, $visible->id]]));

        $this->assertNotContains($hidden->id, array_column($service->toArray()['spaces']['selected'], 'id'));
    }

    private function channel(array $data, string $id): array
    {
        foreach ($data['channels'] as $channel) {
            if ($channel['id'] === $id) {
                return $channel;
            }
        }
        $this->fail('No channel ' . $id);
    }

    private function group(array $channel, string $id): array
    {
        foreach ($channel['groups'] as $group) {
            if ($group['id'] === $id) {
                return $group;
            }
        }
        $this->fail('No group ' . $id);
    }

    private function user(int $id): User
    {
        return User::findOne(['id' => $id]);
    }

    /**
     * @return string[] the notification module's settings stored for the user
     */
    private function settingNames(int $userId): array
    {
        return ContentContainerSetting::find()
            ->select('name')
            ->where(['module_id' => 'notification', 'contentcontainer_id' => $this->user($userId)->contentcontainer_id])
            ->orderBy('name')
            ->column();
    }

    /**
     * A mail target offering the summary mode, which the core mail target does not offer yet.
     */
    private function summaryMailTarget(): MailTarget
    {
        return new MailTarget(['modes' => [NotificationSettingsService::MODE_ADAPTIVE, NotificationSettingsService::MODE_SUMMARY, NotificationSettingsService::MODE_OFF]]);
    }
}
