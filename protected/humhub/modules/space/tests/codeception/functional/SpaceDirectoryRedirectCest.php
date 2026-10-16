<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;
use PHPUnit\Framework\Assert;

/**
 * Links to the space directory with the parameters of its filters before 1.20 (`keyword`,
 * `connection`, the old `sort` values) land on the same selection under the parameters of
 * `GET /api/v2/space` the directory uses now.
 */
class SpaceDirectoryRedirectCest
{
    /**
     * @return array the query parameters of the page the request ended on
     */
    private function landedOn(FunctionalTester $I): array
    {
        parse_str((string)parse_url($I->grabFromCurrentUrl(), PHP_URL_QUERY), $params);
        unset($params['r']);
        ksort($params);

        return $params;
    }

    public function testRedirectsLegacyParameters(FunctionalTester $I)
    {
        $I->wantTo('follow an old directory link to the same selection');
        $I->amUser1();

        $I->amOnRoute('/space/spaces', ['keyword' => 'marketing', 'connection' => 'follow', 'sort' => 'newer', 'foo' => 'bar']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['foo' => 'bar', 'q' => 'marketing', 'scope' => 'following', 'sort' => 'newest'], $this->landedOn($I));

        $I->amOnRoute('/space/spaces', ['connection' => 'member', 'sort' => 'older']);
        Assert::assertSame(['scope' => 'member', 'sort' => 'oldest'], $this->landedOn($I));

        $I->amOnRoute('/space/spaces', ['connection' => 'none']);
        Assert::assertSame(['scope' => 'none'], $this->landedOn($I));

        $I->amOnRoute('/space/spaces', ['connection' => 'archived', 'sort' => 'sortOrder']);
        Assert::assertSame(['scope' => 'archived'], $this->landedOn($I), 'the old default sort is the new default');

        $I->amOnRoute('/space/spaces', ['keyword' => '', 'connection' => '']);
        Assert::assertSame([], $this->landedOn($I), 'empty legacy values are dropped');
    }

    public function testLeavesCurrentParametersAlone(FunctionalTester $I)
    {
        $I->wantTo('open the directory with current parameters without a redirect');
        $I->amUser1();

        $I->stopFollowingRedirects();
        $I->amOnRoute('/space/spaces', ['q' => 'marketing', 'scope' => 'member', 'sort' => 'name']);
        $I->seeResponseCodeIs(200);

        $I->amOnRoute('/space/spaces', ['keyword' => 'marketing']);
        $I->seeResponseCodeIs(301);
    }
}
