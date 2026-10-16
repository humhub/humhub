<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use PHPUnit\Framework\Assert;
use user\FunctionalTester;
use Yii;

/**
 * Links to the People directory with the parameters of its filters before 1.20 (`keyword`,
 * `connection`) land on the same selection under the parameters of `GET /api/v2/user` the
 * directory uses now.
 */
class PeopleDirectoryRedirectCest
{
    public function _after(): void
    {
        Yii::$app->getModule('friendship')->settings->delete('enable');
    }

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
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        $I->amUser1();

        $I->amOnRoute('/user/people', ['keyword' => 'sara', 'connection' => 'following', 'sort' => 'lastname', 'groupId' => 2, 'fields' => ['firstname' => 'Sara']]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['fields' => ['firstname' => 'Sara'], 'groupId' => '2', 'q' => 'sara', 'scope' => 'following', 'sort' => 'lastname'], $this->landedOn($I));

        $I->amOnRoute('/user/people', ['connection' => 'followers']);
        Assert::assertSame(['scope' => 'followers'], $this->landedOn($I));

        $I->amOnRoute('/user/people', ['connection' => 'friends']);
        Assert::assertSame(['scope' => 'friends'], $this->landedOn($I));

        $I->amOnRoute('/user/people', ['connection' => 'pending_friends']);
        Assert::assertSame(['scope' => 'pendingFriends'], $this->landedOn($I));

        $I->amOnRoute('/user/people', ['keyword' => '', 'connection' => '']);
        Assert::assertSame([], $this->landedOn($I), 'empty legacy values are dropped');
    }

    public function testDropsTheConnectionOfAFeatureThatIsOff(FunctionalTester $I)
    {
        $I->wantTo('follow an old link to a connection whose feature is off to the whole directory');
        Yii::$app->getModule('friendship')->settings->set('enable', 0);
        $I->amUser1();

        $I->amOnRoute('/user/people', ['connection' => 'friends', 'keyword' => 'sara']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['q' => 'sara'], $this->landedOn($I));

        $I->amOnRoute('/user/people', ['connection' => 'following']);
        Assert::assertSame(['scope' => 'following'], $this->landedOn($I), 'following is on');
    }

    public function testLeavesCurrentParametersAlone(FunctionalTester $I)
    {
        $I->wantTo('open the directory with current parameters without a redirect');
        $I->amUser1();

        $I->stopFollowingRedirects();
        $I->amOnRoute('/user/people', ['q' => 'sara', 'scope' => 'following', 'sort' => 'firstname']);
        $I->seeResponseCodeIs(200);

        $I->amOnRoute('/user/people', ['keyword' => 'sara']);
        $I->seeResponseCodeIs(301);
    }
}
