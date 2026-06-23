<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\models\User;
use humhub\modules\user\source\BaseUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Tests that User::isEmailRequired() consults the UserSource when set.
 */
class UserEmailRequiredTest extends HumHubDbTestCase
{
    // -------------------------------------------------------------------------
    // Fallback to global setting when no source / unregistered source
    // -------------------------------------------------------------------------

    public function testGlobalRequiredWhenNoSource(): void
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $userModule->emailRequired = true;

        $user = new User();
        $user->user_source = '';

        $this->assertTrue($user->isEmailRequired());
    }

    public function testGlobalNotRequiredWhenNoSource(): void
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $userModule->emailRequired = false;

        $user = new User();
        $user->user_source = '';

        $this->assertFalse($user->isEmailRequired());

        // Restore default
        $userModule->emailRequired = true;
    }

    public function testGlobalSettingUsedForUnregisteredSource(): void
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $userModule->emailRequired = true;

        $user = new User();
        $user->user_source = 'nonexistent-source-xyz';

        // getUserSourceInstance() catches the InvalidArgumentException → null → falls back
        $this->assertTrue($user->isEmailRequired());
    }

    // -------------------------------------------------------------------------
    // Source declares email optional → isEmailRequired() must return false
    // -------------------------------------------------------------------------

    public function testSourceOptionalEmailReturnsFalse(): void
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $userModule->emailRequired = true; // global says required, source overrides

        $stub = new class extends BaseUserSource {
            public function getId(): string { return 'test-optional'; }
            public function createUser(array $attributes): ?User { return null; }
            public function isEmailRequired(): bool { return false; }
        };

        Yii::$app->userSourceCollection->setUserSource('test-optional', $stub);

        $user = new User();
        $user->user_source = 'test-optional';

        $this->assertFalse($user->isEmailRequired());
    }

    public function testSourceOptionalEmailReturnsFalseEvenWhenGlobalFalse(): void
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $userModule->emailRequired = false;

        $stub = new class extends BaseUserSource {
            public function getId(): string { return 'test-optional-2'; }
            public function createUser(array $attributes): ?User { return null; }
            public function isEmailRequired(): bool { return false; }
        };

        Yii::$app->userSourceCollection->setUserSource('test-optional-2', $stub);

        $user = new User();
        $user->user_source = 'test-optional-2';

        $this->assertFalse($user->isEmailRequired());

        // Restore default
        $userModule->emailRequired = true;
    }

    // -------------------------------------------------------------------------
    // Default BaseUserSource returns true (backward-compatibility)
    // -------------------------------------------------------------------------

    public function testBaseSourceDefaultEmailRequired(): void
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $userModule->emailRequired = true;

        $stub = new class extends BaseUserSource {
            public function getId(): string { return 'test-default'; }
            public function createUser(array $attributes): ?User { return null; }
            // isEmailRequired() NOT overridden — must inherit true from BaseUserSource
        };

        Yii::$app->userSourceCollection->setUserSource('test-default', $stub);

        $user = new User();
        $user->user_source = 'test-default';

        $this->assertTrue($user->isEmailRequired());
    }
}
