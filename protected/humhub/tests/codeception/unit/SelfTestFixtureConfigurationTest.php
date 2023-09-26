<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\exceptions\InvalidConfigTypeException;
use humhub\modules\file\tests\codeception\fixtures\FileFixture;
use humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFixture;
use ReflectionClass;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidConfigException;

/**
 * Self-test for the $fixtures parameter of test methods
 *
 * **The things that are tested with this class happen _before_ the test method is even called!!!**
 *
 * ----------
 *
 * The main magic is happening in the `static::_fixtures()` method. Both in this test class for setting up the
 * configuration. As well as in the parent's method, where the configuration is evaluated.
 *
 * IMPORTANT: The test method's name defines the configuration
 * ---
 *
 * If the Name contains `Global` or `Local`, it will define the configuration.
 * Possible Options can be found in (this class') `self::_fixtures()` method.
 *
 * So the test method will only assert things, without actually doing anything first.
 * An exception to this is where invalid parameter configurations are tested.
 *
 * @since 1.15
 * @see HumHubDbTestCase::$fixtureConfig
 * @see self::_fixtures()
 * @see HumHubDbTestCase::_fixtures()
 */
final class SelfTestFixtureConfigurationTest extends HumHubDbTestCase
{
    protected const FIXTURE_ADDING_DEFAULT = '[Fixtures] Adding the default fixtures to the mix ...';
    protected const FIXTURE_CONFIGURATION_CONFIG = '[Fixture Configuration] %s: Loading the fixtures from the config ...';
    protected const FIXTURE_CONFIGURATION_CONFIG_SET = '[Fixture Configuration] %s=%s: Loading the fixtures from the config ...';
    protected const FIXTURE_CONFIGURATION_DEFAULT = "[Fixture Configuration] %s='default': Loading 'default' fixture set ...";
    protected const FIXTURE_CONFIGURATION_CUSTOM = '[Fixture Configuration] %s=CUSTOM: Loading the provided fixtures ...';
    protected const FIXTURE_CONFIGURATION_EMPTY = '[Fixture Configuration] %s=EMPTY: Not loading any fixtures ...';
    protected const FIXTURE_CONFIGURATION_FALSE = '[Fixture Configuration] %s=FALSE: Skipp loading fixtures ...';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM = '[Fixture Configuration] Fixture configuration loaded from method\'s $fixtures parameter: ';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_ARRAY = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . '[]';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_DEFAULT_SET = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . '{"file":"humhub\modules\file\tests\codeception\fixtures\FileFixture"}';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_DEFAULT = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . 'default';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_DEFAULT_IN_ARRAY = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . '["default"]';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_FALSE = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . 'false';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_NULL = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . 'NULL';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_TRUE = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . 'true';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_VALUE = self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM . '%s';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS = '[Fixture Configuration] Fixture configuration loaded from class\'s $fixtureConfig field: ';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_ARRAY = self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS . '[]';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_DEFAULT = self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS . '{"file":"humhub\modules\file\tests\codeception\fixtures\FileFixture"}';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_FALSE = self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS . 'false';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_NULL = self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS . 'NULL';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_TRUE = self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS . 'true';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_VALUE = self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS . '%s';
    protected const FIXTURE_CONFIGURATION_NULL_FALLBACK_CONFIG = '[Fixture Configuration] Skipping class config and falling back to config: %s';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION = '[Fixture Configuration] Fixture configuration loaded from config: ';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_ARRAY = self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION . '[]';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_DEFAULT = self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION . '{"file":"humhub\modules\file\tests\codeception\fixtures\FileFixture"}';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_FALSE = self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION . 'false';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_NULL = self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION . 'NULL';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_TRUE = self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION . 'true';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_VALUE = self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION . '%s';
    protected const FIXTURE_CONFIGURATION_LOADED_FROM_FIELD_FALSE = '[Fixture Configuration] Fixture configuration loaded from class\'s $fixtureConfig field: false';
    protected const FIXTURE_CONFIGURATION_NO_FIXTURES_FOUND = '[Fixture Configuration] No fixtures found.';
    protected const FIXTURE_CONFIGURATION_NULL = '[Fixture Configuration] %s=NULL: no fixtures in config, skipping ...';
    protected const FIXTURE_CONFIGURATION_TRUE_FALLBACK = '[Fixture Configuration] %s=TRUE: Falling back to \'default\' fixture set ';
    protected const FIXTURE_CONFIGURATION_TRUE_FALLBACK_FROM_TRUE = self::FIXTURE_CONFIGURATION_TRUE_FALLBACK . '(true in config)';
    protected const FIXTURE_CONFIGURATION_TRUE_FALLBACK_NO_CONFIG = self::FIXTURE_CONFIGURATION_TRUE_FALLBACK . '(no fixtures in config)';
    protected const FIXTURE_CONFIGURATION_TRUE_FALLBACK_DISABLED_CONFIG = self::FIXTURE_CONFIGURATION_TRUE_FALLBACK . '(disabled in config)';
    protected const FIXTURE_CONFIGURATION_TRUE_FALLBACK_EMPTY_CONFIG = self::FIXTURE_CONFIGURATION_TRUE_FALLBACK . '(empty fixtures in config)';

