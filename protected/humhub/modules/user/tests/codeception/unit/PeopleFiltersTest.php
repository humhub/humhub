<?php

namespace tests\codeception\unit;

use humhub\modules\user\models\ProfileField;
use humhub\modules\user\widgets\PeopleFilterPicker;
use humhub\modules\user\widgets\PeopleFilters;
use tests\codeception\_support\HumHubDbTestCase;

class PeopleFiltersTest extends HumHubDbTestCase
{
    public function testDefaultFilters()
    {
        $peopleFilters = new PeopleFilters();
        $this->assertEquals($this->getGroupOptions(), $this->getFilterOptions('groupId', $peopleFilters));
    }

    public function testProfileFieldFilters()
    {
        $this->becomeUser('Admin');

        // Activate profile field filters
        ProfileField::updateAll(['directory_filter' => 1], ['IN', 'internal_name', ['firstname', 'lastname']]);

        $peopleFilters = new PeopleFilters();

        $this->assertEquals($this->getLastnameOptions(), $this->getFilterOptions('fields[firstname]', $peopleFilters));
    }

    private function getGroupOptions(): array
    {
        return [
            '' => 'Any',
            1 => 'Administrator',
            2 => 'Users',
            3 => 'Moderators'
        ];
    }

    private function getLastnameOptions(): array
    {
        return [
            0 => 'Admin',
            1 => 'Andreas',
            2 => 'Peter',
            3 => 'Sara'
        ];
    }

    private function getFilterOptions(string $filterKey, PeopleFilters $peopleFilters): array
    {
        $filter = $peopleFilters->filters[$filterKey];

        if (isset($filter['options'])) {
            return $filter['options'];
        }

        if (isset($filter['widget'])) {
            /* @var PeopleFilterPicker $widget */
            $widget = new $filter['widget']($filter['widgetOptions']);
            $suggestions =  $widget->getSuggestions();
            $options = [];
            foreach ($suggestions as $suggestion) {
                $options[] = $suggestion['id'];
            }
            return $options;
        }

        return [];
    }
}
