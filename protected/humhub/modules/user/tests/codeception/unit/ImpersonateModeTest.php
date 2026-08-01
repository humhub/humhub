<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\components\access\ControllerAccess;
use humhub\modules\admin\Module as AdminModule;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use ReflectionClass;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\log\Logger;

/**
 * Tests the impersonation modes provided by [[AdminModule::$impersonateMode]].
 *
 * The fixture setup used here:
 *  - `Admin` (id 1) may impersonate, `User1` (id 2) is the impersonated user
 *  - Space 3 is visible for all, `Admin`, `User1` and `User2` are members
 *  - Space 5 is a private (invisible) space, `Admin` and `User1` are members
 *
 * @since 1.19
 */
class ImpersonateModeTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    /**
     * Space 3 of the fixtures: visibility "Visible for all", `User1` is a member
     */
    private const VISIBLE_SPACE_ID = 3;

    /**
     * Space 5 of the fixtures: visibility "Private (Invisible)", `User1` is a member
     */
    private const PRIVATE_SPACE_ID = 5;

    public function _before()
    {
        parent::_before();

        Content::deleteAll();
    }

    public function _after()
    {
        $this->setImpersonateMode(AdminModule::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED);

        parent::_after();
    }

    /**
     * All impersonation modes with their expected effect:
     * [mode, private content is denied, impersonation is logged]
     */
    public static function impersonateModeProvider(): array
    {
        return [
            'full access' => [AdminModule::IMPERSONATE_MODE_FULL_ACCESS, false, false],
            'full access, logged' => [AdminModule::IMPERSONATE_MODE_FULL_ACCESS_LOGGED, false, true],
            'deny private content' => [AdminModule::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT, true, false],
            'deny private content, logged' => [AdminModule::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED, true, true],
        ];
    }

    public function testDefaultImpersonateMode()
    {
        $defaults = (new ReflectionClass(AdminModule::class))->getDefaultProperties();

        $this->assertSame(
            AdminModule::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED,
            $defaults['impersonateMode'],
            'By default private content is denied and impersonations are logged',
        );
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testModuleModeFlags(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $module = $this->setImpersonateMode($mode);

        $this->assertSame($expectDenied, $module->isImpersonatePrivateContentDenied());
        $this->assertSame($expectLogged, $module->isImpersonateLogged());
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testIsPrivateContentRestricted(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $this->setImpersonateMode($mode);

        $this->becomeUser('Admin');
        $this->assertFalse(
            Yii::$app->user->isPrivateContentRestricted,
            'A user which does not impersonate is never restricted',
        );

        $this->impersonate('User1');
        $this->assertSame($expectDenied, Yii::$app->user->isPrivateContentRestricted);

        $this->assertTrue(Yii::$app->user->restoreImpersonator());
        $this->assertFalse(
            Yii::$app->user->isPrivateContentRestricted,
            'The restriction is lifted once the impersonation has been stopped',
        );
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testImpersonationIsLogged(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $this->setImpersonateMode($mode);

        $admin = $this->becomeUser('Admin');
        $user1 = User::findOne(['username' => 'User1']);
        $expectedMessage = sprintf(
            'User "%s" (ID: %d) impersonates user "%s" (ID: %d).',
            $admin->displayName,
            $admin->id,
            $user1->displayName,
            $user1->id,
        );

        static::logInitialize();

        $this->impersonate('User1');

        if ($expectLogged) {
            static::assertLog($expectedMessage, Logger::LEVEL_WARNING, ['user']);
        } else {
            static::assertNotLog($expectedMessage, Logger::LEVEL_WARNING, ['user']);
        }

        static::logReset();
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testPrivateContentIsHidden(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $space = Space::findOne(['id' => self::VISIBLE_SPACE_ID]);
        $publicContent = $this->createPost($space, Content::VISIBILITY_PUBLIC);
        $privateContent = $this->createPost($space, Content::VISIBILITY_PRIVATE);

        $this->setImpersonateMode($mode);
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        $this->assertTrue(
            $publicContent->canView(),
            'Public content stays visible in every impersonation mode',
        );
        $this->assertSame(
            !$expectDenied,
            $privateContent->canView(),
            'Private content of a space the impersonated user is a member of',
        );

        $this->assertSame(
            $expectDenied ? 1 : 2,
            (int)Post::find()->readable()->count(),
            'Readable content query',
        );
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testOwnPrivateContentIsHidden(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $user1 = User::findOne(['username' => 'User1']);
        $ownContent = $this->createPost($user1, Content::VISIBILITY_PRIVATE, 'User1');

        $this->setImpersonateMode($mode);
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        $this->assertSame(
            !$expectDenied,
            $ownContent->canView(),
            'Private content of the impersonated user itself is hidden as well',
        );
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testPrivateSpaceIsNotAccessible(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $this->setImpersonateMode($mode);
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        $privateSpace = Space::findOne(['id' => self::PRIVATE_SPACE_ID]);
        $this->assertSame(Space::VISIBILITY_NONE, (int)$privateSpace->visibility);
        $this->assertTrue($privateSpace->isMember(), 'The impersonated user is a member of the private space');

        $this->assertSame(
            !$expectDenied,
            $privateSpace->canAccessPrivateContent(),
            'Access to the private content of a private space',
        );

        $this->assertSame(
            !$expectDenied,
            Space::find()->visible()->andWhere(['space.id' => self::PRIVATE_SPACE_ID])->exists(),
            'The private space is hidden from space listings',
        );

        $visibleSpace = Space::findOne(['id' => self::VISIBLE_SPACE_ID]);
        $this->assertTrue(
            Space::find()->visible()->andWhere(['space.id' => self::VISIBLE_SPACE_ID])->exists(),
            'A space which is visible for all stays listed in every impersonation mode',
        );
        $this->assertSame(
            !$expectDenied,
            $visibleSpace->canAccessPrivateContent(),
            'Access to the private content of a space which is visible for all',
        );
    }

    /**
     * @dataProvider impersonateModeProvider
     */
    public function testControllerAccessRule(string $mode, bool $expectDenied, bool $expectLogged)
    {
        $this->setImpersonateMode($mode);
        $this->becomeUser('Admin');

        $this->assertTrue($this->runPrivateContentAccessRule(), 'A user which does not impersonate has access');

        $this->impersonate('User1');

        $this->assertSame($expectDenied, !$this->runPrivateContentAccessRule());
    }

    private function runPrivateContentAccessRule(): bool
    {
        $access = new ControllerAccess(['action' => 'index']);
        $access->setRules([
            [ControllerAccess::RULE_PRIVATE_CONTENT_ACCESS],
        ]);

        return $access->run();
    }

    private function setImpersonateMode(string $mode): AdminModule
    {
        /* @var AdminModule $module */
        $module = Yii::$app->getModule('admin');
        $module->impersonateMode = $mode;

        return $module;
    }

    private function impersonate(string $userName): void
    {
        $this->assertFalse(Yii::$app->user->isImpersonated);

        $this->assertTrue(Yii::$app->user->impersonate(User::findOne(['username' => $userName])));

        $this->assertTrue(Yii::$app->user->isImpersonated);
        $this->assertSame($userName, Yii::$app->user->getIdentity()->username);
    }

    private function createPost($container, int $visibility, string $authorUserName = 'User2'): Content
    {
        $this->becomeUser($authorUserName);

        $post = new Post(['message' => 'Test Content']);
        $post->content->container = $container;
        $post->content->visibility = $visibility;

        $this->assertTrue($post->save());

        $this->logout();

        return $post->content;
    }
}
