<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\components\access\ControllerAccess;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\Impersonation;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\log\Logger;

/**
 * Tests the impersonation state and restrictions provided by [[Impersonation]] (`Yii::$app->user->impersonation`).
 *
 * The fixture setup used here:
 *  - `Admin` (id 1) may impersonate, `User1` (id 2) is the impersonated user
 *  - Space 3 is visible for all, `Admin`, `User1` and `User2` are members
 *  - Space 5 is a private (invisible) space, `Admin` and `User1` are members
 *
 * @since 1.19
 */
class ImpersonationTest extends HumHubDbTestCase
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

        Yii::$app->user->impersonation->allowPrivateContentAccess = false;
        Yii::$app->user->impersonation->log = true;
        Yii::$app->getModule('admin')->allowUserImpersonate = true;
    }

    /**
     * All configuration combinations with their expected effect:
     * [allowPrivateContentAccess, log]
     */
    public static function configurationProvider(): array
    {
        return [
            'private content hidden, logged (default)' => [false, true],
            'private content hidden, not logged' => [false, false],
            'full access, logged' => [true, true],
            'full access, not logged' => [true, false],
        ];
    }

    public function testDefaults()
    {
        $impersonation = new Impersonation();

        $this->assertFalse(
            $impersonation->allowPrivateContentAccess,
            'By default private content is hidden while impersonating',
        );
        $this->assertTrue($impersonation->log, 'By default impersonations are logged');
    }

    public function testComponentConfiguration()
    {
        Yii::$app->user->impersonation = ['allowPrivateContentAccess' => true, 'log' => false];

        $this->assertTrue(Yii::$app->user->impersonation->allowPrivateContentAccess);
        $this->assertFalse(Yii::$app->user->impersonation->log);
    }

    public function testStartAndStop()
    {
        $this->becomeUser('Admin');
        $this->assertFalse(Yii::$app->user->impersonation->isActive());

        $this->impersonate('User1');
        $this->assertSame('Admin', Yii::$app->user->impersonation->getImpersonator()->username);

        $this->assertTrue(Yii::$app->user->impersonation->stop());
        $this->assertFalse(Yii::$app->user->impersonation->isActive());
        $this->assertSame('Admin', Yii::$app->user->getIdentity()->username);
    }

    public function testStartWithoutPermission()
    {
        $this->becomeUser('User3');
        $user2 = User::findOne(['username' => 'User2']);

        $this->assertFalse(Yii::$app->user->impersonation->canStart($user2));
        $this->assertFalse(Yii::$app->user->impersonation->start($user2));
        $this->assertFalse(Yii::$app->user->impersonation->isActive());
        $this->assertSame('User3', Yii::$app->user->getIdentity()->username);
    }

    public function testCannotStartForTheCurrentUser()
    {
        $this->becomeUser('Admin');

        $this->assertFalse(Yii::$app->user->impersonation->canStart(User::findOne(['username' => 'Admin'])));
    }

    public function testCanBeDisabledCompletely()
    {
        Yii::$app->getModule('admin')->allowUserImpersonate = false;

        $this->becomeUser('Admin');

        $this->assertFalse(Yii::$app->user->impersonation->canStart(User::findOne(['username' => 'User1'])));
    }

    /**
     * An API request authenticated by the browser session runs with a session-less user
     * component, so `enableSession` alone would report "no impersonation" and the
     * private-content restriction would silently not apply to the platform's own frontend
     * calling the API while impersonating.
     *
     * @see \humhub\components\Request::$isSessionAuthenticated
     * @see \humhub\components\api\SessionAuth
     */
    public function testIsActiveOnASessionAuthenticatedApiRequest()
    {
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        // Mirrors what the API base controller pins for every API request
        Yii::$app->user->enableSession = false;
        $this->assertFalse(
            Yii::$app->user->impersonation->isActive(),
            'A stateless (token) API request must not count as an impersonating session',
        );
        $this->assertTrue(Yii::$app->user->impersonation->canAccessPrivateContent());

        Yii::$app->request->isSessionAuthenticated = true;
        $this->assertTrue(
            Yii::$app->user->impersonation->isActive(),
            'A session-authenticated API request is the browser session and must be restricted',
        );
        $this->assertFalse(Yii::$app->user->impersonation->canAccessPrivateContent());
    }

    public function testOnlyTheUserIdIsStoredInTheSession()
    {
        $this->becomeUser('Admin');
        $adminId = Yii::$app->user->getIdentity()->id;

        $this->impersonate('User1');

        $data = Yii::$app->session->get(Impersonation::SESSION_KEY);
        $this->assertIsArray($data, 'The session must not hold a serialized user record');
        $this->assertSame($adminId, $data['id']);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testCanAccessPrivateContent(bool $allowPrivateContentAccess, bool $log)
    {
        $this->configureImpersonation($allowPrivateContentAccess, $log);

        $this->becomeUser('Admin');
        $this->assertTrue(
            Yii::$app->user->impersonation->canAccessPrivateContent(),
            'A user who does not impersonate is never restricted',
        );

        $this->impersonate('User1');
        $this->assertSame($allowPrivateContentAccess, Yii::$app->user->impersonation->canAccessPrivateContent());

        $this->assertTrue(Yii::$app->user->impersonation->stop());
        $this->assertTrue(
            Yii::$app->user->impersonation->canAccessPrivateContent(),
            'The restriction is lifted once the impersonation has been stopped',
        );
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testImpersonationIsLogged(bool $allowPrivateContentAccess, bool $log)
    {
        $this->configureImpersonation($allowPrivateContentAccess, $log);

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

        if ($log) {
            static::assertLog($expectedMessage, Logger::LEVEL_WARNING, ['user']);
        } else {
            static::assertNotLog($expectedMessage, Logger::LEVEL_WARNING, ['user']);
        }

        static::logReset();
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testPrivateContentIsHidden(bool $allowPrivateContentAccess, bool $log)
    {
        $space = Space::findOne(['id' => self::VISIBLE_SPACE_ID]);
        $publicContent = $this->createPost($space, Content::VISIBILITY_PUBLIC);
        $privateContent = $this->createPost($space, Content::VISIBILITY_PRIVATE);

        $this->configureImpersonation($allowPrivateContentAccess, $log);
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        $this->assertTrue(
            $publicContent->canView(),
            'Public content stays visible in every impersonation mode',
        );
        $this->assertSame(
            $allowPrivateContentAccess,
            $privateContent->canView(),
            'Private content of a space the impersonated user is a member of',
        );

        $this->assertSame(
            $allowPrivateContentAccess ? 2 : 1,
            (int)Post::find()->readable()->count(),
            'Readable content query',
        );
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testOwnPrivateContentIsHidden(bool $allowPrivateContentAccess, bool $log)
    {
        $user1 = User::findOne(['username' => 'User1']);
        $ownContent = $this->createPost($user1, Content::VISIBILITY_PRIVATE, 'User1');

        $this->configureImpersonation($allowPrivateContentAccess, $log);
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        $this->assertSame(
            $allowPrivateContentAccess,
            $ownContent->canView(),
            'Private content of the impersonated user itself is hidden as well',
        );
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testPrivateSpaceIsNotAccessible(bool $allowPrivateContentAccess, bool $log)
    {
        $this->configureImpersonation($allowPrivateContentAccess, $log);
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        $privateSpace = Space::findOne(['id' => self::PRIVATE_SPACE_ID]);
        $this->assertSame(Space::VISIBILITY_NONE, (int)$privateSpace->visibility);
        $this->assertTrue($privateSpace->isMember(), 'The impersonated user is a member of the private space');

        $this->assertSame(
            $allowPrivateContentAccess,
            $privateSpace->canAccessPrivateContent(),
            'Access to the private content of a private space',
        );

        $this->assertSame(
            $allowPrivateContentAccess,
            Space::find()->visible()->andWhere(['space.id' => self::PRIVATE_SPACE_ID])->exists(),
            'The private space is hidden from space listings',
        );

        $visibleSpace = Space::findOne(['id' => self::VISIBLE_SPACE_ID]);
        $this->assertTrue(
            Space::find()->visible()->andWhere(['space.id' => self::VISIBLE_SPACE_ID])->exists(),
            'A space which is visible for all stays listed in every impersonation mode',
        );
        $this->assertSame(
            $allowPrivateContentAccess,
            $visibleSpace->canAccessPrivateContent(),
            'Access to the private content of a space which is visible for all',
        );
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testControllerAccessRule(bool $allowPrivateContentAccess, bool $log)
    {
        $this->configureImpersonation($allowPrivateContentAccess, $log);
        $this->becomeUser('Admin');

        $this->assertTrue($this->runDenyImpersonatedRule(), 'A user who does not impersonate has access');

        $this->impersonate('User1');

        $this->assertSame($allowPrivateContentAccess, $this->runDenyImpersonatedRule());
    }

    public function testStopFailsClosedWhenTheImpersonatorCannotBeRestored()
    {
        $this->becomeUser('Admin');
        $adminId = Yii::$app->user->getIdentity()->id;

        $this->impersonate('User1');

        User::updateAll(['status' => User::STATUS_DISABLED], ['id' => $adminId]);

        $this->assertTrue(Yii::$app->user->impersonation->isActive());
        $this->assertFalse(
            Yii::$app->user->impersonation->canAccessPrivateContent(),
            'The restriction stays in place even when the impersonator cannot be resolved',
        );
        $this->assertNull(Yii::$app->user->impersonation->getImpersonator());

        $this->assertTrue(Yii::$app->user->impersonation->stop());
        $this->assertTrue(
            Yii::$app->user->isGuest,
            'The session is logged out instead of silently continuing as the impersonated user',
        );
    }

    public function testLegacySessionMarkerFailsClosed()
    {
        $this->becomeUser('Admin');
        $this->impersonate('User1');

        // Sessions written before 1.19 stored the serialized user record instead of the id
        Yii::$app->session->set(Impersonation::SESSION_KEY, User::findOne(['username' => 'Admin']));

        $this->assertTrue(Yii::$app->user->impersonation->isActive());
        $this->assertFalse(Yii::$app->user->impersonation->canAccessPrivateContent());
        $this->assertNull(Yii::$app->user->impersonation->getImpersonator());

        $this->assertTrue(Yii::$app->user->impersonation->stop());
        $this->assertTrue(Yii::$app->user->isGuest);
    }

    private function runDenyImpersonatedRule(): bool
    {
        $access = new ControllerAccess(['action' => 'index']);
        $access->setRules([
            [ControllerAccess::RULE_DENY_IMPERSONATED],
        ]);

        return $access->run();
    }

    private function configureImpersonation(bool $allowPrivateContentAccess, bool $log): void
    {
        Yii::$app->user->impersonation->allowPrivateContentAccess = $allowPrivateContentAccess;
        Yii::$app->user->impersonation->log = $log;
    }

    private function impersonate(string $userName): void
    {
        $this->assertFalse(Yii::$app->user->impersonation->isActive());

        $this->assertTrue(Yii::$app->user->impersonation->start(User::findOne(['username' => $userName])));

        $this->assertTrue(Yii::$app->user->impersonation->isActive());
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
