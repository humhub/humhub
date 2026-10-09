<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\notification\components\NotificationCategory;
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

    private function category(ApiTester $I, string $id): array
    {
        $categories = $I->grabDataFromResponseByJsonPath('$.categories[?(@.id == "' . $id . '")]');
        Assert::assertNotEmpty($categories, 'No category ' . $id);

        return $categories[0];
    }

    /**
     * Whether the user's comments and likes reach them by e-mail - the switch the tests change.
     */
    private function socialByMail(int $userId): bool
    {
        return (new NotificationSettingsService(User::findOne(['id' => $userId])))
            ->isCategoryEnabled(Yii::$app->notification->getTarget('email'), NotificationCategory::social());
    }

    public function testReadsTheCallersSettings(ApiTester $I)
    {
        $I->wantTo('read my notification settings');
        $I->amLoggedInAs(2);

        $I->sendGet('notification/settings');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        Assert::assertSame(['scope', 'channels', 'categories', 'defaults', 'spaces'], array_keys(json_decode($I->grabResponse(), true)));
        Assert::assertSame('user', $I->grabDataFromResponseByJsonPath('$.scope')[0]);
        Assert::assertSame(['web', 'email'], $I->grabDataFromResponseByJsonPath('$.channels[*].id'));

        $direct = $this->category($I, 'direct');
        Assert::assertSame(['id', 'title', 'description', 'icon', 'module', 'fixed', 'channels'], array_keys($direct));
        Assert::assertTrue($direct['fixed']);
        Assert::assertSame(['web' => true, 'email' => true], $direct['channels']);
        Assert::assertSame(['web' => true, 'email' => false], $this->category($I, 'followers')['channels']);
        Assert::assertNotContains('admin', $I->grabDataFromResponseByJsonPath('$.categories[*].id'), 'a user without administrative permissions has no admin category');
        Assert::assertSame(['web' => true, 'email' => true], $I->grabDataFromResponseByJsonPath('$.defaults.social')[0]);

        Assert::assertSame(['selected', 'enabled'], array_keys($I->grabDataFromResponseByJsonPath('$.spaces')[0]));
    }

    public function testUpdatesTheCallersSettings(ApiTester $I)
    {
        $I->wantTo('change my notification settings');
        $space = Space::findOne(['id' => 3]);
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $this->sendJsonPatch($I, 'notification/settings', [
            'categories' => ['social' => ['email' => false, 'web' => false]],
            'spaces' => [$space->id],
        ]);

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['web' => false, 'email' => false], $this->category($I, 'social')['channels']);
        Assert::assertSame(['web' => true, 'email' => true], $this->category($I, 'content')['channels']);
        Assert::assertSame([$space->id], $I->grabDataFromResponseByJsonPath('$.spaces.selected[*].id'));

        $I->sendGet('notification/settings');
        Assert::assertSame(['web' => false, 'email' => false], $this->category($I, 'social')['channels'], 'the change is stored');

        // PATCH is the update verb
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut('notification/settings', json_encode(['categories' => ['social' => ['email' => true]]]));
        // no URL rule for PUT: not found, like any other verb a path does not take
        $I->seeResponseCodeIs(404);
        Assert::assertFalse($this->socialByMail(2));
        Assert::assertTrue(NotificationSpaceService::isTouchedSettings(User::findOne(['id' => 2])));
    }

    public function testRejectsInvalidValues(ApiTester $I)
    {
        $I->wantTo('be refused switching off the direct category, an unknown category and a channel the category has not');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $this->sendJsonPatch($I, 'notification/settings', ['categories' => ['direct' => ['email' => false]]]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('categories.direct.email', $I->grabDataFromResponseByJsonPath('$.errors')[0]);

        $this->sendJsonPatch($I, 'notification/settings', ['categories' => ['unknown' => ['email' => false]]]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('categories.unknown', $I->grabDataFromResponseByJsonPath('$.errors')[0]);

        // not active without a push provider
        $this->sendJsonPatch($I, 'notification/settings', ['categories' => ['social' => ['email' => false, 'mobile' => false]]]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('categories.social.mobile', $I->grabDataFromResponseByJsonPath('$.errors')[0]);

        // the admin category is not offered to this user
        $this->sendJsonPatch($I, 'notification/settings', ['categories' => ['admin' => ['email' => false]]]);
        $I->seeResponseCodeIs(422);

        Assert::assertTrue($this->socialByMail(2), 'nothing is written');
    }

    public function testGlobalScopeNeedsManageSettings(ApiTester $I)
    {
        $I->wantTo('be refused the global defaults as a normal user');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendGet('notification/settings?scope=global');
        $I->seeResponseCodeIs(403);

        $this->sendJsonPatch($I, 'notification/settings?scope=global', ['categories' => ['social' => ['email' => false]]]);
        $I->seeResponseCodeIs(403);
        Assert::assertNull(Yii::$app->getModule('notification')->settings->get('email.category.social'));
    }

    public function testReadsAndUpdatesTheGlobalDefaultsAsAdmin(ApiTester $I)
    {
        $I->wantTo('change the global defaults as an administrator');
        $space = Space::findOne(['id' => 2]);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendGet('notification/settings?scope=global');
        $I->seeResponseCodeIs(200);
        Assert::assertSame('global', $I->grabDataFromResponseByJsonPath('$.scope')[0]);
        Assert::assertContains('admin', $I->grabDataFromResponseByJsonPath('$.categories[*].id'));
        Assert::assertNull(json_decode($I->grabResponse(), true)['defaults']);

        $this->sendJsonPatch($I, 'notification/settings?scope=global', [
            'categories' => ['admin' => ['email' => false]],
            'spaces' => [$space->id],
        ]);

        $I->seeResponseCodeIs(200);
        Assert::assertFalse($this->category($I, 'admin')['channels']['email']);
        Assert::assertSame([$space->id], $I->grabDataFromResponseByJsonPath('$.spaces.selected[*].id'));
        Assert::assertEquals('0', Yii::$app->getModule('notification')->settings->get('email.category.admin'));
        Assert::assertSame([$space->guid], Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces'));
        // the administrator's own settings are untouched
        Assert::assertNull(Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => 1]))->get('email.category.admin'));
    }

    public function testResetsTheCallersSettings(ApiTester $I)
    {
        $I->wantTo('reset my notification settings');
        $user = User::findOne(['id' => 2]);
        $service = new NotificationSettingsService($user);
        Assert::assertSame([], $service->fromArray(['categories' => ['social' => ['email' => false]], 'spaces' => []]));
        Assert::assertTrue(NotificationSpaceService::isTouchedSettings($user));

        $I->amLoggedInAs(2);
        $this->withCsrf($I);
        $I->sendPost('notification/settings/reset');

        $I->seeResponseCodeIs(200);
        Assert::assertTrue($this->category($I, 'social')['channels']['email']);
        Yii::$app->getModule('notification')->settings->flushContentContainer();
        Assert::assertFalse(NotificationSpaceService::isTouchedSettings($user));
    }

    public function testResetAllNeedsManageUsers(ApiTester $I)
    {
        $I->wantTo('be refused resetting every user as a normal user');
        Assert::assertSame([], (new NotificationSettingsService(User::findOne(['id' => 3])))->fromArray(['categories' => ['social' => ['email' => false]]]));

        $I->amLoggedInAs(2);
        $this->withCsrf($I);
        $I->sendPost('notification/settings/reset-all');

        $I->seeResponseCodeIs(403);
        Assert::assertFalse($this->socialByMail(3));
    }

    public function testResetAllNeedsManageSettingsToo(ApiTester $I)
    {
        $I->wantTo('be refused resetting every user with ManageUsers alone');
        Assert::assertSame([], (new NotificationSettingsService(User::findOne(['id' => 3])))->fromArray(['categories' => ['social' => ['email' => false]]]));
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
            Assert::assertFalse($this->socialByMail(3));
        } finally {
            $permission->delete();
        }
    }

    public function testResetsEveryUserAsAdmin(ApiTester $I)
    {
        $I->wantTo('reset every user\'s notification settings as an administrator');
        Assert::assertSame([], (new NotificationSettingsService(User::findOne(['id' => 3])))->fromArray(['categories' => ['social' => ['email' => false]]]));

        $I->amLoggedInAs(1);
        $this->withCsrf($I);
        $I->sendPost('notification/settings/reset-all');

        $I->seeResponseCodeIs(200);
        Yii::$app->getModule('notification')->settings->flushContentContainer();
        $user = User::findOne(['id' => 3]);
        Assert::assertTrue($this->socialByMail(3));
        Assert::assertFalse(NotificationSpaceService::isTouchedSettings($user));
    }

    public function testWritesNeedACsrfToken(ApiTester $I)
    {
        $I->wantTo('be refused a write without a CSRF token');
        $I->amLoggedInAs(2);

        $this->sendJsonPatch($I, 'notification/settings', ['categories' => ['social' => ['email' => false]]]);
        $I->seeResponseCodeIs(403);

        $I->sendPost('notification/settings/reset');
        $I->seeResponseCodeIs(403);

        Assert::assertNull(Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => 2]))->get('email.category.social'));
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
