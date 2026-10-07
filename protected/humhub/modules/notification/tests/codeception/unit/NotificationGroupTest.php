<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidArgumentException;

class NotificationGroupTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testCoreGroups()
    {
        $this->assertSame('direct', NotificationGroup::direct()->id);
        $this->assertFalse(NotificationGroup::direct()->switchable);
        $this->assertSame(NotificationPriority::High, NotificationGroup::direct()->priority);
        $this->assertSame(NotificationPriority::Low, NotificationGroup::social()->priority);
        $this->assertSame(NotificationPriority::Normal, NotificationGroup::content()->priority);
        $this->assertTrue(NotificationGroup::admin()->switchable);
        $this->assertNotSame('', NotificationGroup::social()->title);
        $this->assertTrue(NotificationGroup::social()->equals(NotificationGroup::social()));
        $this->assertFalse(NotificationGroup::social()->equals(NotificationGroup::admin()));
    }

    public function testOfModuleUsesTheModuleIdAndName()
    {
        // TestNotification lives in the notification module's test namespace
        $group = NotificationGroup::ofModule(TestNotification::class);
        $this->assertSame('notification', $group->id);
        $this->assertTrue($group->switchable);
        $this->assertSame(NotificationPriority::Normal, $group->priority);
        $this->assertSame(Yii::$app->getModule('notification')->getName(), $group->title);
        $this->assertStringContainsString($group->title, $group->description);
    }

    public function testOfModuleRejectsAClassOutsideAnyModule()
    {
        $this->expectException(InvalidArgumentException::class);
        NotificationGroup::ofModule(\stdClass::class);
    }

    public function testAdminIsVisibleToAdministratorsOnly()
    {
        $admin = User::findOne(['username' => 'Admin']);
        $user = User::findOne(['username' => 'User2']);
        $this->assertTrue(NotificationGroup::admin()->isVisible($admin));
        $this->assertFalse(NotificationGroup::admin()->isVisible($user));
        $this->assertTrue(NotificationGroup::social()->isVisible($user));
    }
}
