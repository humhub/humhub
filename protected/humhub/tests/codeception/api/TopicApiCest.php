<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The topic picker (`humhub\modules\topic\controllers\api\TopicController::actionPicker()`) — the suggestions and
 * URL values of a topic filter, built by `TopicList` (whose rules `TopicListTest` covers).
 *
 * Fixture ground truth used here: space 2 (container 5) is public, space 5 (container 12) is
 * private; user 4 (`User3`, profile container 8) is a member of nothing. The `content_tag` fixture is empty: the
 * topics are created in `_before()` and removed in `_after()`, since rows the API Cests write
 * outlive them and the unit suites read the same database.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class TopicApiCest
{
    /**
     * @var int[] the topics `_before()` created
     */
    private array $topicIds = [];

    public function _before(): void
    {
        foreach (['Api Global' => null, 'Api Public' => 5, 'Api Private' => 12, 'Api Own' => 8] as $name => $container) {
            $topic = new Topic(['name' => $name, 'contentcontainer_id' => $container]);
            if (!$topic->save()) {
                throw new \RuntimeException('Topic "' . $name . '" not saved: ' . json_encode($topic->getErrors()));
            }
            $this->topicIds[$name] = (int)$topic->id;
        }
    }

    public function _after(): void
    {
        Topic::deleteAll(['id' => array_values($this->topicIds)]);
        $this->topicIds = [];
    }

    public function testAnswersTheShortShape(ApiTester $I)
    {
        $I->wantTo('get the topics I may see in the short shape, a global one without container');
        $I->amLoggedInAs(4);
        $I->sendGet('topic/picker', ['q' => 'Api']);

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['Api Global', 'Api Own', 'Api Public'], $I->grabDataFromResponseByJsonPath('$.results[*].name'), 'not the private space\'s');

        [$global, $own, $public] = $I->grabDataFromResponseByJsonPath('$.results')[0];
        Assert::assertSame(['id', 'name', 'color', 'container'], array_keys($global));
        Assert::assertSame($this->topicIds['Api Global'], $global['id']);
        Assert::assertNull($global['container']);
        $user = User::findOne(['id' => 4]);
        Assert::assertSame(['id' => 8, 'guid' => $user->guid, 'name' => $user->displayName], $own['container'], 'a profile: the user\'s guid and display name');
        Assert::assertSame(['id' => 5, 'guid' => '5396d499-20d6-4233-800b-c6c86e5fa34b', 'name' => 'Space 2'], $public['container']);
    }

    public function testNamesTopicsOrTakesAContainer(ApiTester $I)
    {
        $I->wantTo('resolve topic ids and list a container\'s topics with the global ones');
        $I->amLoggedInAs(4);

        $I->sendGet('topic/picker', ['ids' => $this->topicIds['Api Public'] . ',' . $this->topicIds['Api Private']]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([$this->topicIds['Api Public']], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('topic/picker', ['containerId' => 5, 'q' => 'Api']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['Api Global', 'Api Public'], $I->grabDataFromResponseByJsonPath('$.results[*].name'));

        $I->sendGet('topic/picker', ['containerId' => 12]);
        $I->seeResponseCodeIs(422);
        Assert::assertSame(['containerId' => ['Container not found.']], $I->grabDataFromResponseByJsonPath('$.errors')[0]);
    }

    public function testNeedsASearchIdsOrAContainer(ApiTester $I)
    {
        $I->wantTo('be told that the topic picker needs a search, ids or a container');
        $I->amLoggedInAs(4);

        foreach ([[], ['q' => ''], ['q' => '  '], ['ids' => ''], ['page' => 1, 'pageSize' => 5]] as $params) {
            $I->sendGet('topic/picker', $params);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.q');
        }
    }

    public function testRefusesAnUnknownParameter(ApiTester $I)
    {
        $I->wantTo('be told about a parameter the topic picker does not take');
        $I->amLoggedInAs(4);

        foreach (['sort' => 'name', 'purpose' => 'picker', 'spaceId' => '2'] as $param => $value) {
            $I->sendGet('topic/picker', ['q' => 'Api', $param => $value]);
            $I->seeResponseCodeIs(422);
            Assert::assertSame(['Unknown parameter.'], $I->grabDataFromResponseByJsonPath('$.errors.' . $param)[0], $param);
        }
    }

    public function testAnswersAtMost20(ApiTester $I)
    {
        $I->wantTo('get at most 20 topics per page and name at most 20');
        $I->amLoggedInAs(4);

        $I->sendGet('topic/picker', ['q' => 'Api', 'pageSize' => 100]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(20, $I->grabDataFromResponseByJsonPath('$.pageSize')[0], 'at most 20 per page');

        $I->sendGet('topic/picker', ['q' => 'Api']);
        Assert::assertSame(20, $I->grabDataFromResponseByJsonPath('$.pageSize')[0], '20 by default');

        $I->sendGet('topic/picker', ['ids' => implode(',', range(1, 20))]);
        $I->seeResponseCodeIs(200);

        $I->sendGet('topic/picker', ['ids' => implode(',', range(1, 21))]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.ids');
    }

    public function testIsForLoggedInUsers(ApiTester $I)
    {
        $I->wantTo('see the topic picker stay closed to guests, also with guest access');
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        try {
            $I->sendGet('topic/picker', ['q' => 'Api']);
            $I->seeResponseCodeIs(401);
        } finally {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);
        }
    }
}