    private const DEFAULT_FIXTURES = [
        'file' => ['class' => FileFixture::class],
    ];

    /**
     * @var array Holds the fixture configuration resulting for the current test
     * @noinspection PhpMissingFieldTypeInspection
     */
    protected $currentFixtureConfig;

    /**
     * @var null|array Holds the fixture configuration from the configuration
     */
    protected static array $originalInternalConfig;
    protected static ?bool $fixtureConfigurationNull = null;
    protected static bool $fixtureConfigurationFalse = false;
    protected static bool $fixtureConfigurationTrue = true;
    protected static array $fixtureConfigurationEmpty = [];
    protected static string $fixtureConfigurationDefault = 'default';
    protected static array $fixtureConfigurationDefaultInArray = ['default'];
    protected static array $fixtureConfigurationDefaultAndUser = ['default', 'user' => UserFixture::class];

    /**
     * @var mixed|static|null|bool|string|array Holds the default value of the current method's $fixture parameter, or
     *     $this if not set
     */
    protected $fixtureConfigMethodArgument;
    protected ?array $testDebugLog = null;
    private ReflectionProperty $config;
    private array $originalEvaluatedConfig;
    protected static array $testDebugLogAll = [];

    private function setFixture($fixture = null)
    {
        // copy the original config
        $config = $this->originalEvaluatedConfig;

        if (func_num_args() === 0) {
            unset($config['fixtures']);
        } else {
            $config['fixtures'] = $fixture;
        }

        $this->config->setValue($config);
    }

    public static function tearDownAfterClass(...$args): void
    {
        if (count(self::$testDebugLogAll)) {
            codecept_debug("\n\nLog Summary\n");

            foreach (self::$testDebugLogAll as $name => $logs) {
                codecept_debug("\n$name");
                foreach ($logs as $log) {
                    codecept_debug(sprintf("- %s", str_replace('[Fixture Configuration] ', '', $log)));
                }
            }

            codecept_debug("\n");
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->getName() === 'testRequiredParameter') {
            $this->setDependencyInput([$this]);
        }
    }

    protected function tearDown(): void
    {
        $this->reset();

        // restore configuration
        $this->config->setValue(self::$originalInternalConfig);

        // unlock it again
        Configuration::$lock = false;

        parent::tearDown();
    }

    public function reset(?string $name = null): void
    {
        if ($this->testDebugLog !== null) {
            self::$testDebugLogAll[$name ?? $this->getName()] = $this->testDebugLog;
            $this->testDebugLog = null;
        }

        $this->fixtureConfigMethodArgument = null;
        $this->currentFixtureConfig = null;
    }

