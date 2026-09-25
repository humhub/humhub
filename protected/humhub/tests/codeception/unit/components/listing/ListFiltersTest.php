<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\listing;

use humhub\components\listing\ArrayListBuilder;
use humhub\components\listing\filters\BoolFilter;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListFilter;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidConfigException;

/**
 * The ready filters: parsing (values, absence, errors), applying, definitions.
 *
 * @since 1.20
 */
class ListFiltersTest extends HumHubDbTestCase
{
    private function parse(ListFilter $filter, mixed $raw): FilterValue
    {
        return $filter->parse($raw === null ? [] : [$filter->key() => $raw], new ListContext());
    }

    private function assertValue(mixed $expected, FilterValue $value): void
    {
        $this->assertTrue($value->present, 'present');
        $this->assertSame([], $value->errors);
        $this->assertSame($expected, $value->value);
    }

    private function assertAbsent(FilterValue $value): void
    {
        $this->assertFalse($value->present);
        $this->assertTrue($value->isValid());
    }

    public function testSearchFilter(): void
    {
        $filter = new SearchFilter();

        $this->assertSame('q', $filter->key());
        $this->assertValue('marketing', $this->parse($filter, '  marketing '));
        $this->assertAbsent($this->parse($filter, null));
        $this->assertAbsent($this->parse($filter, '   '));
        $this->assertSame(['q'], array_keys($this->parse($filter, ['x'])->errors));
        $this->assertNull($filter->definition(new ListContext()), 'no definition without one');
    }

    public function testSearchFilterOverColumns(): void
    {
        $filter = new SearchFilter('name', columns: ['space.name', 'space.description']);
        $list = new QueryListBuilder(Space::find());

        $filter->apply($list, FilterValue::of('Space 2'), new ListContext());

        $this->assertSame(
            ['or', ['like', 'space.name', 'Space 2'], ['like', 'space.description', 'Space 2']],
            $list->query()->where,
        );
    }

    public function testSearchFilterDefinition(): void
    {
        $filter = new SearchFilter(definition: ['label' => 'Search', 'placeholder' => 'Search…', 'sortOrder' => 100]);

        $definition = $filter->definition(new ListContext());
        $this->assertSame('text', $definition->type);
        $this->assertSame(100, $definition->sortOrder);
        $this->assertSame(
            ['key' => 'q', 'type' => 'text', 'label' => 'Search', 'placeholder' => 'Search…', 'placement' => 'primary'],
            $definition->withKey('q')->toArray(),
        );
    }

    public function testEnumFilterSingle(): void
    {
        $filter = new EnumFilter('scope', values: ['all' => null, 'member' => 'Member', '1' => 'One']);

        $this->assertValue('member', $this->parse($filter, 'member'));
        $this->assertValue('all', $this->parse($filter, 'all'), 'a value without a label is accepted');
        $this->assertValue('1', $this->parse($filter, 1), 'numeric values are strings');
        $this->assertAbsent($this->parse($filter, ''));
        $this->assertSame(['scope' => ['Unknown value "friends".']], $this->parse($filter, 'friends')->errors);
        $this->assertSame(['scope' => ['Unknown value "".']], $this->parse($filter, ['member'])->errors);

        $this->assertSame(
            ['type' => 'select', 'options' => [['value' => 'member', 'label' => 'Member'], ['value' => '1', 'label' => 'One']]],
            array_intersect_key((new EnumFilter('scope', values: ['all' => null, 'member' => 'Member', '1' => 'One'], definition: []))
                ->definition(new ListContext())->withKey('scope')->toArray(), ['type' => 1, 'options' => 1]),
        );
    }

    public function testEnumFilterMultiple(): void
    {
        $filter = new EnumFilter('status', values: ['a' => 'A', 'b' => 'B', 'c' => 'C'], multiple: true);

        $this->assertValue(['a', 'b'], $this->parse($filter, 'a, b,,a'));
        $this->assertValue(['c', 'a'], $this->parse($filter, ['c', 'a']));
        $this->assertAbsent($this->parse($filter, ','));
        $this->assertSame(['status' => ['Unknown value "x".', 'Unknown value "y".']], $this->parse($filter, 'a,x,y')->errors);
    }

    public function testEnumFilterPattern(): void
    {
        $filter = new EnumFilter('useCase', multiple: true, pattern: '/^[a-z0-9_-]+$/');

        $this->assertValue(['intranet', 'higher-education'], $this->parse($filter, 'intranet,higher-education'));
        $this->assertFalse($this->parse($filter, 'Bad Value!')->isValid());
    }

    public function testEnumFilterNeedsValuesOrAPattern(): void
    {
        $this->expectException(InvalidConfigException::class);
        new EnumFilter('x');
    }

    public function testEnumFilterApplyMap(): void
    {
        $applied = [];
        $filter = new EnumFilter(
            'scope',
            values: ['member' => 'Member', 'archived' => 'Archived'],
            apply: static function (ArrayListBuilder $list, string $scope) use (&$applied) {
                $applied[] = 'apply:' . $scope;
            },
            applyMap: ['archived' => static function (ArrayListBuilder $list) use (&$applied) {
                $applied[] = 'archived';
            }],
        );

        $filter->apply(new ArrayListBuilder(), FilterValue::of('member'), new ListContext());
        $filter->apply(new ArrayListBuilder(), FilterValue::of('archived'), new ListContext());

        $this->assertSame(['apply:member', 'archived'], $applied);
    }

    public function testBoolFilter(): void
    {
        $filter = new BoolFilter('archived', definition: ['label' => 'Archived']);

        $this->assertValue(true, $this->parse($filter, '1'));
        $this->assertValue(false, $this->parse($filter, '0'));
        $this->assertValue(true, $this->parse($filter, true));
        $this->assertValue(false, $this->parse($filter, 0));
        $this->assertAbsent($this->parse($filter, ''));
        $this->assertSame(['archived'], array_keys($this->parse($filter, 'yes')->errors));
        $this->assertSame('checkbox', $filter->definition(new ListContext())->type);
    }

    public function testIdsFilter(): void
    {
        $filter = new IdsFilter('ids');

        $this->assertValue([1, 2], $this->parse($filter, '1,2,1'));
        $this->assertValue([3, 4], $this->parse($filter, ['3', 4]));
        $this->assertAbsent($this->parse($filter, ''));
        $this->assertSame(['ids'], array_keys($this->parse($filter, '1,x')->errors));
        $this->assertSame(['ids'], array_keys($this->parse($filter, '0')->errors), 'ids are positive');
        $this->assertSame(['ids'], array_keys($this->parse($filter, implode(',', range(1, 101)))->errors), 'at most 100');
        $this->assertTrue($this->parse($filter, implode(',', range(1, 100)))->present);
        $this->assertNull($filter->definition(new ListContext()));
    }

    public function testIdsFilterOnAColumn(): void
    {
        $only = new QueryListBuilder(Space::find());
        (new IdsFilter('ids', column: 'space.id'))->apply($only, FilterValue::of([2, 3]), new ListContext());
        $this->assertSame(['space.id' => [2, 3]], $only->query()->where);

        $without = new QueryListBuilder(Space::find());
        (new IdsFilter('exclude', column: 'space.id', exclude: true))->apply($without, FilterValue::of([2, 3]), new ListContext());
        $this->assertSame(['not in', 'space.id', [2, 3]], $without->query()->where);
    }
}
