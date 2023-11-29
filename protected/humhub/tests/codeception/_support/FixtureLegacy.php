<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use Attribute;

/**
 * Class for backward-compatibility with deprecated fixture configuration.
 *
 * @since v1.16
 * @deprecated since v1.16; Use FixtureConfig instead.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class FixtureLegacy extends FixtureDefault
{
    /**
     *
     * @see          FixtureConfig
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
        FixtureConfig::__construct();
    }

    /**
     * @inheritDoc
     *
     * @param array|null $classConfig HumHubDbTestCase::$fixtureConfig
     * @param array|null $defaultFixtures HumHubDbTestCase::getDefaultFixtures()
     */
    protected function initialize(
        HumHubDbTestCase $unit,
        string $test,
        string $source,
        ?array $classConfig = null,
        ?array $defaultFixtures = null
    ): self {
        $this->config = $classConfig ?? [];
        $this->defaultFixtures = $defaultFixtures;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return FixtureConfig::initialize($unit, $test, $source);
    }

    public function getDefaultFixtures(): array
    {
        return FixtureConfig::getDefaultFixtures();
    }


    /**
     * @param HumHubDbTestCase $unit Instance of the test unit
     * @param string $test Name of the test that is going to be run
     * @param array|null $classConfig Deprecated. Use annotation parameters instead
     * @param array|null $defaultFixtures Deprecated. Use specific annotation overriding the
     *     `static::getDefaultFixtures()` method
     *
     * @return static
     * @deprecated since v1.16; Please use FixtureConfig instead.
     */
    public static function create(HumHubDbTestCase $unit, string $test, ?array $classConfig = null, ?array $defaultFixtures = null): parent
    {

        if (PHP_MAJOR_VERSION < 8) {
            $fixtureConfig = new FixtureLegacy();
            $source = $classConfig === null ? FixtureConfig::FIXTURE_SOURCE_CONFIG : FixtureConfig::FIXTURE_SOURCE_CLASS;
        } else {
            $fixtureConfig = parent::findFixtureAttribute($unit, $test, $source);
        }

        return $fixtureConfig instanceof self
            ? $fixtureConfig->initialize($unit, $test, $source, $classConfig, $defaultFixtures)
            : $fixtureConfig->initialize($unit, $test, $source);
    }
}
