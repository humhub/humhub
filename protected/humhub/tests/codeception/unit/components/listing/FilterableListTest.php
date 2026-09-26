<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\listing;

use humhub\components\listing\ArrayListBuilder;
use humhub\components\listing\FilterableList;
use humhub\components\listing\FilterDefinition;
use humhub\components\listing\filters\BoolFilter;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListEvent;
use humhub\components\listing\ListFilter;
use humhub\components\listing\ListValidationException;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * The list model on a list of fruit: an array list with a search, a colour select, a flag,
 * ids and two sorts.
 *
 * @since 1.20
 */
class FilterableListTest extends HumHubDbTestCase
{
    protected function tearDown(): void
    {
        Event::off(FruitList::class, FilterableList::EVENT_INIT);
        Event::off(FruitList::class, FilterableList::EVENT_BUILD);
        parent::tearDown();
    }

    private function names(array $params = [], ?ListContext $context = null): array
    {
        return array_column((new FruitList())->build($params, $context ?? new ListContext())->items(), 'name');
    }

    private function errors(array $params, ?FilterableList $list = null): array
    {
        try {
            ($list ?? new FruitList())->build($params, new ListContext());
        } catch (ListValidationException $e) {
            return $e->errors;
        }
        $this->fail('the parameters are refused');
    }

    public function testBuildsWithoutParameters(): void
    {
        $this->assertSame(['Apple', 'Banana', 'Cherry', 'Lime'], $this->names());
    }

    public function testAppliesEveryPresentFilter(): void
    {
        $this->assertSame(['Banana'], $this->names(['q' => 'an']));
        $this->assertSame(['Banana', 'Lime'], $this->names(['q' => '', 'colour' => 'yellow,green']));
        $this->assertSame(['Cherry'], $this->names(['sweet' => '1', 'colour' => ['red']]));
        $this->assertSame(['Banana'], $this->names(['ids' => '2,3', 'sweet' => 0]));
    }

    public function testRefusesUnknownParametersAndInvalidValues(): void
    {
        $errors = $this->errors(['typo' => 'x', 'colour' => 'blue', 'sweet' => 'yes', 'ids' => 'a', 'sort' => 'random']);

        $this->assertEqualsCanonicalizing(['typo', 'colour', 'sweet', 'ids', 'sort'], array_keys($errors));
        $this->assertSame(['Unknown parameter.'], $errors['typo']);
        $this->assertSame(['Unknown value "blue".'], $errors['colour']);
    }

    public function testAcceptsPagingAndPurpose(): void
    {
        $this->assertCount(4, $this->names(['page' => '2', 'pageSize' => '10', 'purpose' => 'basket']));
    }

    public function testThePurposeParameterFillsTheContext(): void
    {
        $context = null;
        Event::on(FruitList::class, FilterableList::EVENT_BUILD, static function (ListEvent $event) use (&$context) {
            $context = $event->context;
        });

        (new FruitList())->build(['purpose' => 'basket'], new ListContext());
        $this->assertSame('basket', $context->purpose);

        (new FruitList())->build(['purpose' => 'basket'], new ListContext(purpose: 'market'));
        $this->assertSame('market', $context->purpose, "the context's own purpose wins");

        $this->assertArrayHasKey('purpose', $this->errors(['purpose' => 'stream']), 'a list with purposes refuses others');
    }

    public function testRefusesTheParameterOfAnUnavailableFilter(): void
    {
        $this->assertSame(['Banana', 'Lime'], $this->names(['secret' => 'x'], new ListContext(purpose: 'market')));

        $errors = $this->errors(['secret' => 'x']);
        $this->assertSame(['This filter is not available.'], $errors['secret']);
    }

    public function testTreatsAnEmptyValueOfAnUnavailableFilterAsAbsent(): void
    {
        $this->assertCount(4, $this->names(['secret' => '']));
        $this->assertCount(4, $this->names(['secret' => null]));
        $this->assertCount(4, $this->names(['secret' => []]));
    }

