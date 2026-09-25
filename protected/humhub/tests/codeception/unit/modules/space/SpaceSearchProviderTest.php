<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\modules\space\components\SpaceListQuery;
use humhub\modules\space\components\SpaceListQueryEvent;
use humhub\modules\space\models\Space;
use humhub\modules\space\search\SpaceSearchProvider;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * The meta search's spaces: the directory's search ({@see SpaceListQuery}, `purpose=directory`).
 *
 * @since 1.20
 */
class SpaceSearchProviderTest extends HumHubDbTestCase
{
    public function testFindsSpacesByKeywordWithTheirTotal(): void
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        $provider = new SpaceSearchProvider();
        $provider->keyword = $space->name;
        $result = $provider->getResults(5);

        $this->assertGreaterThanOrEqual(1, $result['totalCount']);
        $this->assertLessThanOrEqual(5, count($result['results']));
        $this->assertContains($space->name, array_map(static fn($record) => $record->getTitle(), $result['results']));
    }

    public function testLimitsTheResultsButNotTheTotal(): void
    {
        $this->becomeUser('Admin');

        $provider = new SpaceSearchProvider();
        $provider->keyword = '';
        $result = $provider->getResults(1);

        $this->assertCount(1, $result['results']);
        $this->assertGreaterThan(1, $result['totalCount']);
    }

    public function testAppliesTheDirectoryRestrictionsOfModules(): void
    {
        $this->becomeUser('Admin');
        $purposes = [];
        $handler = function (SpaceListQueryEvent $event) use (&$purposes) {
            $purposes[] = $event->purpose;
            $event->query->andWhere('0 = 1');
        };
        Event::on(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);

        try {
            $provider = new SpaceSearchProvider();
            $provider->keyword = '';
            $result = $provider->getResults(5);
        } finally {
            Event::off(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);
        }

        $this->assertSame([SpaceListQuery::PURPOSE_DIRECTORY], $purposes);
        $this->assertSame(0, $result['totalCount']);
        $this->assertSame([], $result['results']);
    }
}
