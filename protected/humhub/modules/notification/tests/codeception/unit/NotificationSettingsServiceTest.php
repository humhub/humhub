<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\tests\codeception\unit\notifications\TestUnlistedNotification;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\services\NotificationSpaceService;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\comment\notifications\NewCommentNotification;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class NotificationSettingsServiceTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testCategoriesAreOnByDefaultUnlessTheCategorySaysOtherwise()
    {
        $service = new NotificationSettingsService($this->user(1));

        $this->assertTrue($service->isCategoryEnabled(new MailTarget(), NotificationCategory::social()));
        $this->assertTrue($service->isCategoryEnabled(new WebTarget(), NotificationCategory::followers()));
        $this->assertFalse($service->isCategoryEnabled(new MailTarget(), NotificationCategory::followers()));
        $this->assertFalse($service->isCategoryEnabled(new MobileTarget(), NotificationCategory::followers()));
        $this->assertFalse((new NotificationSettingsService())->isCategoryEnabled(new MailTarget(), NotificationCategory::followers()));

        // the global default and the user's own switch win over the category's default
        (new NotificationSettingsService())->setCategory(new MailTarget(), NotificationCategory::followers(), true);
        $this->assertTrue($service->isCategoryEnabled(new MailTarget(), NotificationCategory::followers()));
        $service->setCategory(new MailTarget(), NotificationCategory::followers(), false);
        $this->assertFalse($service->isCategoryEnabled(new MailTarget(), NotificationCategory::followers()));
    }

    public function testStoredLegacyModeIsIgnored()
    {
        Yii::$app->getModule('notification')->settings->set('email.mode', 'off');
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set('email.mode', 'off');

        $this->assertTrue((new MailTarget())->isEnabled(NewCommentNotification::class, $this->user(1)));
    }

    public function testDirectCategoryIsAlwaysEnabled()
    {
        $target = new MailTarget();
        Yii::$app->getModule('notification')->settings->set('email.category.direct', 0);
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set('email.category.direct', 0);

        $this->assertTrue((new NotificationSettingsService())->isCategoryEnabled($target, NotificationCategory::direct()));
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isCategoryEnabled($target, NotificationCategory::direct()));
    }

    public function testCategorySwitchedOffGlobally()
    {
        $target = new MailTarget();
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isCategoryEnabled($target, NotificationCategory::social()));

        (new NotificationSettingsService())->setCategory($target, NotificationCategory::social(), false);
        (new NotificationSettingsService($this->user(2)))->setCategory($target, NotificationCategory::social(), true);

        $this->assertFalse((new NotificationSettingsService())->isCategoryEnabled($target, NotificationCategory::social()));
        $this->assertFalse((new NotificationSettingsService($this->user(1)))->isCategoryEnabled($target, NotificationCategory::social()));
        $this->assertTrue((new NotificationSettingsService($this->user(2)))->isCategoryEnabled($target, NotificationCategory::social()));
        // other channels and categories are not affected
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isCategoryEnabled(new MobileTarget(), NotificationCategory::social()));
        $this->assertTrue((new NotificationSettingsService($this->user(1)))->isCategoryEnabled($target, NotificationCategory::content()));
    }

    public function testResetRemovesTheUsersKeysOnly()
    {
        $mail = new MailTarget();
        $mobile = new MobileTarget();
        $user1 = new NotificationSettingsService($this->user(1));
        $user1->setCategory($mail, NotificationCategory::social(), false);
        $user1->setCategory($mobile, NotificationCategory::content(), false);
        (new NotificationSettingsService($this->user(2)))->setCategory($mail, NotificationCategory::social(), false);
        (new NotificationSettingsService())->setCategory($mail, NotificationCategory::admin(), false);
        Yii::$app->getModule('notification')->settings->user($this->user(1))->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);

        $user1->reset();

        $this->assertSame([NotificationSpaceService::IS_TOUCHED_SETTINGS], $this->settingNames(1));
        $this->assertTrue($user1->isCategoryEnabled($mail, NotificationCategory::social()));
        $this->assertTrue($user1->isCategoryEnabled($mobile, NotificationCategory::content()));
        // inherited global default still applies
        $this->assertFalse($user1->isCategoryEnabled($mail, NotificationCategory::admin()));
        $this->assertFalse((new NotificationSettingsService($this->user(2)))->isCategoryEnabled($mail, NotificationCategory::social()));
    }

    public function testResetAllUsers()
    {
        $mail = new MailTarget();
        foreach ([1, 2] as $id) {
            $service = new NotificationSettingsService($this->user($id));
            $service->setCategory($mail, NotificationCategory::content(), true);
            $service->setCategory($mail, NotificationCategory::social(), false);
            Yii::$app->getModule('notification')->settings->user($this->user($id))->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);
        }
        (new NotificationSettingsService())->setCategory($mail, NotificationCategory::content(), false);
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
            $this->assertFalse($service->isCategoryEnabled($mail, NotificationCategory::content()));
            $this->assertTrue($service->isCategoryEnabled($mail, NotificationCategory::social()));
            $this->assertFalse(NotificationSpaceService::isTouchedSettings($this->user($id)));
        }
    }

    public function testToArrayForAUser()
    {
        (new NotificationSettingsService())->setCategory(new MailTarget(), NotificationCategory::content(), false);
        (new NotificationSettingsService($this->user(2)))->setCategory(new MailTarget(), NotificationCategory::social(), false);

        $data = (new NotificationSettingsService($this->user(2)))->toArray();

        $this->assertSame(['scope', 'channels', 'categories', 'defaults', 'spaces'], array_keys($data));
        $this->assertSame('user', $data['scope']);
        // the mobile target is not active without a push provider
        $this->assertSame([['id' => 'web', 'title' => 'Web'], ['id' => 'email', 'title' => 'E-Mail']], $data['channels']);

        $categoryIds = array_column($data['categories'], 'id');
        $this->assertSame(['direct', 'social', 'followers', 'content'], array_slice($categoryIds, 0, 4));
        $this->assertNotContains('admin', $categoryIds, 'the admin category is not shown to a user without an administrative permission');

        $direct = $this->category($data, 'direct');
        $this->assertSame(['id', 'title', 'description', 'icon', 'module', 'fixed', 'channels'], array_keys($direct));
        $this->assertSame('ti-at', $direct['icon']);
        $this->assertFalse($direct['module']);
        $this->assertTrue($direct['fixed']);
        $this->assertEquals((object)['web' => true, 'email' => true], $direct['channels']);

        $this->assertFalse($this->category($data, 'social')['fixed']);
        $this->assertEquals((object)['web' => true, 'email' => false], $this->category($data, 'social')['channels']);
        $this->assertEquals((object)['web' => true, 'email' => false], $this->category($data, 'followers')['channels']);
        $this->assertEquals((object)['web' => true, 'email' => false], $this->category($data, 'content')['channels'], 'inherited');

        // what the user's switches would be without own settings: the global ones
        $this->assertEquals((object)['web' => true, 'email' => true], $data['defaults']->social);
        $this->assertEquals((object)['web' => true, 'email' => false], $data['defaults']->content);
        $this->assertEquals((object)['web' => true, 'email' => false], $data['defaults']->followers);

        $this->assertTrue($data['spaces']['enabled']);
        $this->assertIsArray($data['spaces']['selected']);
    }

    public function testToArrayGlobalShowsEveryCategoryAndTheDefaultSpaces()
    {
        $space = Space::findOne(['id' => 1]);
        (new NotificationSpaceService())->setSpaces([$space->guid]);

        $data = (new NotificationSettingsService())->toArray();

        $this->assertSame('global', $data['scope']);
        $this->assertNull($data['defaults']);
        $this->assertContains('admin', array_column($data['categories'], 'id'));
        $this->assertSame([$space->id], array_column($data['spaces']['selected'], 'id'));
        $this->assertSame(['id', 'guid', 'name', 'url', 'color', 'imageUrl', 'contentContainerId'], array_keys($data['spaces']['selected'][0]));
    }

    public function testToArrayLeavesOutTheWebChannelOfACategoryWithoutListedNotifications()
    {
        $this->registerUnlistedModuleCategory();

        $data = (new NotificationSettingsService($this->user(2)))->toArray();

        $category = $this->category($data, 'example-chat');
        $this->assertTrue($category['module']);
        $this->assertSame('ti-puzzle', $category['icon']);
        $this->assertEquals((object)['email' => true], $category['channels']);
        $this->assertEquals((object)['email' => true], $data['defaults']->{'example-chat'});
        $this->assertSame('example-chat', array_column($data['categories'], 'id')[count($data['categories']) - 1], 'module categories come last, whatever their sort order');

        $this->assertSame(
            ['categories.example-chat.web' => ['This channel is not available for the category.']],
            (new NotificationSettingsService($this->user(2)))->fromArray(['categories' => ['example-chat' => ['web' => false]]]),
        );
    }

    public function testFromArrayWritesCategoriesAndSpaces()
    {
        $user = $this->user(2);
        $space = Space::findOne(['id' => 3]);
        $service = new NotificationSettingsService($user);

        $errors = $service->fromArray([
            'categories' => [
                'social' => ['email' => false, 'web' => false],
                'followers' => ['email' => true],
                'direct' => ['email' => true],
            ],
            'spaces' => [$space->id],
        ]);

        $this->assertSame([], $errors);
        $data = $service->toArray();
        $this->assertEquals((object)['web' => false, 'email' => false], $this->category($data, 'social')['channels']);
        $this->assertEquals((object)['web' => true, 'email' => true], $this->category($data, 'followers')['channels']);
        $this->assertEquals((object)['web' => true, 'email' => true], $this->category($data, 'content')['channels']);
        $this->assertSame([$space->id], array_column($data['spaces']['selected'], 'id'));
        $this->assertTrue(NotificationSpaceService::isTouchedSettings($user));
        // the global defaults are untouched
        $this->assertTrue((new NotificationSettingsService())->isCategoryEnabled(new MailTarget(), NotificationCategory::social()));
    }

    public function testNullFollowsTheDefaultAgain()
    {
        $user = $this->user(2);
        (new NotificationSettingsService())->setCategory(new MailTarget(), NotificationCategory::content(), false);
        $service = new NotificationSettingsService($user);
        $service->setCategory(new MailTarget(), NotificationCategory::content(), true);
        $service->setCategory(new MailTarget(), NotificationCategory::followers(), true);

        $this->assertSame([], $service->fromArray(['categories' => [
            'content' => ['email' => null],
            'followers' => ['email' => null],
            'direct' => ['email' => null],
        ]]));

        $this->assertSame([], $this->settingNames(2));
        $this->assertFalse($service->isCategoryEnabled(new MailTarget(), NotificationCategory::content()), 'the global default');
        $this->assertFalse($service->isCategoryEnabled(new MailTarget(), NotificationCategory::followers()), 'the category default');
    }

    public function testPartialUpdateKeepsTheDefaultSpaces()
    {
        $user = $this->user(2);
        $space = Space::findOne(['id' => 3]);
        (new NotificationSpaceService())->setSpaces([$space->guid]);
        $this->assertContains($space->id, array_map(fn(Space $s) => $s->id, (new NotificationSpaceService())->getSpaces($user)), 'precondition: a default space');

        $this->assertSame([], (new NotificationSettingsService($user))->fromArray(['categories' => ['social' => ['email' => false]]]));

        $this->assertFalse(NotificationSpaceService::isTouchedSettings($user));
        $this->assertContains($space->id, array_map(fn(Space $s) => $s->id, (new NotificationSpaceService())->getSpaces($user)));
    }

    public function testFromArrayGlobal()
    {
        $space = Space::findOne(['id' => 2]);
        $service = new NotificationSettingsService();

        $errors = $service->fromArray([
            'categories' => ['admin' => ['email' => false, 'web' => '0']],
            'spaces' => [(string)$space->id],
        ]);

        $this->assertSame([], $errors);
        $this->assertEquals('0', Yii::$app->getModule('notification')->settings->get('email.category.admin'));
        $this->assertEquals('0', Yii::$app->getModule('notification')->settings->get('web.category.admin'));
        $this->assertSame([$space->guid], Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces'));
    }

    public function testFromArrayRejectsInvalidValuesAndWritesNothing()
    {
        $service = new NotificationSettingsService($this->user(2));

        $errors = $service->fromArray([
            'categories' => [
                'social' => ['email' => false, 'web' => 'sometimes'],
                'direct' => ['email' => false, 'web' => true],
                'unknown' => ['email' => false],
                // not visible to the user
                'admin' => ['email' => false],
                // not active without a push provider
                'content' => ['mobile' => false],
                'followers' => [true],
            ],
            'spaces' => ['x'],
        ]);

        $this->assertSame([
            'categories.social.web' => ['Invalid value.'],
            'categories.direct.email' => ['This category cannot be switched off.'],
            'categories.unknown' => ['Unknown category.'],
            'categories.admin' => ['Unknown category.'],
            'categories.content.mobile' => ['This channel is not available for the category.'],
            'categories.followers' => ['Invalid value.'],
            'spaces' => ['Invalid spaces.'],
        ], $errors);
        $this->assertTrue($service->isCategoryEnabled(new MailTarget(), NotificationCategory::social()), 'nothing is written');
        $this->assertFalse(NotificationSpaceService::isTouchedSettings($this->user(2)));

        $this->assertSame(['categories' => ['Invalid categories.']], $service->fromArray(['categories' => [['id' => 'social']]]));
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

    private function category(array $data, string $id): array
    {
        foreach ($data['categories'] as $category) {
            if ($category['id'] === $id) {
                return $category;
            }
        }
        $this->fail('No category ' . $id);
    }

    private function registerUnlistedModuleCategory(): void
    {
        Yii::$app->set('notification', [
            'class' => NotificationManager::class,
            'targets' => Yii::$app->notification->targets,
        ]);
        Yii::$app->notification->on(NotificationManager::EVENT_SEARCH_MODULE_NOTIFICATIONS, function ($event): void {
            $event->result[] = TestUnlistedNotification::class;
        });
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
}
