<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\services\NotificationSpaceService;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\GroupPermission;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The notification settings API (`humhub\modules\notification\controllers\api\SettingsController`),
 * consumed by the `NotificationSettings` island.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class NotificationSettingsApiCest
{
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    private function sendJsonPatch(ApiTester $I, string $url, array $data): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch($url, json_encode($data));
    }

    private function channel(ApiTester $I, string $id): array
    {
        return $I->grabDataFromResponseByJsonPath('$.channels[?(@.id == "' . $id . '")]')[0];
    }

    private function group(array $channel, string $id): array
    {
        foreach ($channel['groups'] as $group) {
            if ($group['id'] === $id) {
                return $group;
            }
        }
        Assert::fail('No group ' . $id);
    }

    public function testReadsTheCallersSettings(ApiTester $I)
    {
        $I->wantTo('read my notification settings');
        $I->amLoggedInAs(2);

        $I->sendGet('notification/settings');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        Assert::assertSame(['channels', 'spaces', 'summary'], array_keys(json_decode($I->grabResponse(), true)));
        Assert::assertSame('web', $I->grabDataFromResponseByJsonPath('$.channels[0].id')[0]);
        Assert::assertTrue($I->grabDataFromResponseByJsonPath('$.channels[0].fixed')[0]);

        $email = $this->channel($I, 'email');
        Assert::assertSame(['id', 'title', 'fixed', 'mode', 'modes', 'groups'], array_keys($email));
        Assert::assertSame('adaptive', $email['mode']);
        Assert::assertSame(['adaptive', 'off'], array_column($email['modes'], 'value'));
        Assert::assertSame(['id', 'title', 'description', 'enabled', 'fixed'], array_keys($email['groups'][0]));
        Assert::assertTrue($this->group($email, 'direct')['fixed']);
        Assert::assertNotContains('admin', array_column($email['groups'], 'id'), 'a user without administrative permissions has no admin group');

        Assert::assertSame(['selected', 'enabled'], array_keys($I->grabDataFromResponseByJsonPath('$.spaces')[0]));
        Assert::assertSame(['interval', 'intervals'], array_keys($I->grabDataFromResponseByJsonPath('$.summary')[0]));
    }

    public function testUpdatesTheCallersSettings(ApiTester $I)
    {
        $I->wantTo('change my notification settings');
        $space = Space::findOne(['id' => 3]);
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $this->sendJsonPatch($I, 'notification/settings', [
            'channels' => [['id' => 'email', 'mode' => 'off', 'groups' => [['id' => 'social', 'enabled' => false]]]],
            'spaces' => [$space->id],
            'summary' => ['interval' => 1],
        ]);

        $I->seeResponseCodeIs(200);
        $email = $this->channel($I, 'email');
        Assert::assertSame('off', $email['mode']);
        Assert::assertFalse($this->group($email, 'social')['enabled']);
        Assert::assertTrue($this->group($email, 'content')['enabled']);
        Assert::assertSame([$space->id], $I->grabDataFromResponseByJsonPath('$.spaces.selected[*].id'));
        Assert::assertSame(1, $I->grabDataFromResponseByJsonPath('$.summary.interval')[0]);

        $I->sendGet('notification/settings');
        Assert::assertSame('off', $this->channel($I, 'email')['mode'], 'the change is stored');

        // PATCH is the update verb
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('notification/settings', json_encode(['channels' => [['id' => 'email', 'mode' => 'adaptive']]]));
        // no URL rule for PUT: not found, like any other verb a path does not take
        $I->seeResponseCodeIs(404);
        Assert::assertSame('off', (new NotificationSettingsService(User::findOne(['id' => 2])))->getMode(Yii::$app->notification->getTarget('email')));
        Assert::assertTrue(NotificationSpaceService::isTouchedSettings(User::findOne(['id' => 2])));
    }

    public function testRejectsInvalidValues(ApiTester $I)
    {
        $I->wantTo('be refused an unknown mode, switching off the direct group and an unknown group');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $this->sendJsonPatch($I, 'notification/settings', ['channels' => [['id' => 'email', 'mode' => 'sometimes']]]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('email.mode', $I->grabDataFromResponseByJsonPath('$.errors')[0]);

        $this->sendJsonPatch($I, 'notification/settings', ['channels' => [['id' => 'email', 'groups' => [['id' => 'direct', 'enabled' => false]]]]]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('email.group.direct', $I->grabDataFromResponseByJsonPath('$.errors')[0]);

        $this->sendJsonPatch($I, 'notification/settings', ['channels' => [['id' => 'email', 'groups' => [['id' => 'unknown', 'enabled' => false]]]]]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('email.group.unknown', $I->grabDataFromResponseByJsonPath('$.errors')[0]);

        // the admin group is not offered to this user
        $this->sendJsonPatch($I, 'notification/settings', ['channels' => [['id' => 'email', 'groups' => [['id' => 'admin', 'enabled' => false]]]]]);
        $I->seeResponseCodeIs(422);

        Assert::assertSame('adaptive', (new NotificationSettingsService(User::findOne(['id' => 2])))->getMode(Yii::$app->notification->getTarget('email')));
    }

    public function testGlobalScopeNeedsManageSettings(ApiTester $I)
    {
        $I->wantTo('be refused the global defaults as a normal user');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendGet('notification/settings?scope=global');
        $I->seeResponseCodeIs(403);

        $this->sendJsonPatch($I, 'notification/settings?scope=global', ['channels' => [['id' => 'email', 'mode' => 'off']]]);
        $I->seeResponseCodeIs(403);
        Assert::assertNull(Yii::$app->getModule('notification')->settings->get('email.mode'));
    }

    public function testReadsAndUpdatesTheGlobalDefaultsAsAdmin(ApiTester $I)
    {
        $I->wantTo('change the global defaults as an administrator');
        $space = Space::findOne(['id' => 2]);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendGet('notification/settings?scope=global');
        $I->seeResponseCodeIs(200);
        Assert::assertContains('admin', array_column($this->channel($I, 'email')['groups'], 'id'));

        $this->sendJsonPatch($I, 'notification/settings?scope=global', [
            'channels' => [['id' => 'email', 'mode' => 'off', 'groups' => [['id' => 'admin', 'enabled' => false]]]],
            'spaces' => [$space->id],
        ]);

        $I->seeResponseCodeIs(200);
        Assert::assertSame('off', $this->channel($I, 'email')['mode']);
        Assert::assertSame([$space->id], $I->grabDataFromResponseByJsonPath('$.spaces.selected[*].id'));
        Assert::assertSame('off', Yii::$app->getModule('notification')->settings->get('email.mode'));
        Assert::assertSame([$space->guid], Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces'));
        // the administrator's own settings are untouched
        Assert::assertNull(Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => 1]))->get('email.mode'));
    }

    public function testResetsTheCallersSettings(ApiTester $I)
    {
        $I->wantTo('reset my notification settings');
        $user = User::findOne(['id' => 2]);
        $service = new NotificationSettingsService($user);
        Assert::assertSame([], $service->fromArray(['channels' => [['id' => 'email', 'mode' => 'off']], 'spaces' => []]));
        Assert::assertTrue(NotificationSpaceService::isTouchedSettings($user));

        $I->amLoggedInAs(2);
        $this->withCsrf($I);
        $I->sendPost('notification/settings/reset');

        $I->seeResponseCodeIs(200);
        Assert::assertSame('adaptive', $this->channel($I, 'email')['mode']);
        Yii::$app->getModule('notification')->settings->flushContentContainer();
        Assert::assertFalse(NotificationSpaceService::isTouchedSettings($user));
    }

    public function testResetAllNeedsManageUsers(ApiTester $I)
    {
        $I->wantTo('be refused resetting every user as a normal user');
        Assert::assertSame([], (new NotificationSettingsService(User::findOne(['id' => 3])))->fromArray(['channels' => [['id' => 'email', 'mode' => 'off']]]));

        $I->amLoggedInAs(2);
        $this->withCsrf($I);
        $I->sendPost('notification/settings/reset-all');

        $I->seeResponseCodeIs(403);
        Assert::assertSame('off', (new NotificationSettingsService(User::findOne(['id' => 3])))->getMode(Yii::$app->notification->getTarget('email')));
    }

    public function testResetAllNeedsManageSettingsToo(ApiTester $I)
    {
        $I->wantTo('be refused resetting every user with ManageUsers alone');
        Assert::assertSame([], (new NotificationSettingsService(User::findOne(['id' => 3])))->fromArray(['channels' => [['id' => 'email', 'mode' => 'off']]]));
        $groupId = (int)GroupUser::find()->select('group_id')->where(['user_id' => 2])->scalar();
        $permission = new GroupPermission([
            'permission_id' => (new ManageUsers())->id,
            'group_id' => $groupId,
            'module_id' => (new ManageUsers())->moduleId,
            'class' => ManageUsers::class,
            'state' => 1,
        ]);
        Assert::assertTrue($permission->save());

        try {
            $I->amLoggedInAs(2);
            $this->withCsrf($I);
            $I->sendPost('notification/settings/reset-all');

            $I->seeResponseCodeIs(403);
            Assert::assertSame('off', (new NotificationSettingsService(User::findOne(['id' => 3])))->getMode(Yii::$app->notification->getTarget('email')));
        } finally {
            $permission->delete();
        }
    }

    public function testResetsEveryUserAsAdmin(ApiTester $I)
    {
        $I->wantTo('reset every user\'s notification settings as an administrator');
        Assert::assertSame([], (new NotificationSettingsService(User::findOne(['id' => 3])))->fromArray(['channels' => [['id' => 'email', 'mode' => 'off']]]));

        $I->amLoggedInAs(1);
        $this->withCsrf($I);
        $I->sendPost('notification/settings/reset-all');

        $I->seeResponseCodeIs(200);
        Yii::$app->getModule('notification')->settings->flushContentContainer();
        $user = User::findOne(['id' => 3]);
        Assert::assertSame('adaptive', (new NotificationSettingsService($user))->getMode(Yii::$app->notification->getTarget('email')));
        Assert::assertFalse(NotificationSpaceService::isTouchedSettings($user));
    }

    public function testWritesNeedACsrfToken(ApiTester $I)
    {
        $I->wantTo('be refused a write without a CSRF token');
        $I->amLoggedInAs(2);

        $this->sendJsonPatch($I, 'notification/settings', ['channels' => [['id' => 'email', 'mode' => 'off']]]);
        $I->seeResponseCodeIs(403);

        $I->sendPost('notification/settings/reset');
        $I->seeResponseCodeIs(403);

        Assert::assertNull(Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => 2]))->get('email.mode'));
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');

        $I->sendGet('notification/settings');
        $I->seeResponseCodeIs(401);

        $this->sendJsonPatch($I, 'notification/settings', []);
        $I->seeResponseCodeIs(401);

        $I->sendPost('notification/settings/reset');
        $I->seeResponseCodeIs(401);

        $I->sendPost('notification/settings/reset-all');
        $I->seeResponseCodeIs(401);
    }
}
