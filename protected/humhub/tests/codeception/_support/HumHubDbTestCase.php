<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace tests\codeception\_support;

use Codeception\Configuration;
use Codeception\Test\Unit;
use humhub\libs\BasePermission;
use humhub\modules\content\components\ContentContainerPermissionManager;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use PHPUnit\Framework\SkippedTestError;
use ReflectionAttribute;
use ReflectionClass;
use Yii;

/**
 * HumHub test case
 * ---
 * ## Fixture Configuration
 *
 * Fixtures are configured in the test configuration and can be overwritten per unit and per test.
 *
 * - Per-Unit Fixture Configuration: \
 *   Annotate the **class** with an annotation that extends from `FixtureConfig`.
 * - Per-Test Fixture Configuration: \
 *    Annotate the **method** with an annotation that extends from `FixtureConfig`.
 *
 * For configuration options, see `FixtureConfig`.
 *
 * While evaluating the configuration for a specific test, the following priority applies:
 *    1. Test method's first annotation deriving from `FixtureConfig`
 *    2. Class's first annotation deriving from `FixtureConfig`
 *    3. Fixture provided by the configuration.
 *
 * If you create your own Fixture Configuration class, please make sure it extends from `FixtureConfig` and has the
 *  attribute `#[\Attribute]` set.
 *
 * **Note:** Since Attributes are only supported as of PHP version 8, mark the entire unit to be skipped by adding
 * `static::isPhpVersion()` to your `static::setUp()` method, or at the top of individual test methods.
 *
 * @see FixtureConfig
 */
#[FixtureLegacy]
class HumHubDbTestCase extends Unit
{
    use HumHubHelperTrait;

    /**
     * ## Deprecated Per-Class Fixture Configuration
     * Possible values are:
     *  - `null`: the setting from the configuration is used
     *  - `['default']`: the result from `$this->getDefaultFixtures()` is used
     *  - `['alias1', 'alias2', ...]`: the alias denotes the fixture definition key as specified in the current class'
     *    `getDefaultFixtures()`.
     *     The value will be replaced with the respective fixture definition.
     *     (A combination with `default` does not throw an error, but makes no sense either, since the alias is already
     *     included in `default`.)
     *  - `[$fixtureTable => $fixtureClass, ...]`: the specified fixture classes are loaded
     *  - `['default', $fixtureTable => $fixtureClass, ...]`: the result from `$this->getDefaultFixtures()` is merged
     *     with the specified fixtures.
     *     Only meaningful if the additional fixture is not included in the default set.
     *  - `['Alias1', 'alias2', $fixtureTable => $fixtureClass, ...]`: the two aliases are retrieved from
     *      `$this->getDefaultFixtures()` (see above under `alias` syntax) and is merged with the specified fixtures.
     *
     * @var array|null Fixture configuration
     *
     * @see FixtureConfig
     * @deprecated since 1.16; use `FixtureConfig` attributes instead
     */
    protected ?array $fixtureConfig = null;

    public $appConfig = '@tests/codeception/config/unit.php';

    public $time;

    public array $firedEvents = [];

    protected function setUp(): void
    {
        if (Yii::$app !== null) {
            Yii::$app->db->trigger('afterOpen');
        }

        if (Yii::$app === null) {
            $c = new ReflectionClass($this);
            $m = $c->getMethod($this->getName(false));
            $doc = $m->getDocComment();
            if (preg_match('#@skip(.*?)\r?\n#s', $doc, $annotations)) {
                throw new SkippedTestError(
                    "Test was skipped due to @skip annotation: " . (trim($annotations[1]) ?: "[No reason indicated!]"),
                    0
                );
            }
            return;
        }

        $webRoot = dirname(__DIR__, 2) . '/../../..';
        Yii::setAlias('@webroot', realpath($webRoot));
        static::initModules(__METHOD__);
        static::reloadSettings(__METHOD__);
        static::flushCache(__METHOD__);
        static::deleteMails(__METHOD__);

        parent::setUp();
    }

    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected static function initModules(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Initializing Modules', $caller ?? __METHOD__));
        $cfg = Configuration::config();

        if (!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }

    /**
     * @see FixtureConfig for default configuration for the entire class.
     * There you also find further details on how to configure the fixture on a per-test basis.
     *
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     **/
    public function _fixtures(): array
    { // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        return FixtureLegacy::create(
            $this,
            $this->getName(),
            $this->fixtureConfig,
            static::getDefaultFixtures()
        )->getFixtures();
    }


    /**
     * @return array|null
     * @deprecated since 1.16; use FixtureConfig instead.
     * @see FixtureConfig
     */
    protected static function getDefaultFixtures(): ?array
    {
        return null;
    }

    /**
     * @param bool $allow
     */
    public static function allowGuestAccess(bool $allow = true)
    {
        Yii::$app
            ->getModule('user')
            ->settings
            ->set('auth.allowGuestAccess', (int)$allow);
    }

    public static function setProfileField($field, $value, $user)
    {
        if (is_int($user)) {
            $user = User::findOne($user);
        } elseif (is_string($user)) {
            $user = User::findOne(['username' => $user]);
        } elseif (!$user) {
            $user = Yii::$app->user->identity;
        }

        $user->profile->setAttributes([$field => $value]);
        $user->profile->save();
    }

    public static function becomeFriendWith($username)
    {
        $user = User::findOne(['username' => $username]);
        Friendship::add($user, Yii::$app->user->identity);
        Friendship::add(Yii::$app->user->identity, $user);
    }

    public static function follow($username)
    {
        User::findOne(['username' => $username])->follow();
    }

    public static function enableFriendships($enable = true)
    {
        Yii::$app->getModule('friendship')->settings->set('enable', $enable);
    }

    public static function setGroupPermission($groupId, $permission, $state = BasePermission::STATE_ALLOW)
    {
        $permissionManger = new PermissionManager();
        $permissionManger->setGroupState($groupId, $permission, $state);
        Yii::$app->user->permissionManager->clear();
    }

    public static function setContentContainerPermission(
        $contentContainer,
        $groupId,
        $permission,
        $state = BasePermission::STATE_ALLOW
    ) {
        $permissionManger = new ContentContainerPermissionManager(['contentContainer' => $contentContainer]);
        $permissionManger->setGroupState($groupId, $permission, $state);
        $contentContainer->permissionManager->clear();
    }

    public static function becomeUser($userName): ?User
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
        return $user;
    }

    public static function logout()
    {
        Yii::$app->user->logout();
    }
}