    /**
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _fixtures(): array
    {
        // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        $this->_fixtures_prepare();

        $name = $this->getName();

        if (str_contains($name, 'Global')) {
            switch (true) {
                case str_contains($name, 'GlobalOriginal'):
                    $this->config->setValue($this->originalEvaluatedConfig);
                    break;

                case str_contains($name, 'GlobalNull'):
                    $this->setFixture(self::$fixtureConfigurationNull);
                    break;

                case str_contains($name, 'GlobalFalse'):
                    $this->setFixture(self::$fixtureConfigurationFalse);
                    break;

                case str_contains($name, 'GlobalTrue'):
                    $this->setFixture(self::$fixtureConfigurationTrue);
                    break;

                case str_contains($name, 'GlobalEmpty'):
                    $this->setFixture(self::$fixtureConfigurationEmpty);
                    break;

                case str_contains($name, 'GlobalDefaultFixtures'):
                    $this->setFixture($this->fixtureConfigurationDefaultFixtures);
                    break;

                case str_contains($name, 'GlobalDefaultInArray'):
                    $this->setFixture(self::$fixtureConfigurationDefaultInArray);
                    break;

                case str_contains($name, 'GlobalDefault'):
                    $this->setFixture(self::$fixtureConfigurationDefault);
                    break;

                default:
                    // unset $config('fixtures']
                    $this->setFixture();
            }
        } else {
            $this->setFixture();
        }

        $this->fixtureConfig = null;

        if (str_contains($name, 'Local')) {
            switch (true) {
                case str_contains($name, 'LocalFalse'):
                    $this->fixtureConfig = self::$fixtureConfigurationFalse;
                    break;

                case str_contains($name, 'LocalTrue'):
                    $this->fixtureConfig = self::$fixtureConfigurationTrue;
                    break;

                case str_contains($name, 'LocalEmpty'):
                    $this->fixtureConfig = self::$fixtureConfigurationEmpty;
                    break;

                case str_contains($name, 'LocalDefaultFixtures'):
                    $this->fixtureConfig = $this->fixtureConfigurationDefaultFixtures;
                    break;

                case str_contains($name, 'LocalDefaultInArray'):
                    $this->fixtureConfig = self::$fixtureConfigurationDefaultInArray;
                    break;

                case str_contains($name, 'LocalDefault'):
                    $this->fixtureConfig = self::$fixtureConfigurationDefault;
                    break;
            }
        }

        $this->testDebugLog = [];

        // now get the fixtures configured and store the result
        return $this->currentFixtureConfig = parent::_fixtures();
    }

    /**
     * @throws InvalidConfigException
     * @throws ConfigurationException
     * @throws InvalidConfigTypeException
     * @inerhitdoc
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _fixtures_evaluate_setting(&$defaultValue = null, object $class = null, string $method = null)
    {
        // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        // $this cannot be used as a method's default parameter. Hence, we can use it to distinguish if the parameter has been set
        $defaultValue = $this;

        $this->testDebugLog = [];

        $fixtureConfig = parent::_fixtures_evaluate_setting($defaultValue, $class, $method);

        $this->fixtureConfigMethodArgument = $defaultValue;

        return $fixtureConfig;
    }

    /**
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _fixtures_prepare(): bool
    {
        // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

        if (!parent::_fixtures_prepare()) {
            return false;
        }

        $rc = new ReflectionClass(Configuration::class);
        $this->config = $rc->getProperty('config');
        $this->config->setAccessible(true);

        // get the full config
        $this->originalEvaluatedConfig = Configuration::config();

        // now store the actual config as stored in the way it was
        self::$originalInternalConfig = $this->config->getValue();

        // now save the evaluated config as the internal state
        $this->config->setValue($this->originalEvaluatedConfig);

        // now lock it
        Configuration::$lock = true;

        return true;
    }


    /**
     * Reduce default data set
     */
    protected static function getDefaultFixtures(): array
    {
        return self::DEFAULT_FIXTURES;
    }

    protected function debug($message): string
    {
        $this->testDebugLog[] = $message = parent::debug($message);

        return $message;
    }

    public function assertDebug(array $expected, $message = ''): void
    {
        self::assertEquals($expected, $this->testDebugLog, $message);
    }

    public function assertDefaultFixtures($message = ''): void
    {
        $this->assertFixtureEquals(self::DEFAULT_FIXTURES, $message);
    }

    public function assertEmptyFixtures($message = ''): void
    {
        $this->assertFixtureEquals([], $message);
    }

    public function assertFixtureEquals($expected, $message = ''): void
    {
        self::assertEquals($expected, $this->currentFixtureConfig, $message);
    }

    public function assertNoFixtureParameter($message = '')
    {
        $this->assertFixtureParameter($this, $message);
    }

    public function assertFixtureParameter($expected, $message = '')
    {
        $this->assertFixtureParameterWithActualValue($expected, $this, $message);
    }

    public function assertFixtureParameterWithActualValue($expected, $parameter, $message = '')
    {
        $actual = $this->fixtureConfigMethodArgument;

        if (is_object($actual)) {
            $actual = sprintf("%s(%s)", get_class($actual), spl_object_id($actual));
        }

        if (is_object($expected)) {
            $expected = sprintf("%s(%s)", get_class($expected), spl_object_id($expected));
        }

        self::assertEquals($expected, $actual, $message);

        if ($parameter !== $this) {
            self::assertEquals($expected, $parameter, $message);
        }
    }

