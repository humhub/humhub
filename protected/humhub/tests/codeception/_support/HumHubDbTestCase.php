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
use Codeception\Exception\ConfigurationException;
use Codeception\Module\Yii2;
use Codeception\Test\Unit;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\exceptions\InvalidConfigTypeException;
use humhub\libs\BasePermission;
use humhub\modules\activity\tests\codeception\fixtures\ActivityFixture;
use humhub\modules\content\components\ContentContainerPermissionManager;
use humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture;
use humhub\modules\content\tests\codeception\fixtures\ContentFixture;
use humhub\modules\file\tests\codeception\fixtures\FileFixture;
use humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\tests\codeception\fixtures\FriendshipFixture;
use humhub\modules\live\tests\codeception\fixtures\LiveFixture;
use humhub\modules\notification\tests\codeception\fixtures\NotificationFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use humhub\tests\codeception\fixtures\SettingFixture;
use humhub\tests\codeception\fixtures\UrlOembedFixture;
use PHPUnit\Framework\SkippedTestError;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Traversable;
use Yii;
use yii\base\InvalidConfigException;

/**
 * @SuppressWarnings(PHPMD)
 */
class HumHubDbTestCase extends Unit
{
    use HumHubHelperTrait;

    /** @internal */
    protected const FIXTURE_SOURCE_CONFIG = 'CONFIG';
    /** @internal */
    protected const FIXTURE_SOURCE_PARAM = 'PARAM';
    /** @internal */
    protected const FIXTURE_SOURCE_CLASS = 'CLASS';

    /**
     * ---
     * Default per-class definition for fixtures:
     *  - `null`: the setting from the configuration is used
     *  - `'default'` or `['default']`: the result from `$this->getDefaultFixtures()` is used
     *  - `'alias'` or `['alias']`: the alias denotes the fixture definition key as specified in the current class'
     *     `getDefaultFixtures()`. It will be replaced with the respective fixture definition. A combination with
     * `default` does not throw an error, but makes no sense either, since the alias is already included in `default`
     *  - `[$fixtureTable => $fixtureClass, ...]`: the specified fixture classes are loaded
     *  - `['default', $fixtureTable => $fixtureClass, ...]`: the result from `$this->getDefaultFixtures()` is merged
     *      with the specified fixtures. Only meaningful if the additional fixture is not included in the default set.
     *  - `['alias1', 'alias2', $fixtureTable => $fixtureClass, ...]`: the two aliases are retrieved from
     *      `$this->getDefaultFixtures()` (see above under `alias` syntax) and is merged with the specified fixtures.
     *  - `false`: fixtures are disabled
     *  - `true`: If configured, the fixtures from the configuration will be used, or the `default` set otherwise. This
     *     differs from `null` insofar as it will fall back to `default` if there is no fixture specified in the
     *     configuration.
     *
     * In addition to the per-class configuration possible in `static::$fixtureConfig`, there is also the possibility
     * to use a per-method configuration that is still loaded *before* the method is run:
     * Simply add the special method parameter `$fixtures` WITH A DEFAULT VALUE. The latter will be used to overrule
     * the per-class definition. The same rules as above apply. If you do want to use the per-class configuration, you
     * MUST NOT add this method parameter.
     *
     * While evaluation the setting for a specific test, the following priority applies:
     *  1. test method's function parameter "$fixtures"
     *  2. class's field "$fixtureConfig"
     *  3. fixture provided by the configuration.     *
     *
     * @var null|bool|array|string Flag or fixture configuration
     *
     * @see static::_fixtures()
     * @see static::getDefaultFixtures()
     * @see Yii2
     * @see Yii2::loadFixtures
     * @see Yii2::haveFixtures
     */
    protected $fixtureConfig;

    /**
     * Caches the `[$name => $className]` pairs from the getDefaultFixtures()
     *
     * @internal
     */
    protected array $fixtureConfigurationDefaultFixtures;

