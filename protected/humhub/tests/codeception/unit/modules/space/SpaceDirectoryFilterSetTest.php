<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\components\filter\FilterSet;
use humhub\modules\space\components\SpaceDirectoryFilterSet;
use humhub\modules\space\components\SpaceListQuery;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * @since 1.20
 */
class SpaceDirectoryFilterSetTest extends HumHubDbTestCase
{
    private function byKey(array $filters): array
    {
        return array_column($filters, null, 'key');
    }

    public function testFiltersInOrder()
    {
        $filters = (new SpaceDirectoryFilterSet())->toArray();

        $this->assertSame(['q', 'sort', 'scope'], array_column($filters, 'key'));
        $this->assertSame(['text', 'select', 'select'], array_column($filters, 'type'));
    }

    public function testKeysAreTheListParameters()
    {
        $filters = $this->byKey((new SpaceDirectoryFilterSet())->toArray());

        $this->assertSame('Search Spaces...', $filters['q']['placeholder']);
        // Archived is a Status option that sends `archived=1` instead of a scope value.
        $archived = array_values(array_filter($filters['scope']['options'], static fn(array $option) => $option['value'] === 'archived'));
        $this->assertSame(['archived' => 1], $archived[0]['params']);

        // Every option is a value `GET /api/v2/space` accepts; "all" / "default" is the
        // placeholder, never an option.
        $sorts = array_column($filters['sort']['options'], 'value');
        $this->assertSame(['name', 'newest', 'oldest'], $sorts);
        $this->assertEmpty(array_diff($sorts, SpaceListQuery::SORTS));
        // No placeholder: the select shows its label ("Sort") as the default state.
        $this->assertArrayNotHasKey('placeholder', $filters['sort']);

        $scopes = array_column($filters['scope']['options'], 'value');
        $this->assertSame(['member', 'following', 'none', 'archived'], $scopes);
        // Every option but Archived (which carries its own parameters) is a scope the API accepts.
        $this->assertSame(['archived'], array_values(array_diff($scopes, SpaceListQuery::SCOPES)));

        foreach ($filters as $filter) {
            foreach ($filter['options'] ?? [] as $option) {
                $this->assertNotSame('', $option['value'], 'no empty option');
            }
        }
    }

    public function testModulesAddFiltersOnTheEvent()
    {
        $handler = function (Event $event) {
            $event->sender->addFilter('category', ['type' => 'select', 'label' => 'Category', 'options' => [], 'sortOrder' => 250]);
        };
        Event::on(SpaceDirectoryFilterSet::class, FilterSet::EVENT_INIT, $handler);

        try {
            $keys = array_column((new SpaceDirectoryFilterSet())->toArray(), 'key');
        } finally {
            Event::off(SpaceDirectoryFilterSet::class, FilterSet::EVENT_INIT, $handler);
        }

        $this->assertSame(['q', 'sort', 'category', 'scope'], $keys);
    }
}