    public function testSelfSetup()
    {
        self::assertEquals(self::DEFAULT_FIXTURES, self::getDefaultFixtures());
        self::assertNotEquals(self::DEFAULT_FIXTURES, parent::getDefaultFixtures());

        self::assertEquals(
            array_combine(
                array_keys(self::DEFAULT_FIXTURES),
                array_column(self::DEFAULT_FIXTURES, 'class')
            ),
            $this->fixtureConfigurationDefaultFixtures,
            '$this->fixtureConfigurationDefaultFixtures is improperly initialized.'
        );

        $defaultFixtures = implode(', (string)', array_keys($this->fixtureConfigurationDefaultFixtures));
        self::assertEquals(
            $defaultFixtures ? "(string) 'default', (string) " . $defaultFixtures : "(string) 'default'",
            $this->fixtureAliases,
            '$this->fixtureAliases is improperly initialized.'
        );
    }

    public function testFixtureConfigurationGlobalNull()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_CONFIG),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationGlobalFalse()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_FALSE,
            sprintf(self::FIXTURE_CONFIGURATION_FALSE, self::FIXTURE_SOURCE_CONFIG),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationGlobalTrue()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_TRUE_FALLBACK_FROM_TRUE, self::FIXTURE_SOURCE_CONFIG),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationGlobalEmpty()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_ARRAY,
            sprintf(self::FIXTURE_CONFIGURATION_EMPTY, self::FIXTURE_SOURCE_CONFIG),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationGlobalDefaultFixtures()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_DEFAULT,
            sprintf(self::FIXTURE_CONFIGURATION_CONFIG, self::FIXTURE_SOURCE_CONFIG),
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationGlobalDefaultInArray()
    {
        $this->assertDebug([
            sprintf(self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_VALUE, '["default"]'),
            sprintf(self::FIXTURE_CONFIGURATION_CONFIG, self::FIXTURE_SOURCE_CONFIG),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationGlobalDefault()
    {
        $this->assertDebug([
            sprintf(self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_VALUE, 'default'),
            sprintf(self::FIXTURE_CONFIGURATION_DEFAULT, self::FIXTURE_SOURCE_CONFIG),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationLocalNull()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_CONFIG),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationLocalFalse()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_FIELD_FALSE,
            sprintf(self::FIXTURE_CONFIGURATION_FALSE, self::FIXTURE_SOURCE_CLASS),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationLocalFalseGlobalDefaultFixtures()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_FALSE,
            sprintf(self::FIXTURE_CONFIGURATION_FALSE, self::FIXTURE_SOURCE_CLASS),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationLocalTrue()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_TRUE_FALLBACK_NO_CONFIG, self::FIXTURE_SOURCE_CLASS),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationLocalTrueGlobalDefaultFixtures()
    {
        $this->assertDefaultFixtures();

        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_CONFIG_SET, self::FIXTURE_SOURCE_CLASS, 'TRUE'),
        ]);
    }

    public function testFixtureConfigurationLocalTrueGlobalFalse()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_TRUE_FALLBACK_DISABLED_CONFIG, self::FIXTURE_SOURCE_CLASS),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationLocalTrueGlobalEmpty()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_TRUE_FALLBACK_EMPTY_CONFIG, self::FIXTURE_SOURCE_CLASS),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationLocalEmptyGlobalDefaultFixtures()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_ARRAY,
            sprintf(self::FIXTURE_CONFIGURATION_EMPTY, self::FIXTURE_SOURCE_CLASS),
        ]);

        $this->assertEmptyFixtures();
    }

    public function testFixtureConfigurationLocalDefaultFixturesGlobalDefaultFixtures()
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_DEFAULT,
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_CLASS),
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationLocalDefaultInArrayGlobalDefaultFixtures()
    {
        $this->assertDebug([
            sprintf(self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_VALUE, '["default"]'),
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_CLASS),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testFixtureConfigurationLocalDefaultGlobalDefaultFixtures()
    {
        $this->assertDebug([
            sprintf(self::FIXTURE_CONFIGURATION_LOADED_FROM_CLASS_VALUE, 'default'),
            sprintf(self::FIXTURE_CONFIGURATION_DEFAULT, self::FIXTURE_SOURCE_CLASS),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();
    }

    public function testRequiredParameter()
    {
        $class = new class () {
            public function test($fixtures)
            {
            }
        };

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            "%s: The test method's magic parameter '%s' has no default value set. Please add a valid default value or remove the parameter. See %s::%s for more information.",
            __METHOD__,
            '$fixture',
            self::class,
            '$fixtureConfig'
        ));
        $this->_fixtures_evaluate_setting($defaultValue, $class, 'test');
    }

    public function testParameters()
    {
        $i = 1;
        $name = $this->getName();

        $class = new class () {
            public function test()
            {
            }
        };

        $this->_fixtures_evaluate_setting($defaultValue, $class, 'test');

        $this->assertNoFixtureParameter();
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_CONFIG),
        ]);

        $this->reset($name . ':' . $i++);


        $class = new class () {
            public function test($notRelevant)
            {
            }
        };

        $this->_fixtures_evaluate_setting($defaultValue, $class, 'test');

        $this->assertNoFixtureParameter();
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_CONFIGURATION_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_CONFIG),
        ]);


        $this->reset($name . ':' . $i++);
    }

    public function testInvalidMethodDoesNotExist()
    {

        $class = new class () {
            public function testSomethingElse($fixtures = false)
            {
            }
        };

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            "%s: There was an error determining the test method configuration: Method class@anonymous::test() does not exist",
            __METHOD__,
        ));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_fixtures_evaluate_setting($defaultValue, $class, 'test');
    }

    public function testInvalidParameterZero()
    {
        $class = new class () {
            public function test($fixtures = 0)
            {
            }
        };

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage(sprintf(
            "%s: The test method's default value to the '%s' parameter is invalid! It must be one of null, bool, %s, or array - int given. See %s::%s for more information.",
            __METHOD__,
            '$fixture',
            $this->fixtureAliases,
            self::class,
            '$fixtureConfig'
        ));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_fixtures_evaluate_setting($defaultValue, $class, 'test');
    }

    public function testParameterInvalidFixtureAlias()
    {
        $class = new class () {
            public function test($fixtures = 'user')
            {
            }
        };

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessage(sprintf(
            "%s: The test method's default value to the '%s' parameter is invalid! It must be one of null, bool, %s, or array - 'user' given. See %s::%s for more information.",
            __METHOD__,
            '$fixture',
            $this->fixtureAliases,
            self::class,
            '$fixtureConfig'
        ));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_fixtures_evaluate_setting($defaultValue, $class, 'test');
    }

    public function testParameterInvalidFixtureAliasInArray()
    {
        $class = new class () extends SelfTestLogAssertionsTest {
            public function getName(bool $withDataSet = true): string
            {
                return 'test';
            }

            protected static function getDefaultFixtures(): array
            {
                return SelfTestFixtureConfigurationTest::getDefaultFixtures();
            }

            public function test($fixtures = ['user'])
            {
            }
        };

        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionMessageMatches(sprintf(
            "#%s#",
            preg_quote(
                "Unknown fixture alias! It must be one of null, bool, (string) 'default', (string) file, or array - 'user' given. See ",
                '#'
            ),
        ));

        /** @noinspection PhpUnhandledExceptionInspection */
        $class->_fixtures();
    }

    public function testClassConfigNotIterable()
    {
        $class = new class () extends HumHubDbTestCase {
            protected $fixtureConfig = 15.3;

            public function getName(bool $withDataSet = true): string
            {
                return 'test';
            }

            protected static function getDefaultFixtures(): array
            {
                return SelfTestFixtureConfigurationTest::getDefaultFixtures();
            }

            public function test()
            {
            }
        };

        $this->expectException(InvalidConfigTypeException::class);
        $this->expectExceptionMessageMatches(sprintf(
            "#%s#",
            preg_quote(
                "::\$fixtureConfig' of configuration passed to tests\codeception\_support\HumHubDbTestCase::_fixtures_evaluate_setting must be one of the following type NULL, bool, array, Traversable, (string) 'default', (string) file - float given.",
                '#'
            ),
        ));

        /** @noinspection PhpUnhandledExceptionInspection */
        $class->_fixtures();
    }

    public function testParameterNull($fixtures = null)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL_FALLBACK_CONFIG, 'NULL'),
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertEmptyFixtures();

        $this->assertFixtureParameterWithActualValue(null, $fixtures);
    }

    public function testParameterNullLocalTrue($fixtures = null)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL_FALLBACK_CONFIG, 'NULL'),
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertEmptyFixtures();

        $this->assertFixtureParameterWithActualValue(null, $fixtures);
    }

    public function testParameterNullLocalFalse($fixtures = null)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL_FALLBACK_CONFIG, 'NULL'),
            sprintf(self::FIXTURE_CONFIGURATION_NULL, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertEmptyFixtures();

        $this->assertFixtureParameterWithActualValue(null, $fixtures);
    }

    public function testParameterNullLocalFalseGlobalDefault($fixtures = null)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_NULL,
            sprintf(self::FIXTURE_CONFIGURATION_NULL_FALLBACK_CONFIG, 'default'),
            sprintf(self::FIXTURE_CONFIGURATION_DEFAULT, self::FIXTURE_SOURCE_PARAM),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue(null, $fixtures);
    }

    public function testParameterFalse($fixtures = false)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_FALSE,
            sprintf(self::FIXTURE_CONFIGURATION_FALSE, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertEmptyFixtures();

        $this->assertFixtureParameterWithActualValue(false, $fixtures);
    }

    public function testParameterTrue($fixtures = true)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_TRUE_FALLBACK_NO_CONFIG, self::FIXTURE_SOURCE_PARAM),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue(true, $fixtures);
    }

    public function testParameterTrueLocalFalse($fixtures = true)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_TRUE_FALLBACK_NO_CONFIG, self::FIXTURE_SOURCE_PARAM),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue(true, $fixtures);
    }

    public function testParameterTrueGlobalDefaultFixtures($fixtures = true)
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_TRUE,
            sprintf(self::FIXTURE_CONFIGURATION_CONFIG_SET, self::FIXTURE_SOURCE_PARAM, 'TRUE'),
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue(true, $fixtures);
    }

    public function testParameterEmpty($fixtures = [])
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_ARRAY,
            sprintf(self::FIXTURE_CONFIGURATION_EMPTY, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertEmptyFixtures();

        $this->assertFixtureParameterWithActualValue([], $fixtures);
    }

    public function testParameterDefault($fixtures = 'default')
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_DEFAULT,
            sprintf(self::FIXTURE_CONFIGURATION_DEFAULT, self::FIXTURE_SOURCE_PARAM),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue('default', $fixtures);
    }

    public function testParameterDefaultInArray($fixtures = ['default'])
    {
        $this->assertDebug([
            self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_DEFAULT_IN_ARRAY,
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_PARAM),
            self::FIXTURE_ADDING_DEFAULT,
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue(['default'], $fixtures);
    }

    public function testParameterFileAlias($fixtures = 'file')
    {
        $this->assertDebug([
            sprintf(
                self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_VALUE,
                'file'
            ),
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue('file', $fixtures);
    }

    public function testParameterFileAliasInArray($fixtures = ['file'])
    {
        $this->assertDebug([
            sprintf(
                self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_VALUE,
                '["file"]'
            ),
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertDefaultFixtures();

        $this->assertFixtureParameterWithActualValue(['file'], $fixtures);
    }

    public function testParameterFileHistory($fixtures = ['file_history' => FileHistoryFixture::class])
    {
        $this->assertDebug([
            sprintf(
                self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_VALUE,
                '{"file_history":"humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture"}'
            ),
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertFixtureEquals(['file_history' => ['class' => FileHistoryFixture::class]]);

        $this->assertFixtureParameterWithActualValue(['file_history' => FileHistoryFixture::class], $fixtures);
    }

    public function testParameterFileAndFileHistory($fixtures = ['file', 'file_history' => FileHistoryFixture::class])
    {
        $this->assertDebug([
            sprintf(
                self::FIXTURE_CONFIGURATION_LOADED_FROM_PARAM_VALUE,
                '{"0":"file","file_history":"humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture"}'
            ),
            sprintf(self::FIXTURE_CONFIGURATION_CUSTOM, self::FIXTURE_SOURCE_PARAM),
        ]);

        $this->assertFixtureEquals([
            'file' => ['class' => FileFixture::class],
            'file_history' => ['class' => FileHistoryFixture::class]
        ]);

        $this->assertFixtureParameterWithActualValue(['file', 'file_history' => FileHistoryFixture::class], $fixtures);
    }
}