    /**
     * Caches the `(string)'default', (string)$name1, (string)$name2, ...` string for error messages
     *
     * @internal
     */
    protected string $fixtureAliases;

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
     * @throws InvalidConfigException
     * @throws ConfigurationException
     *
     * @see static::$fixtureConfig for default coniguration for the entire class.
     * There you also find further details
     * on how to configure the fixture on a per-test basis with the special method parameter `$fixtures`
     *
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     **/
    public function _fixtures(): array
    {
        // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        $this->_fixtures_prepare();

        /**
         * Here the fixture setting for the current test is evaluated. According to the following precedence:
         * 1. test method's function parameter "$fixtures"
         * 2. class's field "$fixtureConfig"
         * 3. fixture provided by the configuration.
         *
         * Note: the following function is marked as "internal" and "deprecated".
         * The former to denote that developers should not use it other than at this place - and int the self-test.
         * The latter is there to make it more noticeable in IDEs
         *
         * @noinspection PhpDeprecationInspection
         * @see          self::$fixtureConfig for more Information
         */
        $fixtureConfig = $this->_fixtures_evaluate_setting();

        if ($fixtureConfig === null) {
            return [];
        }

        $result = [];

        foreach ($fixtureConfig as $fixtureTable => $fixtureClass) {
            switch (true) {
                // check if the default fixture set is requested
                case $fixtureClass === 'default':
                    $this->debugSection('Adding the default fixtures to the mix ...', 'Fixtures');
                    $result = array_merge($result, static::getDefaultFixtures());
                    break;

                // check for an alias and get its definition from the default fixture set
                case is_int($fixtureTable):
                    $this->isFixtureAlias($fixtureClass, null, "Unknown fixture alias!");
                    $result[$fixtureClass] = ['class' => $this->fixtureConfigurationDefaultFixtures[$fixtureClass]];
                    break;

                // otherwise, assume a `[$fixtureTable => $fixtureClass]`-pair
                default:
                    $result[$fixtureTable] = ['class' => $fixtureClass];
            }
        }

        return $result;
    }

    /**
     * Needs to be in a separate method in order to properly self-test it.
     *
     * @param object|null $class Only required for testing purposes
     * @param string|null $method Only required for testing purposes
     *
     * @return array|bool|mixed|string|string[]|HumHubDbTestCase|null
     * @throws ConfigurationException
     * @throws InvalidConfigException
     * @throws InvalidConfigTypeException
     *
     * @deprecated Not really deprecated, but just don't use it. Only for internal use!
     * @internal
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _fixtures_evaluate_setting(&$defaultValue = null, object $class = null, string $method = null)
    {
        // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        $fixtureConfig = $this->fixtureConfig;

        try {
            // get a list of parameter
            $rm = new ReflectionMethod($class ?? $this, $method ?? $this->getName());

            // index them by their name
            $rp = array_column($rm->getParameters(), null, 'name');

            /**
             * Try to get the $fixtures parameter
             *
             * @var ReflectionParameter|null $rpFixtures
             */
            $rpFixtures = $rp['fixtures'] ?? null;

