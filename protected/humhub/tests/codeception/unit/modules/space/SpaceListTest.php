<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListEvent;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\space\components\SpaceList;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * Fixture ground truth: spaces 2, 3 and 4 are public, space 1 is visible to registered users,
 * space 5 is private. Memberships: user 1 (admin) of 1, 3, 4, 5; user 2 (`User1`) of 2, 3, 4,
 * 5; user 3 (`User2`) of 1, 3, 4; user 4 (`User3`) of nothing. There is no follow fixture.
 *
 * @since 1.20
 */
class SpaceListTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Follow::deleteAll(['object_model' => Space::class]);
        ContentContainerBlockedUsers::deleteAll(['user_id' => 4]);
    }

    protected function tearDown(): void
    {
        Event::off(SpaceList::class, SpaceList::EVENT_INIT);
        Event::off(SpaceList::class, SpaceList::EVENT_BUILD);
        parent::tearDown();
    }

    private function user(int $id): User
    {
        return User::findOne(['id' => $id]);
    }

    private function seedFollow(int $userId, int $spaceId): void
    {
        $follow = new Follow(['user_id' => $userId, 'object_model' => Space::class, 'object_id' => $spaceId]);
        $this->assertTrue($follow->save(), 'seeded a follow');
    }

    /**
     * @return int[] the ids of the list, in result order
     */
    private function ids(array $params = [], ?int $userId = 4, ?string $purpose = null): array
    {
        $context = new ListContext($userId !== null ? $this->user($userId) : null, $purpose);

        return array_map('intval', (new SpaceList())->build($params, $context)->query()->select('space.id')->column());
    }

    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }

    private function byKey(array $definitions): array
    {
        return array_column($definitions, null, 'key');
    }

    public function testListsOnlyVisibleSpaces()
    {
        $this->becomeUser('User3');

        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids()));
    }

    public function testTheCurrentUsersContext()
    {
        $this->becomeUser('User3');

        $ids = array_map('intval', (new SpaceList())->build([], ListContext::forCurrentUser())->query()->select('space.id')->column());
        $this->assertNotContains(5, $ids, 'the private space stays hidden');
    }

    public function testFiltersBlockedSpaces()
    {
        $this->becomeUser('User3');
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => Space::findOne(['id' => 2])->contentcontainer_id,
            'user_id' => 4,
        ])->execute();

        $this->assertNotContains(2, $this->ids());
    }

    public function testSearchesNameDescriptionAndTags()
    {
        $this->becomeUser('User3');

        $ids = $this->ids(['q' => 'Space 2']);

        $this->assertContains(2, $ids);
        $this->assertNotContains(1, $ids);
    }

    public function testScopeMember()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);

        $this->assertSame([1, 3, 4], $this->sorted($this->ids(['scope' => 'member'], 3)));
    }

    public function testScopeFollowing()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);

        $this->assertSame([2], $this->ids(['scope' => 'following'], 3));
    }

    public function testScopeMine()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);

        $ids = $this->ids(['scope' => 'mine'], 3);

        $this->assertSame([1, 2, 3, 4], $this->sorted($ids));
        $this->assertSame(2, end($ids), 'memberships come first, followed spaces after them');
    }

    public function testScopeNone()
    {
        $this->becomeUser('User3');
        $this->seedFollow(4, 2);

        $this->assertSame([1, 3, 4], $this->sorted($this->ids(['scope' => 'none'])));

        $this->becomeUser('User2');
        $this->assertSame([], $this->ids(['scope' => 'none', 'ids' => [1, 3, 4]], 3));
    }

    public function testScopeAll()
    {
        $this->becomeUser('User3');

        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids(['scope' => 'all'])));
    }

    public function testScopesWithoutAUserAreEmpty()
    {
        $this->assertSame([], $this->ids(['scope' => 'member'], null));
        $this->assertNotSame([], $this->ids(['scope' => 'none'], null));
    }

    public function testArchived()
    {
        $this->becomeUser('User3');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $this->assertNotContains(3, $this->ids(), 'excluded by default');
        $this->assertNotContains(3, $this->ids(['archived' => false]));
        $this->assertSame([3], $this->ids(['archived' => true]), 'only archived ones');
        $this->assertSame([3], $this->ids(['archived' => '1']));
        $this->assertSame([3], $this->ids(['scope' => 'archived']), "the directory's Archived status");
    }

    public function testArchivedWithAnotherScope()
    {
        $this->becomeUser('User2');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $this->assertSame([3], $this->ids(['scope' => 'member', 'archived' => '1'], 3), 'the archived memberships');
        $this->assertSame([1, 4], $this->sorted($this->ids(['scope' => 'member', 'archived' => '0'], 3)));
        $this->assertSame([3], $this->ids(['scope' => 'archived', 'archived' => '1'], 3));
    }

    public function testRefusesTheArchivedScopeWithoutArchivedSpaces()
    {
        try {
            $this->ids(['scope' => 'archived', 'archived' => '0']);
            $this->fail('the contradiction is refused');
        } catch (ListValidationException $e) {
            $this->assertSame(['archived'], array_keys($e->errors));
        }
    }

    public function testIdsAndExclude()
    {
        $this->becomeUser('User3');

        $this->assertSame([2, 3], $this->sorted($this->ids(['ids' => [2, 3, 5]])), 'ids never widen visibility');
        $this->assertSame([1, 4], $this->sorted($this->ids(['exclude' => '2,3'])));
        $this->assertSame([3], $this->ids(['ids' => [2, 3], 'exclude' => [2]]));
        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids(['ids' => []])), 'no ids, no restriction');
    }

    public function testSortOrders()
    {
        $this->becomeUser('User3');
        foreach ([1 => ['Delta', 3, '2020-01-03'], 2 => ['Alpha', 2, '2020-01-01'], 3 => ['Charlie', 1, '2020-01-04'], 4 => ['Bravo', 2, '2020-01-02']] as $id => [$name, $sortOrder, $createdAt]) {
            Space::updateAll(['name' => $name, 'sort_order' => $sortOrder, 'created_at' => $createdAt], ['id' => $id]);
        }

        $this->assertSame([3, 2, 4, 1], $this->ids(), 'sort order, then name');
        $this->assertSame([3, 2, 4, 1], $this->ids(['sort' => 'default']));
        $this->assertSame([2, 4, 3, 1], $this->ids(['sort' => 'name']));
        $this->assertSame([3, 1, 4, 2], $this->ids(['sort' => 'newest']));
        $this->assertSame([2, 4, 1, 3], $this->ids(['sort' => 'oldest']));
    }

    public function testAModuleSortReplacesTheOrderOfACoreKey()
    {
        $this->becomeUser('User3');
        Event::on(SpaceList::class, SpaceList::EVENT_INIT, static function (ListEvent $event) {
            $event->list->addSort('name', 'By Name', static fn(QueryListBuilder $list) => $list->query()->orderBy(['space.id' => SORT_DESC]));
        });

        $this->assertSame([4, 3, 2, 1], $this->ids(['sort' => 'name']));
        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids(['sort' => 'newest'])), 'the other core sorts are untouched');
    }

    public function testScopeOrderUnlessSortIsGiven()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);
        Space::updateAll(['name' => 'Aardvark'], ['id' => 2]);

        $ids = $this->ids(['scope' => 'mine'], 3);
        $this->assertSame(2, end($ids), "the scope's own order");
        $this->assertSame(2, $this->ids(['scope' => 'mine', 'sort' => 'name'], 3)[0], 'an explicit sort wins');
    }

    public function testRejectsUnknownValuesAndParameters()
    {
        try {
            (new SpaceList())->build([
                'scope' => 'friends',
                'sort' => 'random',
                'purpose' => 'stream',
                'archived' => 'yes',
                'ids' => 'abc',
                'exclude' => '1,x',
                'category' => 'two',
            ], new ListContext($this->user(4)));
            $this->fail('the parameters are refused');
        } catch (ListValidationException $e) {
            $this->assertEqualsCanonicalizing(['scope', 'sort', 'purpose', 'archived', 'ids', 'exclude', 'category'], array_keys($e->errors));
        }
    }

    public function testBuildAppliesTheParameters()
    {
        $this->becomeUser('User3');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $this->assertSame([3], $this->ids(['q' => 'Space', 'scope' => 'none', 'archived' => true, 'sort' => 'name', 'ids' => [2, 3]]));
        $this->assertSame([4], $this->ids(['exclude' => [1, 2], 'sort' => null, 'purpose' => null]), 'archived space 3 is left out by default');
    }

    public function testModulesAddAFilterAndRestrictTheList()
    {
        $this->becomeUser('User3');
        $received = null;
        Event::on(SpaceList::class, SpaceList::EVENT_INIT, static function (ListEvent $event) {
            $event->list->addFilter(new EnumFilter(
                'category',
                values: ['two' => 'Two', 'even' => 'Even'],
                apply: static fn(QueryListBuilder $list, string $category) => $list->query()->andWhere(['space.id' => $category === 'two' ? [2] : [2, 4]]),
                definition: ['label' => 'Category', 'sortOrder' => 250],
            ));
        });
        Event::on(SpaceList::class, SpaceList::EVENT_BUILD, static function (ListEvent $event) use (&$received) {
            $received = $event;
            if ($event->context->purpose === SpaceList::PURPOSE_DIRECTORY) {
                $event->builder->query()->andWhere(['!=', 'space.id', 4]);
            }
        });

        $this->assertSame([2], $this->ids(['category' => 'two', 'purpose' => 'directory']));
        $this->assertSame('directory', $received->context->purpose);
        $this->assertSame('two', $received->value('category'));
        $this->assertSame(4, $received->context->user->id);

        $this->assertSame([2, 4], $this->sorted($this->ids(['category' => 'even'])), 'the restriction only concerns the directory');
        $this->assertNull($received->context->purpose, 'absent purpose is neutral');

        $this->assertSame(['q', 'sort', 'category', 'scope'], array_column((new SpaceList())->definitions(new ListContext()), 'key'));
    }

    public function testDirectoryDefinitions()
    {
        $definitions = (new SpaceList())->definitions(new ListContext(null, SpaceList::PURPOSE_DIRECTORY));

        $this->assertSame(['q', 'sort', 'scope'], array_column($definitions, 'key'));
        $this->assertSame(['text', 'select', 'select'], array_column($definitions, 'type'));

        $definitions = $this->byKey($definitions);
        $this->assertSame('Search', $definitions['q']['label']);
        $this->assertSame('Search Spaces...', $definitions['q']['placeholder']);

        // Every option is a value `GET /api/v2/space` accepts; "all" / "default" is the
        // placeholder, never an option.
        $this->assertSame('Sort', $definitions['sort']['label']);
        $sorts = array_column($definitions['sort']['options'], 'value');
        $this->assertSame(['name', 'newest', 'oldest'], $sorts);
        $this->assertEmpty(array_diff($sorts, SpaceList::SORTS));
        $this->assertSame(['By Name', 'Newest first', 'Oldest first'], array_column($definitions['sort']['options'], 'label'));
        // No placeholder: the select shows its label ("Sort") as the default state.
        $this->assertArrayNotHasKey('placeholder', $definitions['sort']);

        $this->assertSame('Status', $definitions['scope']['label']);
        $this->assertSame(['member', 'following', 'none', 'archived'], array_column($definitions['scope']['options'], 'value'));
        $this->assertSame(['Member', 'Following', 'Neither..nor', 'Archived'], array_column($definitions['scope']['options'], 'label'));
        $this->assertEmpty(array_diff(array_column($definitions['scope']['options'], 'value'), SpaceList::SCOPES), 'Archived is a scope value, not option params');

        foreach ($definitions as $definition) {
            $this->assertSame('primary', $definition['placement']);
            foreach ($definition['options'] ?? [] as $option) {
                $this->assertSame(['value', 'label'], array_keys($option), 'options carry no params');
                $this->assertNotSame('', $option['value'], 'no empty option');
            }
        }
    }
}