    public function testMatchesBracketKeysOfNestedParameters(): void
    {
        $age = null;
        Event::on(FruitList::class, FilterableList::EVENT_INIT, static function (ListEvent $event) use (&$age) {
            $event->list->addFilter(new SearchFilter('fields[age]', apply: static function (ArrayListBuilder $list, string $value) use (&$age) {
                $age = $value;
            }));
        });

        // `fields[age]=x` as PHP parses it.
        $this->assertCount(4, $this->names(['fields' => ['age' => 'x']]));
        $this->assertSame('x', $age);

        $errors = $this->errors(['fields' => ['age' => 'x', 'x' => 'y']]);
        $this->assertSame(['fields[x]'], array_keys($errors));
        $this->assertSame(['Unknown parameter.'], $errors['fields[x]']);

        // A list (`ids[]=2&ids[]=3`) stays the value of its parameter.
        $this->assertSame(['Banana', 'Cherry'], $this->names(['ids' => ['2', '3']]));
    }

    public function testSorts(): void
    {
        $this->assertSame(['Lime', 'Cherry', 'Banana', 'Apple'], $this->names(['sort' => 'reverse']));
        $this->assertSame(['Apple', 'Banana', 'Cherry', 'Lime'], $this->names(['sort' => 'default']));
        $this->assertSame(['default' => null, 'reverse' => 'Reverse'], (new FruitList())->getSorts());
    }

    public function testModulesAddAndRemoveFiltersAndSortsOnInit(): void
    {
        $initialized = 0;
        Event::on(FruitList::class, FilterableList::EVENT_INIT, static function (ListEvent $event) use (&$initialized) {
            $initialized++;
            $event->list->removeFilter('sweet');
            $event->list->addFilter(new EnumFilter(
                'shape',
                values: ['round' => 'Round', 'long' => 'Long'],
                apply: static fn(ArrayListBuilder $list, string $shape) => $list->filter(static fn(array $fruit) => $fruit['shape'] === $shape),
                definition: ['label' => 'Shape', 'sortOrder' => 150],
            ));
            $event->list->addSort('shape', 'By shape', static fn(ArrayListBuilder $list) => $list->sort(static fn(array $a, array $b) => $a['shape'] <=> $b['shape']));
        });

        $list = new FruitList();
        $this->assertSame(1, $initialized, 'once per instance');
        $this->assertNull($list->getFilter('sweet'));
        $this->assertInstanceOf(EnumFilter::class, $list->getFilter('shape'));

        $this->assertSame(['Banana'], array_column($list->build(['shape' => 'long'], new ListContext())->items(), 'name'));
        $this->assertSame('Banana', array_column((new FruitList())->build(['sort' => 'shape'], new ListContext())->items(), 'name')[0]);
        $this->assertArrayHasKey('sweet', $this->errors(['sweet' => '1']), 'a removed filter is unknown');
        $this->assertSame(['q', 'shape', 'sort', 'colour'], array_column((new FruitList())->definitions(new ListContext()), 'key'));
    }

    public function testBuildEventRestrictsAfterTheFilters(): void
    {
        $received = null;
        Event::on(FruitList::class, FilterableList::EVENT_BUILD, static function (ListEvent $event) use (&$received) {
            $received = $event;
            $event->builder->filter(static fn(array $fruit) => $fruit['name'] !== 'Lime');
        });

        $this->assertSame(['Banana'], $this->names(['colour' => 'yellow,green'], new ListContext(purpose: 'market')));
        $this->assertSame(['yellow', 'green'], $received->value('colour'));
        $this->assertNull($received->value('q'), 'absent');
        $this->assertFalse($received->values['q']->present);
        $this->assertSame('market', $received->context->purpose);
        $this->assertInstanceOf(FruitList::class, $received->list);
    }

    public function testDefinitionsInSortOrderWithTheSortSelect(): void
    {
        $definitions = (new FruitList())->definitions(new ListContext());

        $this->assertSame(['q', 'sort', 'colour', 'sweet'], array_column($definitions, 'key'), 'no definition for ids and the unavailable secret');
        $this->assertSame([
            'key' => 'sort',
            'type' => 'select',
            'label' => 'Sort',
            'options' => [['value' => 'reverse', 'label' => 'Reverse']],
            'placement' => 'primary',
        ], $definitions[1]);
        $this->assertSame([
            'key' => 'colour',
            'type' => 'tags',
            'label' => 'Colour',
            'options' => [['value' => 'red', 'label' => 'Red'], ['value' => 'yellow', 'label' => 'Yellow'], ['value' => 'green', 'label' => 'Green']],
            'multiple' => true,
            'placement' => 'panel',
        ], $definitions[2]);

        $this->assertContains('secret', array_column((new FruitList())->definitions(new ListContext(purpose: 'market')), 'key'));
    }

