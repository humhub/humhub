<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\topic\models\Topic;
use PHPUnit\Framework\Assert;

/**
 * The topics to manage as rows of a topic table
 * (`humhub\modules\topic\controllers\api\TopicTableController`): `GET /api/v2/admin/topic`,
 * `/space/<id>/topic`, `/user/<id>/topic`.
 *
 * The `content_tag` fixture is empty: the topics are created in `_before()` and removed in
 * `_after()` (see `TopicApiCest`). Space 2 (container 5) has user 2 as its administrator; user 4
 * has the profile container 8.
 */
class TopicTableApiCest
{
    /**
     * @var int[] the topics `_before()` created
     */
    private array $topicIds = [];

    public function _before(): void
    {
        foreach (['Table Global B' => [null, 2], 'Table Global A' => [null, 1], 'Table Space' => [5, 0], 'Table Own' => [8, 0]] as $name => [$container, $sortOrder]) {
            $topic = new Topic(['name' => $name, 'contentcontainer_id' => $container, 'sort_order' => $sortOrder]);
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

    private function names(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].cells.name.text');
    }

    public function testListsTheGlobalTopicsBySortOrder(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/topic', ['q' => 'Table']);

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['Table Global A', 'Table Global B'], $this->names($I));
        Assert::assertSame(['edit', 'view', 'delete'], $I->grabDataFromResponseByJsonPath('$.results[0].actions[*].id'));

        $I->sendGet('admin/topic', ['q' => 'Table', 'sort' => '-name']);
        Assert::assertSame(['Table Global B', 'Table Global A'], $this->names($I));
    }

    public function testRequiresTheSettingsForTheGlobalTopics(ApiTester $I)
    {
        $I->amLoggedInAs(2);
        $I->sendGet('admin/topic');
        $I->seeResponseCodeIs(403);
    }

    public function testListsTheTopicsOfASpace(ApiTester $I)
    {
        // The space's administrator (as on the space's topic page, not every system administrator)
        $I->amLoggedInAs(2);

        $I->sendGet('space/2/topic');

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['Table Space'], $this->names($I));

        $I->sendGet('space/99/topic');
        $I->seeResponseCodeIs(404);
    }

    public function testListsTheOwnTopics(ApiTester $I)
    {
        $I->amLoggedInAs(4);

        $I->sendGet('user/4/topic');

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['Table Own'], $this->names($I));
    }
}
