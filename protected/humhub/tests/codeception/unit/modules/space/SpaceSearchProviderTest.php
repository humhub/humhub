<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\components\listing\ListEvent;
use humhub\modules\space\components\SpaceList;
use humhub\modules\space\models\Space;
use humhub\modules\space\search\SpaceSearchProvider;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * The meta search's spaces: the directory's search ({@see SpaceList}, `purpose=directory`).
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
        $handler = function (ListEvent $event) use (&$purposes) {
            $purposes[] = $event->context->purpose;
            $event->builder->query()->andWhere('0 = 1');
        };
        Event::on(SpaceList::class, SpaceList::EVENT_BUILD, $handler);

        try {
            $provider = new SpaceSearchProvider();
            $provider->keyword = '';
            $result = $provider->getResults(5);
        } finally {
            Event::off(SpaceList::class, SpaceList::EVENT_BUILD, $handler);
        }

        $this->assertSame([SpaceList::PURPOSE_DIRECTORY], $purposes);
        $this->assertSame(0, $result['totalCount']);
        $this->assertSame([], $result['results']);
    }
}
