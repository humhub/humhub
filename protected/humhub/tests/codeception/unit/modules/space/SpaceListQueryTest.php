<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\space\components\SpaceListQuery;
use humhub\modules\space\components\SpaceListQueryEvent;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\base\InvalidArgumentException;

/**
 * Fixture ground truth: spaces 2, 3 and 4 are public, space 1 is visible to registered users,
 * space 5 is private. Memberships: user 1 (admin) of 1, 3, 4, 5; user 2 (`User1`) of 2, 3, 4,
 * 5; user 3 (`User2`) of 1, 3, 4; user 4 (`User3`) of nothing. There is no follow fixture.
 *
 * @since 1.20
 */
class SpaceListQueryTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Follow::deleteAll(['object_model' => Space::class]);
        ContentContainerBlockedUsers::deleteAll(['user_id' => 4]);
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
     * @return int[] the ids of the query's result, in result order
     */
    private function ids(SpaceListQuery $list): array
    {
        return array_map('intval', $list->getQuery()->select('space.id')->column());
    }

    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }

    public function testListsOnlyVisibleSpaces()
    {
        $this->becomeUser('User3');

        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids(new SpaceListQuery($this->user(4)))));
    }

    public function testDefaultsToTheCurrentUser()
    {
        $this->becomeUser('User3');

        $this->assertNotContains(5, $this->ids(new SpaceListQuery()), 'the private space stays hidden');
    }

    public function testFiltersBlockedSpaces()
    {
        $this->becomeUser('User3');
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => Space::findOne(['id' => 2])->contentcontainer_id,
            'user_id' => 4,
        ])->execute();

        $this->assertNotContains(2, $this->ids(new SpaceListQuery($this->user(4))));
    }

    public function testSearchesNameDescriptionAndTags()
    {
        $this->becomeUser('User3');

        $ids = $this->ids((new SpaceListQuery($this->user(4)))->search('Space 2'));

        $this->assertContains(2, $ids);
        $this->assertNotContains(1, $ids);
    }

    public function testScopeMember()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);

        $this->assertSame([1, 3, 4], $this->sorted($this->ids((new SpaceListQuery($this->user(3)))->scope('member'))));
    }

    public function testScopeFollowing()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);

        $this->assertSame([2], $this->ids((new SpaceListQuery($this->user(3)))->scope('following')));
    }

    public function testScopeMine()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);

        $ids = $this->ids((new SpaceListQuery($this->user(3)))->scope('mine'));

        $this->assertSame([1, 2, 3, 4], $this->sorted($ids));
        $this->assertSame(2, end($ids), 'memberships come first, followed spaces after them');
    }

    public function testScopeNone()
    {
        $this->becomeUser('User3');
        $this->seedFollow(4, 2);

        $this->assertSame([1, 3, 4], $this->sorted($this->ids((new SpaceListQuery($this->user(4)))->scope('none'))));

        $this->becomeUser('User2');
        $this->assertSame([], $this->ids((new SpaceListQuery($this->user(3)))->scope('none')->ids([1, 3, 4])));
    }

    public function testScopeAll()
    {
        $this->becomeUser('User3');

        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids((new SpaceListQuery($this->user(4)))->scope('all'))));
    }

    public function testArchived()
    {
        $this->becomeUser('User3');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $this->assertNotContains(3, $this->ids(new SpaceListQuery($this->user(4))), 'excluded by default');
        $this->assertNotContains(3, $this->ids((new SpaceListQuery($this->user(4)))->archived(false)));
        $this->assertSame([3], $this->ids((new SpaceListQuery($this->user(4)))->archived(true)), 'only archived ones');
    }

    public function testIdsAndExclude()
    {
        $this->becomeUser('User3');

        $this->assertSame([2, 3], $this->sorted($this->ids((new SpaceListQuery($this->user(4)))->ids([2, 3, 5]))), 'ids never widen visibility');
        $this->assertSame([1, 4], $this->sorted($this->ids((new SpaceListQuery($this->user(4)))->exclude([2, 3]))));
        $this->assertSame([3], $this->ids((new SpaceListQuery($this->user(4)))->ids([2, 3])->exclude([2])));
        $this->assertSame([1, 2, 3, 4], $this->sorted($this->ids((new SpaceListQuery($this->user(4)))->ids([]))), 'no ids, no restriction');
    }

    public function testSortOrders()
    {
        $this->becomeUser('User3');
        foreach ([1 => ['Delta', 3, '2020-01-03'], 2 => ['Alpha', 2, '2020-01-01'], 3 => ['Charlie', 1, '2020-01-04'], 4 => ['Bravo', 2, '2020-01-02']] as $id => [$name, $sortOrder, $createdAt]) {
            Space::updateAll(['name' => $name, 'sort_order' => $sortOrder, 'created_at' => $createdAt], ['id' => $id]);
        }
        $list = fn() => new SpaceListQuery($this->user(4));

        $this->assertSame([3, 2, 4, 1], $this->ids($list()), 'sort order, then name');
        $this->assertSame([3, 2, 4, 1], $this->ids($list()->sort('default')));
        $this->assertSame([2, 4, 3, 1], $this->ids($list()->sort('name')));
        $this->assertSame([3, 1, 4, 2], $this->ids($list()->sort('newest')));
        $this->assertSame([2, 4, 1, 3], $this->ids($list()->sort('oldest')));
    }

    public function testScopeOrderUnlessSortIsGiven()
    {
        $this->becomeUser('User2');
        $this->seedFollow(3, 2);
        Space::updateAll(['name' => 'Aardvark'], ['id' => 2]);

        $ids = $this->ids((new SpaceListQuery($this->user(3)))->scope('mine'));
        $this->assertSame(2, end($ids), "the scope's own order");
        $this->assertSame(2, $this->ids((new SpaceListQuery($this->user(3)))->scope('mine')->sort('name'))[0], 'an explicit sort wins');
    }

    public function testRejectsUnknownValues()
    {
        $list = new SpaceListQuery($this->user(4));

        foreach ([fn() => $list->scope('friends'), fn() => $list->sort('random'), fn() => $list->purpose('stream')] as $call) {
            try {
                $call();
                $this->fail('an unknown value is refused');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function testBuildAppliesTheParameters()
    {
        $this->becomeUser('User3');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $query = (new SpaceListQuery($this->user(4)))->build([
            'q' => 'Space',
            'scope' => 'none',
            'archived' => true,
            'sort' => 'name',
            'ids' => [2, 3],
        ]);
        $this->assertSame([3], array_map('intval', $query->select('space.id')->column()));

        $query = (new SpaceListQuery($this->user(4)))->build(['exclude' => [1, 2], 'sort' => null, 'purpose' => null]);
        $this->assertSame([4], array_map('intval', $query->select('space.id')->column()), 'archived space 3 is left out by default');
    }

    public function testEventReceivesParamsAndPurposeAndCanRestrict()
    {
        $this->becomeUser('User3');
        $received = null;
        $handler = function (SpaceListQueryEvent $event) use (&$received) {
            $received = $event;
            if (($event->params['category'] ?? null) === 'only-two') {
                $event->query->andWhere(['space.id' => 2]);
            }
        };

        Event::on(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);
        try {
            $query = (new SpaceListQuery($this->user(4)))->build(['category' => 'only-two', 'purpose' => 'directory']);
        } finally {
            Event::off(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);
        }

        $this->assertInstanceOf(SpaceListQueryEvent::class, $received);
        $this->assertSame('directory', $received->purpose);
        $this->assertSame('only-two', $received->params['category'], 'parameters the core does not know are passed on');
        $this->assertSame(4, $received->user->id);
        $this->assertSame($query, $received->query);
        $this->assertSame([2], array_map('intval', $query->select('space.id')->column()), 'the handler restricted the list');
    }

    public function testEventPurposeIsNullWhenAbsent()
    {
        $this->becomeUser('User3');
        $received = null;
        $handler = function (SpaceListQueryEvent $event) use (&$received) {
            $received = $event;
        };

        Event::on(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);
        try {
            (new SpaceListQuery($this->user(4)))->build([]);
        } finally {
            Event::off(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);
        }

        $this->assertNull($received->purpose);
    }
}
