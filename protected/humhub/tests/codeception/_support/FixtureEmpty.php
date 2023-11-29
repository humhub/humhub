<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FixtureEmpty extends FixtureConfig
{
    protected array $fixtures = [];
    protected array $fixtureAliasArray = [];
    protected string $fixtureAliasString = '';

    public function __construct()
    {
        parent::__construct(null, false, false);
    }

    protected function initialize(HumHubDbTestCase $unit, string $test, string $source): FixtureConfig
    {
        $this->test = [$unit, $test];
        $this->source = $source;

        return $this;
    }
}
