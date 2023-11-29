<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use Attribute;

/** Use fixture as defined in configuration */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FixtureDefault extends FixtureConfig
{
    protected string $source = self::FIXTURE_SOURCE_CONFIG;

    /**
     *
     * @see FixtureConfig
     */
    public function __construct()
    {
        parent::__construct(null, true);
    }

    protected function initialize(HumHubDbTestCase $unit, string $test, string $source): FixtureConfig
    {
        $this->test = [$unit, $test];
        $this->source = $source;
        $this->fixtures = $this->getDefaultFixtures();

        return $this->evaluate();
    }

    public function getDefaultFixtures(): array
    {
        return $this->getFixturesFromConfig() ?? [];
    }
}
