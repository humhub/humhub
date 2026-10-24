<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\components\listing\SpaceFilter;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The space filter: one or several space ids the caller may see, parsed, applied and presented.
 *
 * Fixture ground truth (see {@see \humhub\tests\codeception\unit\modules\space\SpaceListTest}):
 * spaces 2, 3 and 4 are public, space 1 is visible to registered users, space 5 is private.
 * Memberships: user 1 (admin) of 1, 3, 4, 5; user 2 (`User1`) of 2, 3, 4, 5; user 3 (`User2`) of
 * 1, 3, 4; user 4 (`User3`) of nothing.
 *
 * @since 1.20
 */
class SpaceFilterTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    private function context(?int $userId = 4): ListContext
    {
        return new ListContext($userId !== null ? User::findOne(['id' => $userId]) : null);
    }

    private function parse(mixed $raw, ?int $userId = 4, ?SpaceFilter $filter = null): FilterValue
    {
        $filter ??= new SpaceFilter();

        return $filter->parse($raw === null ? [] : [$filter->key() => $raw], $this->context($userId));
    }

    private function assertSpaceNotFound(FilterValue $value, string $message = ''): void
    {
        $this->assertFalse($value->present, $message);
        $this->assertSame(['spaceId' => ['Space not found.']], $value->errors, $message);
    }

    public function testParsesTheIdOfASpaceTheCallerMaySee(): void
    {
        $this->assertSame('spaceId', (new SpaceFilter())->key());

        $value = $this->parse('2');
        $this->assertTrue($value->present);
        $this->assertSame([2], $value->value, 'a list, even for one id - multiple defaults to true');

        $this->assertSame([3], $this->parse(3)->value, 'typed from PHP');
    }

    public function testParsesSeveralIds(): void
    {
        $this->assertSame([1, 2], $this->parse(['1', '2'])->value, 'repeated');
        $this->assertSame([1, 2], $this->parse('1,2')->value, 'comma-separated');
        $this->assertSame([1, 2], $this->parse('2,1,2')->value, 'unique and sorted');
        $this->assertSame([2, 3], $this->parse(' 2 , 3 ')->value, 'whitespace around the ids');
    }

    public function testRefusesAPrivateSpaceTheCallerIsNotAMemberOf(): void
    {
        $this->assertSpaceNotFound($this->parse('5'));
        $this->assertSame([5], $this->parse('5', 2)->value, 'a member still may');
    }

    public function testRefusesAMixedVisibleAndHiddenSet(): void
    {
        // Space 2 is visible to user 4, space 5 is not - the whole set is refused, not narrowed.
        $this->assertSpaceNotFound($this->parse('2,5'));
    }

    public function testRefusesAnArchivedSpace(): void
    {
        // Space 2 is otherwise visible to user 4, but the picker's scope (all) leaves archived
        // spaces out by default, same as the directory.
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 2]);

        $this->assertSpaceNotFound($this->parse('2'));
    }

    public function testRefusesAnUnknownSpace(): void
    {
        $this->assertSpaceNotFound($this->parse('9999'));
    }

    public function testRefusesWhatIsNoId(): void
    {
        foreach (['abc', '0', '-2', '1.5'] as $raw) {
            $this->assertSame(['spaceId' => ['spaceId must be an integer.']], $this->parse($raw)->errors, var_export($raw, true));
        }
    }

    public function testRefusesMoreThanTheMaxIds(): void
    {
        // Over the max: the count is refused before the ids are even looked up (none of them
        // exist as spaces), so this is the "too many" error, not "Space not found" - IdsFilter's
        // own message.
        $this->assertSame(
            ['spaceId' => ['At most 100 ids can be named.']],
            $this->parse(range(1, IdsFilter::MAX_IDS + 1))->errors,
        );
    }

    public function testIsAbsentWithoutAValue(): void
    {
        foreach ([null, '', []] as $raw) {
            $value = $this->parse($raw);
            $this->assertFalse($value->present);
            $this->assertTrue($value->isValid());
        }
    }

    public function testNarrowsAColumn(): void
    {
        $list = new QueryListBuilder(Space::find());

        (new SpaceFilter(column: 'space.id'))->apply($list, FilterValue::of([2, 3]), $this->context());

        $this->assertSame(['space.id' => [2, 3]], $list->query()->where);
    }

    public function testNarrowsByItsCallback(): void
    {
        $applied = null;
        $filter = new SpaceFilter('spaces', column: 'space.id', apply: static function (ListBuilder $list, array $ids) use (&$applied) {
            $applied = $ids;
        });
        $list = new QueryListBuilder(Space::find());

        $filter->apply($list, FilterValue::of([2, 3]), $this->context());

        $this->assertSame([2, 3], $applied);
        $this->assertNull($list->query()->where, 'the callback replaces the column');
    }

    public function testMultipleFalse(): void
    {
        $filter = new SpaceFilter(multiple: false);

        $this->assertSame(2, $this->parse('2', 4, $filter)->value);
        $this->assertSame(2, $this->parse(2, 4, $filter)->value, 'typed from PHP');

        $this->assertSame(['spaceId' => ['spaceId must be an integer.']], $this->parse(['2', '3'], 4, $filter)->errors, 'repeated');
        $this->assertSame(['spaceId' => ['spaceId must be an integer.']], $this->parse('2,3', 4, $filter)->errors, 'comma-separated');
    }

    public function testMultipleFalseRefusesASpaceTheCallerMayNotSee(): void
    {
        $this->assertSpaceNotFound($this->parse('5', 4, new SpaceFilter(multiple: false)));
    }

    public function testIsASpaceTypeDefinitionInAList(): void
    {
        $list = new SpaceFilterTestList();

        $this->assertSame([
            'key' => 'spaceId',
            'type' => 'space',
            'label' => 'Space',
            'multiple' => true,
            'placement' => 'primary',
            'props' => ['scope' => 'member'],
        ], $list->definitions($this->context())[0]);

        $this->assertNull((new SpaceFilter())->definition($this->context()), 'no definition without one');
    }

    public function testDefaultLabelWithoutOneInTheDefinition(): void
    {
        $definition = (new SpaceFilter(definition: ['sortOrder' => 1]))->definition($this->context());

        $this->assertSame('Space', $definition->label);
    }

    public function testDefinitionCannotOverrideTypeOrMultiple(): void
    {
        $definition = (new SpaceFilter(definition: ['type' => 'text', 'multiple' => false]))->definition($this->context());

        $this->assertSame('space', $definition->type);
        $this->assertTrue($definition->multiple);
    }

    public function testIsNotAvailableToGuests(): void
    {
        $this->assertTrue((new SpaceFilter())->isAvailable($this->context()));
        $this->assertFalse((new SpaceFilter())->isAvailable($this->context(null)));

        $list = new SpaceFilterTestList();
        $this->assertSame([], $list->definitions($this->context(null)), 'no definition for a guest');
    }
}

/**
 * A list with one space filter, its definition carrying a control-specific `scope` prop.
 */
class SpaceFilterTestList extends FilterableList
{
    protected function filters(): array
    {
        return [
            new SpaceFilter(definition: ['label' => 'Space', 'props' => ['scope' => 'member'], 'sortOrder' => 110]),
        ];
    }

    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new QueryListBuilder(Space::find());
    }
}