    public function testDefinitionContract(): void
    {
        $definition = new FilterDefinition(
            type: 'select',
            label: 'Status',
            placeholder: 'Any',
            options: [['value' => 'a', 'label' => 'A']],
            optionsUrl: '/api/v2/x',
            multiple: false,
            default: 'a',
            hidden: false,
            wide: true,
            placement: FilterDefinition::PLACEMENT_PANEL,
            sortOrder: 5,
        );

        $this->assertSame(
            ['key', 'type', 'label', 'placeholder', 'options', 'optionsUrl', 'multiple', 'default', 'hidden', 'wide', 'placement'],
            array_keys($definition->withKey('status')->toArray()),
        );
        $this->assertSame(['key' => 'q', 'type' => 'text', 'placement' => 'primary'], (new FilterDefinition('text'))->withKey('q')->toArray());
    }

    public function testRefusesAnInvalidDefinition(): void
    {
        $this->expectException(InvalidConfigException::class);
        new FilterDefinition('select', placement: 'sidebar');
    }

    public function testRefusesAReservedParameter(): void
    {
        $this->expectException(InvalidConfigException::class);
        (new FruitList())->addFilter(new SearchFilter('page'));
    }
}

/**
 * Available only in the `market` purpose.
 */
class SecretFilter extends ListFilter
{
    public function key(): string
    {
        return 'secret';
    }

    public function isAvailable(ListContext $context): bool
    {
        return $context->purpose === 'market';
    }

    public function parse(array $raw, ListContext $context): FilterValue
    {
        return isset($raw['secret']) ? FilterValue::of($raw['secret']) : FilterValue::absent();
    }

    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        /** @var ArrayListBuilder $list */
        $list->filter(static fn(array $fruit) => $fruit['colour'] !== 'red');
    }

    public function definition(ListContext $context): ?FilterDefinition
    {
        return new FilterDefinition('text', label: 'Secret');
    }
}

class FruitList extends FilterableList
{
    public const FRUIT = [
        1 => ['name' => 'Apple', 'colour' => 'red', 'shape' => 'round', 'sweet' => false],
        2 => ['name' => 'Banana', 'colour' => 'yellow', 'shape' => 'long', 'sweet' => false],
        3 => ['name' => 'Cherry', 'colour' => 'red', 'shape' => 'round', 'sweet' => true],
        4 => ['name' => 'Lime', 'colour' => 'green', 'shape' => 'round', 'sweet' => false],
    ];

    protected function filters(): array
    {
        return [
            new SearchFilter('q', apply: static fn(ArrayListBuilder $list, string $q) => $list->filter(
                static fn(array $fruit) => stripos($fruit['name'], $q) !== false,
            ), definition: ['label' => 'Search', 'sortOrder' => 100]),
            new EnumFilter(
                'colour',
                values: ['red' => 'Red', 'yellow' => 'Yellow', 'green' => 'Green'],
                apply: static fn(ArrayListBuilder $list, array $colours) => $list->filter(static fn(array $fruit) => in_array($fruit['colour'], $colours, true)),
                multiple: true,
                definition: ['type' => 'tags', 'label' => 'Colour', 'multiple' => true, 'placement' => 'panel', 'sortOrder' => 300],
            ),
            new BoolFilter('sweet', apply: static fn(ArrayListBuilder $list, bool $sweet) => $list->filter(
                static fn(array $fruit) => $fruit['sweet'] === $sweet,
            ), definition: ['label' => 'Sweet', 'sortOrder' => 400]),
            new IdsFilter('ids', apply: static fn(ArrayListBuilder $list, array $ids) => $list->setItems(array_intersect_key($list->items(), array_flip($ids)))),
            new SecretFilter(),
        ];
    }

    protected function sorts(): array
    {
        return ['default' => null, 'reverse' => 'Reverse'];
    }

    public function purposes(): array
    {
        return ['basket', 'market'];
    }

    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new ArrayListBuilder(self::FRUIT);
    }

    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var ArrayListBuilder $builder */
        if ($this->value($values, self::SORT_PARAM) === 'reverse') {
            $builder->setItems(array_reverse($builder->items(), true));

            return;
        }

        parent::finalize($builder, $values, $context);
    }

    public function build(array $params, ListContext $context): ArrayListBuilder
    {
        /** @var ArrayListBuilder $builder */
        $builder = parent::build($params, $context);

        return $builder;
    }
}
