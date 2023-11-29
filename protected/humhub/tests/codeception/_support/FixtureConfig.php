<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use Attribute;
use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\modules\activity\tests\codeception\fixtures\ActivityFixture;
use humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture;
use humhub\modules\content\tests\codeception\fixtures\ContentFixture;
use humhub\modules\file\tests\codeception\fixtures\FileFixture;
use humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture;
use humhub\modules\friendship\tests\codeception\fixtures\FriendshipFixture;
use humhub\modules\live\tests\codeception\fixtures\LiveFixture;
use humhub\modules\notification\tests\codeception\fixtures\NotificationFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture;
use humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use humhub\tests\codeception\fixtures\SettingFixture;
use humhub\tests\codeception\fixtures\UrlOembedFixture;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;

/**
 * This Annotation Class can be used to configure the fixtures for unit/class level and test/method level.
 *
 * While evaluation the setting for a specific test, the following priority applies:
 *   1. Test method's first annotation deriving from `FixtureConfig`
 *   2. Class's first annotation deriving from `FixtureConfig`
 *   3. Fixture provided by the configuration.
 *
 *
 * ## Configuration Options:
 *  - `null`: the setting from the configuration is used
 *  - `[]`: If configured, the fixtures from the configuration will be used, or the `default` set otherwise.
 *     This differs from `null` insofar, as it will fall back to `default` if there is no fixture specified in the
 *     configuration
 *  - `['default']`: the result from `static::getDefaultFixtures()` is used
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
 * @see HumHubDbTestCase::_fixtures()
 * @see Yii2
 * @see Yii2::loadFixtures
 * @see Yii2::haveFixtures
 * @see FixtureEmpty for disabling any fixtures
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FixtureConfig
{
    use DebugTrait;

    /** @internal */
    protected const FIXTURE_SOURCE_CONFIG = 'CONFIG';
    /** @internal */
    protected const FIXTURE_SOURCE_METHOD = 'METHOD';
    /** @internal */
    protected const FIXTURE_SOURCE_CLASS = 'CLASS';

    protected ?array $config;
    protected array $fixtures;
    protected string $source = self::FIXTURE_SOURCE_CLASS;

    /**
     * Caches the `[$name => $className]` pairs from the getDefaultFixtures()
     *
     * @internal
     */
    protected array $fixtureAliasArray;

    /**
     * Caches the `(string)'default', (string)$name1, (string)$name2, ...` string for error messages
     *
     * @internal
     */
    protected string $fixtureAliasString;

    /**
     * @var array|string[]
     */
    protected array $test;
    protected ?bool $useConfig;
    protected ?bool $useDefault;

    protected ?array $defaultFixtures = null;

    /**
     * @param array|null $fixtures Configuration options
     * @param bool|null $useDefault
     * @param bool|null $useConfig
     *
     * @see FixtureConfig
     */
    public function __construct(?array $fixtures = null, ?bool $useDefault = false, ?bool $useConfig = true)
    {
        $this->config = $fixtures;
        $this->useDefault = $useDefault;
        $this->useConfig = $useConfig;
    }


    /**
     * @param HumHubDbTestCase $unit Test suite/class
     * @param string $test Test name
     * @param string $source Where the attribute was defined: static::FIXTURE_SOURCE_CLASS or
     *     static::FIXTURE_SOURCE_METHOD
     *
     * @return $this
     */
    protected function initialize(HumHubDbTestCase $unit, string $test, string $source): self
    {
        $this->test = [$unit, $test];
        $this->source = $source;

        $defaultFixtures = $this->getDefaultFixtures();

        $this->fixtureAliasArray = array_combine(
            array_keys($defaultFixtures),
            array_column($defaultFixtures, 'class')
        );

        $defaultFixtures = implode(', (string)', array_keys($this->fixtureAliasArray));
        $this->fixtureAliasString = $defaultFixtures ? "(string) 'default', (string) " . $defaultFixtures : "(string) 'default'";

        return $this->evaluate();
    }


    protected function evaluate(): self
    {

        if ($this->source === self::FIXTURE_SOURCE_METHOD) {
            $this->debugSection(
                "Fixture configuration loaded from method's \$fixtures parameter: " . $this->debugString($this->config),
                'Fixture Configuration'
            );
        }

        if (isset($this->fixtures)) {
            return $this;
        }

        /** @var array[]|null $fixtures */
        $fixtures = null;

        if ($this->useConfig) {
            $this->debugSection(
                $this->source . ": Loading the fixtures from the config ...",
                'Fixture Configuration'
            );

            $this->source = self::FIXTURE_SOURCE_CONFIG;
            $fixtures = $this->getFixturesFromConfig();

            if ($fixtures === null && $this->useDefault) {
                $this->debugSection(
                    $this->source . ": Falling back to 'default' fixture set (nothing found in config)",
                    'Fixture Configuration'
                );
            }
        }

        if ($this->useDefault && $fixtures === null) {
            $this->debugSection(
                sprintf("%s: Loading the default fixtures for class %s ...", $this->source, static::class),
                'Fixture Configuration'
            );

            $fixtures = $this->getDefaultFixtures();
        }

        // if the default value is null, use the configuration's fixtures
        if ($this->config === null) {
            $this->fixtures = $fixtures ?? [];

            return $this;
        }

        $fixtures ??= [];

        foreach ($this->config as $fixtureTable => $fixtureClass) {
            switch (true) {
                // check if the default fixture set is requested
                case $fixtureClass === 'default':
                    $this->debugSection('Adding the default fixtures to the mix ...', 'Fixtures');
                    $fixtures = array_merge($fixtures, $this->getDefaultFixtures());
                    break;

                // check for an alias and get its definition from the default fixture set
                case is_int($fixtureTable):
                    $this->isFixtureAlias($fixtureClass, null, "Unknown fixture alias!");
                    $fixtures[$fixtureClass] = ['class' => $this->fixtureAliasArray[$fixtureClass]];
                    break;

                // otherwise, assume a `[$fixtureTable => $fixtureClass]`-pair
                default:
                    $fixtures[$fixtureTable] = ['class' => $fixtureClass];
            }
        }

        $this->fixtures = $fixtures;

        return $this;
    }

    public function getDefaultFixtures(): array
    {
        return $this->defaultFixtures ??= [
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

    public function isFixtureAlias($defaultValue, ?bool $includeDefault = false, ?string $throeError = null): bool
    {

        if (!is_string($defaultValue)) {
            if ($throeError) {
                throw new InvalidArgumentTypeException(sprintf(
                    "%s::%s: %s It must be one of null, bool, %s, or array - %s given. See %s::%s for more information.",
                    static::class,
                    $this->getName(),
                    $throeError,
                    $this->fixtureAliasString,
                    get_debug_type($defaultValue),
                    HumHubDbTestCase::class,
                    '$fixtureConfig'
                ));
            }

            return false;
        }

        if ($includeDefault && $defaultValue === 'default') {
            return true;
        }

        if (array_key_exists($defaultValue, $this->fixtureAliasArray)) {
            return true;
        }

        if ($throeError) {
            throw new InvalidArgumentValueException(sprintf(
                "%s::%s: %s It must be one of null, bool, %s, or array - '%s' given. See %s::%s for more information.",
                static::class,
                $this->getName(),
                $throeError,
                $this->fixtureAliasString,
                $defaultValue,
                static::class,
                '$fixtureConfig'
            ));
        }

        return false;
    }

    /**
     * @return array[]|null
     * @throws ConfigurationException
     */
    protected function getFixturesFromConfig(): ?array
    {
        $config = Configuration::config();

        return $config['fixtures'] ?? null;
    }

    public function getFixtures(): array
    {
        return $this->fixtures;
    }

    public function getName(): string
    {
        return $this->test[1];
    }


    /**
     * @param HumHubDbTestCase $unit Instance of the test unit
     * @param string $test Name of the test that is going to be run
     *
     * @return static
     */
    public static function create(HumHubDbTestCase $unit, string $test): self
    {
        return self::findFixtureAttribute($unit, $test, $source)
            ->initialize($unit, $test, $source);
    }

    /**
     * @param HumHubDbTestCase $unit
     * @param string $test
     * @param string|null $source
     *
     * @return FixtureConfig
     */
    public static function findFixtureAttribute(HumHubDbTestCase $unit, string $test, ?string &$source): self
    {
        $fixtureAttribute = null;

        $rc = new ReflectionObject($unit);

        /**
         * @param $rm ReflectionObject|ReflectionClass|ReflectionMethod|null
         *
         * @return static|null
         */
        $getAttribute = static function ($attributeSource): ?self {
            if ($attributeSource === null) {
                return null;
            }

            $ra = $attributeSource->getAttributes(self::class, ReflectionAttribute::IS_INSTANCEOF);

            $fixtureConfig = reset($ra);
            return $fixtureConfig === false ? null : $fixtureConfig->newInstance();
        };

        do {
            try {
                $rm = $rc->getMethod($test);
                $fixtureAttribute = $getAttribute($rm);
            } catch (ReflectionException $e) {
                $rm = null;
            }

            if ($fixtureAttribute !== null) {
                $source = self::FIXTURE_SOURCE_METHOD;
            } else {
                $source = self::FIXTURE_SOURCE_CLASS;
                $fixtureAttribute = $getAttribute($rc);
            }
        } while (
            !$fixtureAttribute instanceof self
            && false !== ($rc = $rc->getParentClass())
        );

        return $fixtureAttribute;
    }
}
