<?php

namespace tests\codeception\unit;

use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\PeopleFilterPicker;
use humhub\modules\user\widgets\PeopleFilters;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class PeopleFiltersTest extends HumHubDbTestCase
{
    public function testDefaultFilters()
    {
        $peopleFilters = new PeopleFilters();
        $this->assertEquals([
            '' => 'Any',
            1 => 'Administrator',
            2 => 'Users',
            3 => 'Moderators',
        ], $this->getFilterOptions('groupId', $peopleFilters));
    }

    public function testProfileFieldFilters()
    {
        $this->becomeUser('Admin');

        // Activate profile field filters
        ProfileField::updateAll(['directory_filter' => 1], ['IN', 'internal_name', ['firstname', 'lastname']]);
        $peopleFilters = new PeopleFilters();

        $this->assertEquals(['Admin', 'Andreas', 'Peter', 'Sara'], $this->getFilterOptions('fields[firstname]', $peopleFilters));
    }

    public function testReducedFilters()
    {
        $this->becomeUser('Admin');
        /* @var User $user */
        $user = Yii::$app->user->getIdentity();
        $user->profile->updateAttributes(['lastname' => 'AdminLastName']);
        ProfileField::updateAll(['directory_filter' => 1], ['IN', 'internal_name', ['firstname', 'lastname']]);

        // Filter by lastname
        $peopleQuery = new PeopleQuery(['defaultFilters' => ['fields' => ['lastname' => 'Tester']]]);
        $peopleFilters = new PeopleFilters(['query' => $peopleQuery]);
        $this->assertEquals(['Andreas', 'Peter', 'Sara'], $this->getFilterOptions('fields[firstname]', $peopleFilters));
        $this->assertEquals(['Tester'], $this->getFilterOptions('fields[lastname]', $peopleFilters));
        $this->assertEquals(['' => 'Any', 2 => 'Users', 3 => 'Moderators'], $this->getFilterOptions('groupId', $peopleFilters));

        // Filter by firstname
        $peopleQuery = new PeopleQuery(['defaultFilters' => ['fields' => ['firstname' => 'Admin']]]);
        $peopleFilters = new PeopleFilters(['query' => $peopleQuery]);
        $this->assertEquals(['Admin'], $this->getFilterOptions('fields[firstname]', $peopleFilters));
        $this->assertEquals(['AdminLastName'], $this->getFilterOptions('fields[lastname]', $peopleFilters));
        $this->assertEquals(['' => 'Any', 1 => 'Administrator'], $this->getFilterOptions('groupId', $peopleFilters));

        // Filter by group
        $peopleQuery = new PeopleQuery(['defaultFilters' => ['groupId' => 2]]);
        $peopleFilters = new PeopleFilters(['query' => $peopleQuery]);
        $this->assertEquals(['Peter'], $this->getFilterOptions('fields[firstname]', $peopleFilters));
        $this->assertEquals(['Tester'], $this->getFilterOptions('fields[lastname]', $peopleFilters));
        $this->assertEquals(['' => 'Any', 2 => 'Users'], $this->getFilterOptions('groupId', $peopleFilters));
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
            $suggestions = $widget->getSuggestions();
            $options = [];
            foreach ($suggestions as $suggestion) {
                $options[] = $suggestion['id'];
            }
            return $options;
        }

        return [];
    }
}
