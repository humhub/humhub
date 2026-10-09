<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidArgumentException;

class NotificationCategoryTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testCoreCategories()
    {
        $this->assertSame('direct', NotificationCategory::direct()->id);
        $this->assertFalse(NotificationCategory::direct()->switchable);
        $this->assertSame(NotificationPriority::High, NotificationCategory::direct()->priority);
        $this->assertSame(NotificationPriority::Low, NotificationCategory::social()->priority);
        $this->assertSame(NotificationPriority::Normal, NotificationCategory::content()->priority);
        $this->assertTrue(NotificationCategory::admin()->switchable);
        $this->assertNotSame('', NotificationCategory::social()->title);
        $this->assertTrue(NotificationCategory::social()->equals(NotificationCategory::social()));
        $this->assertFalse(NotificationCategory::social()->equals(NotificationCategory::admin()));
    }

    public function testOfModuleUsesTheModuleIdAndName()
    {
        // TestNotification lives in the notification module's test namespace
        $category = NotificationCategory::ofModule(TestNotification::class);
        $this->assertSame('notification', $category->id);
        $this->assertTrue($category->switchable);
        $this->assertSame(NotificationPriority::Normal, $category->priority);
        $this->assertSame(Yii::$app->getModule('notification')->getName(), $category->title);
        $this->assertStringContainsString($category->title, $category->description);
    }

    public function testOfModuleRejectsAClassOutsideAnyModule()
    {
        $this->expectException(InvalidArgumentException::class);
        NotificationCategory::ofModule(\stdClass::class);
    }

    public function testAdminIsVisibleToAdministratorsOnly()
    {
        $admin = User::findOne(['username' => 'Admin']);
        $user = User::findOne(['username' => 'User2']);
        $this->assertTrue(NotificationCategory::admin()->isVisible($admin));
        $this->assertFalse(NotificationCategory::admin()->isVisible($user));
        $this->assertTrue(NotificationCategory::social()->isVisible($user));
    }

    public function testVisibilityFollowsThePermissions()
    {
        $admin = User::findOne(['username' => 'Admin']);
        $user = User::findOne(['username' => 'User2']);

        $everyone = new NotificationCategory('example-news', 'News');
        $this->assertSame([], $everyone->permissions);
        $this->assertTrue($everyone->isVisible($user));

        $managers = new NotificationCategory('example-reports', 'Reports', permissions: [ManageUsers::class]);
        $this->assertTrue($managers->isVisible($admin));
        $this->assertFalse($managers->isVisible($user));

        $this->assertSame([ManageSettings::class, ManageUsers::class, ManageSpaces::class], NotificationCategory::admin()->permissions);
    }

    public function testFollowersAreOffForMailAndPushByDefault()
    {
        $this->assertSame([MailTarget::ID, MobileTarget::ID], NotificationCategory::followers()->offByDefault);
        $this->assertFalse(NotificationCategory::followers()->isEnabledByDefault(MailTarget::ID));
        $this->assertTrue(NotificationCategory::followers()->isEnabledByDefault(WebTarget::ID));
        $this->assertTrue(NotificationCategory::direct()->isEnabledByDefault(MailTarget::ID));
    }
}