            if ($rpFixtures) {
                // parameter is specified!

                // set the source of the config to "PARAM"
                $source = self::FIXTURE_SOURCE_PARAM;

                // check if the parameter has a default value or throw an error otherwise
                if (!$rpFixtures->isDefaultValueAvailable()) {
                    throw new InvalidConfigException(sprintf(
                        "%s::%s: The test method's magic parameter '\$fixture' has no default value set. Please add a valid default value or remove the parameter. See %s::%s for more information.",
                        static::class,
                        $this->getName(),
                        static::class,
                        '$fixtureConfig'
                    ));
                }

                // actually get the default value
                $defaultValue = $rpFixtures->getDefaultValue();

                // reset class configuration
                $fixtureConfig = null;

                if (
                    $defaultValue !== null
                    && $defaultValue !== 'default'
                    && !is_array($defaultValue)
                    && !is_bool($defaultValue)
                ) {
                    // throws an Exception if it's not an alias
                    $this->isFixtureAlias(
                        $defaultValue,
                        false,
                        "The test method's default value to the '\$fixture' parameter is invalid!"
                    );

                    $fixtureConfig = [$defaultValue => $this->fixtureConfigurationDefaultFixtures[$defaultValue]];
                }

                $this->debugSection(
                    "Fixture configuration loaded from method's \$fixtures parameter: " . $this->debugString($defaultValue),
                    'Fixture Configuration'
                );

                // if the default value is null, use the configuration's fixtures
                if ($defaultValue === null) {
                    $fixtureConfig = Configuration::config()['fixtures'] ?? null;

                    $this->debugSection(
                        "Skipping class config and falling back to config: " . $this->debugString($fixtureConfig),
                        'Fixture Configuration'
                    );

                    // otherwise, make sure the configuration has not already been set by an alias above
                } elseif ($fixtureConfig === null) {
                    $fixtureConfig = $defaultValue;
                }

                // There was no method parameter. So check if there was a class level configuration
            } elseif ($fixtureConfig !== null) {
                // There _is_ a class level configuration!
                $source = self::FIXTURE_SOURCE_CLASS;

                $this->debugSection(
                    "Fixture configuration loaded from class's \$fixtureConfig field: " . $this->debugString($fixtureConfig),
                    'Fixture Configuration'
                );
            } else {
                // There is no class-level configuration. Hence use the one from the config
                $source = self::FIXTURE_SOURCE_CONFIG;

                $fixtureConfig = Configuration::config()['fixtures'] ?? null;

                $this->debugSection(
                    "Fixture configuration loaded from config: " . $this->debugString($fixtureConfig),
                    'Fixture Configuration'
                );
            }
        } catch (ReflectionException $e) {
            throw new InvalidConfigException(sprintf(
                "%s::%s: There was an error determining the test method configuration: %s",
                static::class,
                $this->getName(),
                $e->getMessage()
            ), 1, $e);
        }

        switch (true) {
            case $fixtureConfig === false:
                $this->debugSection($source . "=FALSE: Skipp loading fixtures ...", 'Fixture Configuration');
                return null;

            case $fixtureConfig === true:
                $fixtureConfig = Configuration::config()['fixtures'] ?? null;

                if ($fixtureConfig === true || empty($fixtureConfig)) {
                    if ($fixtureConfig === true) {
                        $reason = 'true';
                    } elseif ($fixtureConfig === false) {
                        $reason = 'disabled';
                    } elseif ($fixtureConfig === []) {
                        $reason = 'empty fixtures';
                    } elseif (empty($fixtureConfig)) {
                        $reason = 'no fixtures';
                    } else {
                        $reason = 'UNKNOWN';
                    }

                    $fixtureConfig = ['default'];

                    $this->debugSection(
                        sprintf("%s=TRUE: Falling back to 'default' fixture set (%s in config)", $source, $reason),
                        'Fixture Configuration'
                    );
                } else {
                    $this->debugSection(
                        $source . "=TRUE: Loading the fixtures from the config ...",
                        'Fixture Configuration'
                    );
                }
                break;

            case $fixtureConfig === 'default':
                $this->debugSection($source . "='default': Loading 'default' fixture set ...", 'Fixture Configuration');
                $fixtureConfig = [$fixtureConfig];
                break;

            case(empty($fixtureConfig)):
                if ($fixtureConfig === null) {
                    $this->debugSection(
                        $source . "=NULL: no fixtures in config, skipping ...",
                        'Fixture Configuration'
                    );
                } else {
                    $this->debugSection($source . "=EMPTY: Not loading any fixtures ...", 'Fixture Configuration');
                }
                return null;

            case !is_iterable($fixtureConfig):
                switch ($source) {
                    case self::FIXTURE_SOURCE_PARAM:
                        $source = $this->getName() . '($fixture)';
                        break;

                    case self::FIXTURE_SOURCE_CONFIG:
                        $source = Configuration::class . '::config()["fixtures"]';
                        break;

                    case self::FIXTURE_SOURCE_CLASS:
                        $source = static::class . '::$fixtureConfig';
                        break;
                }

                throw new InvalidConfigTypeException(
                    $source,
                    [null, 'bool', 'array', Traversable::class, $this->fixtureAliases],
                    $fixtureConfig
                );

            case $source === self::FIXTURE_SOURCE_CONFIG:
                $this->debugSection($source . ": Loading the fixtures from the config ...", 'Fixture Configuration');
                break;

            default:
                $this->debugSection($source . "=CUSTOM: Loading the provided fixtures ...", 'Fixture Configuration');
        }

        if (empty($fixtureConfig)) {
            $this->debugSection("No fixtures found.", 'Fixture Configuration');

            return null;
        }

        return $fixtureConfig;
    }

    /**
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _fixtures_prepare(): bool
    {
        // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        try {
            /**
             * If the value has already been initialized, this test yields true, and we can skip the rest.
             * Otherwise, it's now time to do the initialization
             *
             * @noinspection PhpConditionAlreadyCheckedInspection
             */
            if ($this->fixtureConfigurationDefaultFixtures !== null) {
                return false;
            }
        } catch (\Error $e) {
            // tried to access an un-initialized variable
        }

        /**
         * Caches the `[$name => $className]` pairs from the getDefaultFixtures()
         *
         * @see self::$fixtureConfigurationDefaultFixtures
         */
        $defaultFixtures = static::getDefaultFixtures();
        $this->fixtureConfigurationDefaultFixtures = array_combine(
            array_keys($defaultFixtures),
            array_column($defaultFixtures, 'class')
        );

        /**
         * Caches the `(string)'default', (string)$name1, (string)$name2, ...` string for error messages
         *
         * @see self::$fixtureAliases
         */
        $defaultFixtures = implode(', (string)', array_keys($this->fixtureConfigurationDefaultFixtures));
        $this->fixtureAliases = $defaultFixtures ? "(string) 'default', (string) " . $defaultFixtures : "(string) 'default'";

        return true;
    }

    public function isFixtureAlias($defaultValue, ?bool $includeDefault = false, ?string $throeError = null): bool
    {
        if (!is_string($defaultValue)) {
            if ($throeError) {
                throw new InvalidArgumentTypeException(sprintf(
                    "%s::%s: %s It must be one of null, bool, %s, or array - %s given. See %s::%s for more information.",
                    static::class,
                    $this->getName(),
                    $throeError,
                    $this->fixtureAliases,
                    get_debug_type($defaultValue),
                    static::class,
                    '$fixtureConfig'
                ));
            }

            return false;
        }

        if ($includeDefault && $defaultValue === 'default') {
            return true;
        }

        if (array_key_exists($defaultValue, $this->fixtureConfigurationDefaultFixtures)) {
            return true;
        }

        if ($throeError) {
            throw new InvalidArgumentValueException(sprintf(
                "%s::%s: %s It must be one of null, bool, %s, or array - '%s' given. See %s::%s for more information.",
                static::class,
                $this->getName(),
                $throeError,
                $this->fixtureAliases,
                $defaultValue,
                static::class,
                '$fixtureConfig'
            ));
        }

        return false;
    }

    protected static function getDefaultFixtures(): array
    {
        return [
            'user' => ['class' => UserFullFixture::class],
            'url_oembed' => ['class' => UrlOembedFixture::class],
            'group_permission' => ['class' => GroupPermissionFixture::class],
            'contentcontainer' => ['class' => ContentContainerFixture::class],
            'settings' => ['class' => SettingFixture::class],
            'space' => ['class' => SpaceFixture::class],
            'space_membership' => ['class' => SpaceMembershipFixture::class],
            'content' => ['class' => ContentFixture::class],
            'notification' => ['class' => NotificationFixture::class],
            'file' => ['class' => FileFixture::class],
            'file_history' => ['class' => FileHistoryFixture::class],
            'activity' => ['class' => ActivityFixture::class],
            'friendship' => ['class' => FriendshipFixture::class],
            'live' => ['class' => LiveFixture::class],
        ];
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
