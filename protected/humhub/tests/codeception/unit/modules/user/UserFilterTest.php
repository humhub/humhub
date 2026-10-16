<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\user;

use humhub\components\listing\FilterableList;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListEvent;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\listing\UserFilter;
use humhub\modules\user\components\UserList;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * The person filter: a user id the caller may see, parsed, applied and presented.
 *
 * Fixture ground truth (see {@see UserListTest}): the enabled users are 1, 2, 3, 4 and 8; 5 is
 * disabled, 6 and 7 are unapproved.
 *
 * @since 1.20
 */
class UserFilterTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    /**
     * @var callable|null the {@see UserList::EVENT_BUILD} handler a test registered
     */
    private $buildHandler = null;

    protected function setUp(): void
    {
        parent::setUp();
        ContentContainerBlockedUsers::deleteAll(['user_id' => 4]);
    }

    protected function tearDown(): void
    {
        if ($this->buildHandler !== null) {
            Event::off(UserList::class, UserList::EVENT_BUILD, $this->buildHandler);
            $this->buildHandler = null;
        }
        parent::tearDown();
    }

    private function onUserListBuild(callable $handler): void
    {
        $this->buildHandler = $handler;
        Event::on(UserList::class, UserList::EVENT_BUILD, $handler);
    }

    private function context(?int $userId = 4): ListContext
    {
        return new ListContext($userId !== null ? User::findOne(['id' => $userId]) : null);
    }

    private function parse(mixed $raw, ?int $userId = 4, ?UserFilter $filter = null): FilterValue
    {
        $filter ??= new UserFilter();

        return $filter->parse($raw === null ? [] : [$filter->key() => $raw], $this->context($userId));
    }

    private function assertUnknownUser(FilterValue $value, string $message = ''): void
    {
        $this->assertFalse($value->present, $message);
        $this->assertSame(['userId' => ['User not found.']], $value->errors, $message);
    }

    public function testParsesTheIdOfAUserTheCallerMaySee(): void
    {
        $this->assertSame('userId', (new UserFilter())->key());

        $value = $this->parse('2');
        $this->assertTrue($value->present);
        $this->assertSame(2, $value->value, 'an int');

        $this->assertSame(3, $this->parse(3)->value, 'typed from PHP');
    }

    public function testIsAbsentWithoutAValue(): void
    {
        foreach ([null, ''] as $raw) {
            $value = $this->parse($raw);
            $this->assertFalse($value->present);
            $this->assertTrue($value->isValid());
        }
    }

    public function testRefusesWhatIsNoId(): void
    {
        foreach (['abc', '0', '-2', '1.5', ['2']] as $raw) {
            $this->assertSame(['userId' => ['userId must be an integer.']], $this->parse($raw)->errors, var_export($raw, true));
        }
    }

    public function testRefusesAnUnknownUser(): void
    {
        $this->assertUnknownUser($this->parse('9999'));
    }

    public function testRefusesUsersTheListDoesNotShow(): void
    {
        $this->assertUnknownUser($this->parse('5'), 'disabled');
        $this->assertUnknownUser($this->parse('6'), 'unapproved');

        User::updateAll(['visibility' => User::VISIBILITY_HIDDEN], ['id' => 3]);
        $this->assertUnknownUser($this->parse('3'), 'hidden');
        $this->assertSame(3, $this->parse('3', 3)->value, 'a hidden user still finds themselves');
    }

    public function testRefusesAUserWhoBlockedTheCaller(): void
    {
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => User::findOne(['id' => 2])->contentcontainer_id,
            'user_id' => 4,
        ])->execute();

        $this->assertUnknownUser($this->parse('2'));
        $this->assertSame(2, $this->parse('2', 3)->value, 'another caller still may');
    }

    public function testFollowsTheRestrictionsModulesAddToThePicker(): void
    {
        $this->onUserListBuild(static function (ListEvent $event) {
            if ($event->context->purpose === UserList::PURPOSE_PICKER) {
                $event->builder->query()->andWhere(['!=', 'user.id', 2]);
            }
        });

        $this->assertUnknownUser($this->parse('2'));
        $this->assertSame(3, $this->parse('3')->value);
    }

    public function testChecksAgainstThePickerOfTheCallerNotTheHostList(): void
    {
        // What the control's suggestions saw: the picker without the host list's purpose or
        // container - a restriction of those must not refuse a user it suggested.
        $this->onUserListBuild(static function (ListEvent $event) {
            if ($event->context->container !== null || $event->context->purpose === 'tasks.board') {
                $event->builder->query()->andWhere(['!=', 'user.id', 2]);
            }
        });
        $host = new ListContext(User::findOne(['id' => 4]), 'tasks.board', Space::findOne(['id' => 2]));

        $this->assertSame(2, (new UserFilter())->parse(['userId' => '2'], $host)->value);
    }

    public function testIsNotAvailableToGuests(): void
    {
        $this->assertTrue((new UserFilter())->isAvailable($this->context()));
        $this->assertFalse((new UserFilter())->isAvailable($this->context(null)));

        $list = new UserFilterTestList();
        $this->assertSame([], $list->definitions($this->context(null)), 'no definition for a guest');

        try {
            $list->build(['authorId' => '2'], $this->context(null));
            $this->fail('a guest\'s person filter is refused');
        } catch (ListValidationException $e) {
            $this->assertSame(['authorId' => ['This filter is not available.']], $e->errors);
        }
    }

    public function testNarrowsAColumn(): void
    {
        $list = new QueryListBuilder(Content::find());

        (new UserFilter(column: 'content.created_by'))->apply($list, FilterValue::of(2), $this->context());

        $this->assertSame(['content.created_by' => 2], $list->query()->where);
    }

    public function testNarrowsByItsCallback(): void
    {
        $applied = null;
        $filter = new UserFilter('author', column: 'content.created_by', apply: static function (ListBuilder $list, int $id) use (&$applied) {
            $applied = $id;
        });
        $list = new QueryListBuilder(Content::find());

        $filter->apply($list, FilterValue::of(3), $this->context());

        $this->assertSame(3, $applied);
        $this->assertNull($list->query()->where, 'the callback replaces the column');
    }

    public function testIsAUserTypeDefinitionInAList(): void
    {
        $list = new UserFilterTestList();

        $this->assertSame([
            ['key' => 'authorId', 'type' => 'user', 'label' => 'Author', 'placement' => 'primary'],
            ['key' => 'userId', 'type' => 'user', 'label' => 'Person', 'placeholder' => 'Anyone', 'placement' => 'panel'],
        ], $list->definitions($this->context()));

        $this->assertNull((new UserFilter())->definition($this->context()), 'no definition without one');
    }

    public function testValidatesAndAppliesInAList(): void
    {
        $query = (new UserFilterTestList())->build(['authorId' => '2'], $this->context())->query();
        $this->assertSame(['content.created_by' => 2], $query->where);

        try {
            (new UserFilterTestList())->build(['authorId' => '5'], $this->context());
            $this->fail('a user the caller may not see is refused');
        } catch (ListValidationException $e) {
            $this->assertSame(['authorId' => ['User not found.']], $e->errors);
        }
    }
}

/**
 * A list with two person filters: one labelled by the list, one with the defaults.
 */
class UserFilterTestList extends FilterableList
{
    protected function filters(): array
    {
        return [
            // The type is the filter's, whatever the definition says.
            new UserFilter('authorId', column: 'content.created_by', definition: ['type' => 'select', 'label' => 'Author', 'sortOrder' => 100]),
            new UserFilter(definition: ['placeholder' => 'Anyone', 'placement' => 'panel', 'sortOrder' => 200]),
        ];
    }

    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new QueryListBuilder(Content::find());
    }

    public function build(array $params, ListContext $context): QueryListBuilder
    {
        /** @var QueryListBuilder $builder */
        $builder = parent::build($params, $context);

        return $builder;
    }
}
