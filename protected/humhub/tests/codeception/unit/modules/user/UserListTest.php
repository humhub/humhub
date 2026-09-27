<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\user;

use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListEvent;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\content\models\ContentContainerTagRelation;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\listing\ProfileFieldFilter;
use humhub\modules\user\components\UserList;
use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\fieldtype\CountrySelect;
use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\fieldtype\Text;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * Fixture ground truth: the enabled users are 1 (`Admin`), 2 (`User1`, Peter), 3 (`User2`,
 * Sara), 4 (`User3`, Andreas) and 8 (`AdminNotMember`), all visible to registered users;
 * 5 is disabled, 6 and 7 are unapproved. Every last name is "Tester" but 8's ("User").
 * Groups: 1 Administrator (1, 8), 2 Users (2), 3 Moderators (3 and the unapproved 6), 4 Editors
 * — a sub group of 3 — with nobody; all are shown in the directory. Space 2 has the one member
 * 2, space 5 is private (members 1 and 2). Follows between users are cleared before every test
 * (other suites change them), a test seeds those it needs.
 *
 * @since 1.20
 */
class UserListTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    protected function setUp(): void
    {
        parent::setUp();
        ContentContainerBlockedUsers::deleteAll(['user_id' => 4]);
        Follow::deleteAll(['object_model' => User::class]);
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
    }

    protected function tearDown(): void
    {
        Event::off(UserList::class, UserList::EVENT_INIT);
        Event::off(UserList::class, UserList::EVENT_BUILD);
        parent::tearDown();
    }

    private function seedFollow(int $userId, int $followedId): void
    {
        $follow = new Follow(['user_id' => $userId, 'object_model' => User::class, 'object_id' => $followedId]);
        $this->assertTrue($follow->save(), 'seeded a follow');
    }

    private function user(int $id): User
    {
        return User::findOne(['id' => $id]);
    }

    /**
     * @return int[] the ids of the list, in result order
     */
    private function ids(array $params = [], ?int $userId = 4, ?string $purpose = null): array
    {
        $context = new ListContext($userId !== null ? $this->user($userId) : null, $purpose);

        return array_map('intval', (new UserList())->build($params, $context)->query()->select('user.id')->column());
    }

    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }

    /**
     * @return array<string, string[]> the parameters the list refused, with their messages
     */
    private function refused(array $params, ?int $userId = 4): array
    {
        try {
            $this->ids($params, $userId);
        } catch (ListValidationException $e) {
            return $e->errors;
        }
        $this->fail('the parameters are refused');
    }

    /**
     * @return array the directory's definitions by key
     */
    private function definitions(?int $userId = 4): array
    {
        $definitions = (new UserList())->definitions(new ListContext($userId !== null ? $this->user($userId) : null, UserList::PURPOSE_DIRECTORY));

        return array_column($definitions, null, 'key');
    }

    private function seedRequest(int $from, int $to): void
    {
        Yii::$app->db->createCommand()->insert('user_friendship', [
            'user_id' => $from,
            'friend_user_id' => $to,
            'created_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    /**
     * A directory filter field over a profile column — one of the installation's own columns,
     * or one this creates for a checkbox list, which needs an "Other:" column beside it.
     *
     * Creating the columns happens once per test database. A schema change ends the open
     * transaction the test runs in (MySQL commits implicitly), so a new one is opened right
     * after it — otherwise the rest of the test would be committed instead of rolled back.
     */
    public static function filterField(string $name, string $class, array $config = []): ProfileField
    {
        if ($class === CheckboxList::class && !Profile::columnExists($name)) {
            $db = Yii::$app->db;
            $db->createCommand()->addColumn(Profile::tableName(), $name, 'TEXT')->execute();
            $db->createCommand()->addColumn(Profile::tableName(), CheckboxList::getOtherColumnName($name), 'VARCHAR(255)')->execute();
            $db->getSchema()->refreshTableSchema(Profile::tableName());
            $db->pdo->exec('START TRANSACTION');
        }

        Yii::$app->db->createCommand()->insert(ProfileField::tableName(), [
            'profile_field_category_id' => 1,
            'field_type_class' => $class,
            'field_type_config' => json_encode($config),
            'internal_name' => $name,
            'title' => ucfirst($name),
            'visible' => 1,
            'directory_filter' => 1,
        ])->execute();

        return ProfileField::findOne(['internal_name' => $name]);
    }

    public function testListsAvailableUsersOnly()
    {
        $this->becomeUser('User3');

        $this->assertSame([1, 2, 3, 4, 8], $this->sorted($this->ids()), 'no disabled or unapproved users');
    }

    public function testTheCurrentUsersContext()
    {
        $this->becomeUser('User3');
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => $this->user(2)->contentcontainer_id,
            'user_id' => 4,
        ])->execute();

        $ids = array_map('intval', (new UserList())->build([], ListContext::forCurrentUser())->query()->select('user.id')->column());
        $this->assertNotContains(2, $ids, "the current user's blocks apply");
    }

    public function testNeverListsHiddenUsers()
    {
        User::updateAll(['visibility' => User::VISIBILITY_HIDDEN], ['id' => 3]);

        $this->becomeUser('Admin');
        $this->assertNotContains(3, $this->ids([], 1), 'not even to an administrator');
        $this->assertSame([], $this->ids(['ids' => [3]], 1), 'ids never widen availability');
    }

    public function testAHiddenUserFindsThemselves()
    {
        User::updateAll(['visibility' => User::VISIBILITY_HIDDEN], ['id' => 3]);

        $this->assertContains(3, $this->ids([], 3));
        $this->assertNotContains(3, $this->ids([], 2));
    }

    public function testFiltersUsersWhoBlockedTheCaller()
    {
        $this->becomeUser('User3');
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => $this->user(2)->contentcontainer_id,
            'user_id' => 4,
        ])->execute();

        $this->assertNotContains(2, $this->ids());
    }

    public function testSearches()
    {
        $this->becomeUser('User3');

        $this->assertSame([3], $this->ids(['q' => 'Sara']));
        $this->assertSame([1, 2, 3, 4, 8], $this->sorted($this->ids(['q' => '  '])), 'blank keywords are no restriction');
    }

    public function testScopeFollowingAndFollowers()
    {
        $this->becomeUser('Admin');
        $this->seedFollow(1, 2);

        $this->assertSame([2], $this->ids(['scope' => 'following'], 1), 'user 1 follows user 2');
        $this->assertSame([1], $this->ids(['scope' => 'followers'], 2));
        $this->assertSame([], $this->ids(['scope' => 'following'], 2));
        // A followed space is no followed user.
        $this->assertNotContains(2, $this->ids(['scope' => 'followers'], 1));
        $this->assertSame([1, 2, 3, 4, 8], $this->sorted($this->ids(['scope' => 'all'], 1)));
    }

    public function testScopeFriendsAndPendingFriends()
    {
        $this->becomeUser('User3');
        $this->seedRequest(4, 2);
        $this->seedRequest(2, 4);
        $this->seedRequest(4, 3);
        $this->seedRequest(1, 4);

        $this->assertSame([2], $this->ids(['scope' => 'friends']), 'a request in each direction');
        $this->assertSame([3], $this->ids(['scope' => 'pendingFriends']), 'my unanswered requests, not the ones I received');
    }

    public function testScopesAreOnlyAvailableWithTheirFeature()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 0);
        $this->assertSame(['all', 'following', 'followers'], UserList::availableScopes());
        $this->assertArrayHasKey('scope', $this->refused(['scope' => 'friends']), 'refused while friendship is off');

        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;
        try {
            $this->assertSame(['all', 'friends', 'pendingFriends'], UserList::availableScopes());
            $this->assertArrayHasKey('scope', $this->refused(['scope' => 'following']));
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testScopesAreEmptyWithoutAUser()
    {
        $this->logout();

        $this->assertSame([], $this->ids(['scope' => 'following'], null));
    }

    public function testGroupIncludesSubGroups()
    {
        $this->becomeUser('User3');
        $this->assertTrue($this->addUserToGroup('User3', 4));

        $this->assertSame([3, 4], $this->sorted($this->ids(['groupId' => '3'])), 'Moderators and their Editors, the unapproved user left out');
        $this->assertSame([2], $this->ids(['groupId' => 2]));

        // A member of the group and of a sub group is listed once.
        $this->assertTrue($this->addUserToGroup('User2', 4));
        $this->assertSame([3, 4], $this->sorted($this->ids(['groupId' => 3])));
    }

    public function testGroupMustBeShownInTheDirectory()
    {
        Group::updateAll(['show_at_directory' => 0], ['id' => 3]);

        $this->assertSame(['groupId'], array_keys($this->refused(['groupId' => '3'])));
        $this->assertSame(['groupId'], array_keys($this->refused(['groupId' => '99999'])));

        Group::updateAll(['show_at_directory' => 0]);
        $this->assertSame(['groupId'], array_keys($this->refused(['groupId' => '2'])), 'without a directory group there is no group filter');
    }

    public function testSpaceMembers()
    {
        $this->becomeUser('User3');

        $this->assertSame([2], $this->ids(['spaceId' => '2']));
        $this->assertSame([2], $this->ids(['spaceId' => 2]));
    }

    public function testSpaceMustShowItsMembers()
    {
        $this->becomeUser('User3');

        foreach (['5', '99999', 'abc'] as $spaceId) {
            $this->assertSame(['spaceId'], array_keys($this->refused(['spaceId' => $spaceId])), 'a private or unknown space is refused');
        }

        Space::findOne(['id' => 2])->settings->set('hideMembers', true);
        $this->assertSame(['spaceId'], array_keys($this->refused(['spaceId' => '2'])), 'hidden members');
    }

    public function testFieldsSelect()
    {
        $this->becomeUser('User3');
        self::filterField('gender', Select::class, ['options' => "male=>Male\nfemale=>Female"]);
        Profile::updateAll(['gender' => 'female'], ['user_id' => [2, 3]]);
        Profile::updateAll(['gender' => 'male'], ['user_id' => 4]);

        $this->assertSame([2, 3], $this->sorted($this->ids(['fields' => ['gender' => 'female']])));
        $this->assertSame([2, 3], $this->sorted($this->ids(['fields[gender]' => 'female'])), 'the bracket key itself');
        $this->assertSame([], $this->ids(['fields' => ['gender' => 'fem']]), 'a dropdown matches the whole key');
        $this->assertCount(5, $this->ids(['fields' => ['gender' => '']]), 'an empty value is no restriction');
    }

    public function testFieldsCheckboxListIncludingOther()
    {
        $this->becomeUser('User3');
        self::filterField('test_skills', CheckboxList::class, ['options' => "php=>PHP\njs=>JavaScript", 'allowOther' => 1]);
        Profile::updateAll(['test_skills' => "php\njs"], ['user_id' => 2]);
        Profile::updateAll(['test_skills' => 'js'], ['user_id' => 3]);
        Profile::updateAll(['test_skills' => 'other', 'test_skills_other_selection' => 'Go'], ['user_id' => 4]);

        $this->assertSame([2], $this->ids(['fields' => ['test_skills' => 'php']]));
        $this->assertSame([2, 3], $this->sorted($this->ids(['fields' => ['test_skills' => 'js']])), 'any line of the stored options');
        $this->assertSame([4], $this->ids(['fields' => ['test_skills' => 'Go']]), 'the "Other:" value');

        ProfileField::updateAll(['field_type_config' => json_encode(['options' => "php=>PHP\njs=>JavaScript", 'allowOther' => 0])], ['internal_name' => 'test_skills']);
        $this->assertSame([], $this->ids(['fields' => ['test_skills' => 'Go']]), 'a stale "Other:" value does not match once it is not allowed');
    }

    public function testFieldsTextMatchesAPart()
    {
        $this->becomeUser('User3');
        self::filterField('gender', Select::class, ['options' => "male=>Male\nfemale=>Female"]);
        self::filterField('city', Text::class);
        Profile::updateAll(['city' => 'Hamburg'], ['user_id' => 2]);
        Profile::updateAll(['city' => 'Hamm'], ['user_id' => 3]);

        $this->assertSame([2, 3], $this->sorted($this->ids(['fields' => ['city' => 'Ham']])));
        $this->assertSame([2], $this->ids(['fields' => ['city' => 'burg', 'gender' => '']]));
        $this->assertSame([2, 3], $this->ids(['fields' => ['city' => 'Ham'], 'sort' => 'firstname']), 'the profile joined for the order too');
    }

    public function testFieldsMustBeDirectoryFilters()
    {
        $this->assertSame(['fields[firstname]'], array_keys($this->refused(['fields' => ['firstname' => 'Sara']])));
        $this->assertSame(['fields'], array_keys($this->refused(['fields' => 'Sara'])), 'no field named');
    }

    public function testFilterFieldsAreVisibleFieldsOfASupportedType()
    {
        ProfileField::updateAll(['directory_filter' => 1], ['internal_name' => ['birthday', 'firstname', 'mobile']]);
        ProfileField::updateAll(['visible' => 0], ['internal_name' => 'mobile']);

        $names = array_keys(UserList::filterFields());

        $this->assertContains('firstname', $names);
        $this->assertNotContains('birthday', $names, 'a birthday is no type the directory filters by');
        $this->assertNotContains('mobile', $names, 'an invisible field is not filtered by');

        foreach (['birthday' => '1980', 'mobile' => '555'] as $name => $value) {
            $this->assertSame(['fields[' . $name . ']'], array_keys($this->refused(['fields' => [$name => $value]])), '"' . $name . '" is refused as an unknown field');
        }
    }

    public function testSortOrders()
    {
        $this->becomeUser('User3');
        foreach ([1 => '2020-01-03', 2 => '2020-01-05', 3 => null, 4 => '2020-01-01', 8 => '2020-01-04'] as $id => $lastLogin) {
            User::updateAll(['last_login' => $lastLogin], ['id' => $id]);
        }

        $this->assertSame([1, 8, 4, 2, 3], $this->ids(['sort' => 'firstname']));
        $this->assertSame([1, 4, 2, 3, 8], $this->ids(['sort' => 'lastname']), 'last name, then first name');
        $this->assertSame([2, 8, 1, 4, 3], $this->ids(['sort' => 'lastlogin']), 'most recent first, never logged in last');
        $this->assertSame([2, 8, 1, 4, 3], $this->ids(), "the administrator's default is the last login");
    }

    public function testDefaultSortFollowsTheAdministrator()
    {
        $this->becomeUser('User3');
        foreach ([1 => '2020-01-03', 2 => '2020-01-05', 3 => null, 4 => '2020-01-01', 8 => '2020-01-04'] as $id => $lastLogin) {
            User::updateAll(['last_login' => $lastLogin], ['id' => $id]);
        }

        Yii::$app->settings->set('people.defaultSorting', 'firstname');
        $this->assertSame([1, 8, 4, 2, 3], $this->ids(['sort' => 'default']));
        $this->assertSame([1, 8, 4, 2, 3], $this->ids(), 'no sort is the default');

        // "Default": the prioritised group first, each part by last login.
        Yii::$app->settings->set('people.defaultSorting', '');
        Yii::$app->settings->set('people.defaultSortingGroup', 1);
        $this->assertSame([8, 1, 2, 4, 3], $this->ids(['sort' => 'default']));

        // ... and without a prioritised group simply by last login.
        Yii::$app->settings->set('people.defaultSortingGroup', '');
        $this->assertSame([2, 8, 1, 4, 3], $this->ids(['sort' => 'default']));
    }

    public function testAModuleSortReplacesTheOrderOfACoreKey()
    {
        $this->becomeUser('User3');
        Event::on(UserList::class, UserList::EVENT_INIT, static function (ListEvent $event) {
            $event->list->addSort('lastname', 'Last name', static fn(QueryListBuilder $list) => $list->query()->orderBy(['user.id' => SORT_DESC]));
        });

        $this->assertSame([8, 4, 3, 2, 1], $this->ids(['sort' => 'lastname']));
    }

    public function testIdsAndExclude()
    {
        $this->becomeUser('User3');

        $this->assertSame([2, 3], $this->sorted($this->ids(['ids' => [2, 3, 5]])), 'ids never widen availability');
        $this->assertSame([1, 4, 8], $this->sorted($this->ids(['exclude' => '2,3'])));
        $this->assertSame([3], $this->ids(['ids' => [2, 3], 'exclude' => [2]]));
        $this->assertSame([1, 2, 3, 4, 8], $this->sorted($this->ids(['ids' => []])), 'no ids, no restriction');
    }

    public function testRejectsUnknownValuesAndParameters()
    {
        $this->assertEqualsCanonicalizing(
            ['scope', 'sort', 'purpose', 'groupId', 'spaceId', 'ids', 'exclude', 'fields[nothing]', 'interest'],
            array_keys($this->refused([
                'scope' => 'members',
                'sort' => 'random',
                'purpose' => 'stream',
                'groupId' => '99999',
                'spaceId' => 'x',
                'ids' => 'abc',
                'exclude' => '1,x',
                'fields' => ['nothing' => 'x'],
                'interest' => 'x',
            ])),
        );
    }

    public function testBuildAppliesTheParameters()
    {
        $this->becomeUser('User3');

        // The space first: group 3 comes with a default space, joining it makes User3 a
        // member of space 2.
        $this->assertSame([2], $this->ids(['spaceId' => 2, 'sort' => null, 'purpose' => null]));

        $this->assertTrue($this->addUserToGroup('User3', 3));
        $this->assertSame([4], $this->ids([
            'q' => 'Tester',
            'groupId' => '3',
            'sort' => 'firstname',
            'exclude' => [3],
        ]));
    }

    public function testModulesAddAFilterAndRestrictTheList()
    {
        $this->becomeUser('User3');
        $received = null;
        Event::on(UserList::class, UserList::EVENT_INIT, static function (ListEvent $event) {
            $event->list->addFilter(new EnumFilter(
                'interest',
                values: ['sara' => 'Sara', 'team' => 'Team'],
                apply: static fn(QueryListBuilder $list, string $interest) => $list->query()->andWhere(['user.id' => $interest === 'sara' ? [3] : [2, 3]]),
                definition: ['label' => 'Interest', 'sortOrder' => 250],
            ));
        });
        Event::on(UserList::class, UserList::EVENT_BUILD, static function (ListEvent $event) use (&$received) {
            $received = $event;
            if ($event->context->purpose === UserList::PURPOSE_DIRECTORY) {
                $event->builder->query()->andWhere(['!=', 'user.id', 2]);
            }
        });

        $this->assertSame([3], $this->ids(['interest' => 'team', 'purpose' => 'directory']));
        $this->assertSame('directory', $received->context->purpose);
        $this->assertSame('team', $received->value('interest'));
        $this->assertSame(4, $received->context->user->id);

        $this->assertSame([2, 3], $this->sorted($this->ids(['interest' => 'team', 'purpose' => 'mentioning'])), 'the restriction only concerns the directory');
        $this->assertSame('mentioning', $received->context->purpose);

        $this->ids(['interest' => 'team']);
        $this->assertNull($received->context->purpose, 'absent purpose is neutral');

        ProfileField::updateAll(['directory_filter' => 0]);
        $this->assertSame(['q', 'groupId', 'sort', 'interest', 'scope'], array_keys($this->definitions()));
    }

    public function testDirectoryDefinitions()
    {
        ProfileField::updateAll(['directory_filter' => 0]);
        $definitions = $this->definitions();

        $this->assertSame(['q', 'groupId', 'sort', 'scope'], array_keys($definitions));
        $this->assertSame(['text', 'select', 'select', 'select'], array_column($definitions, 'type'));

        $this->assertSame('Search', $definitions['q']['label']);
        $this->assertSame('Search people...', $definitions['q']['placeholder']);

        $this->assertSame('Group', $definitions['groupId']['label']);
        // By sort order, then name.
        $this->assertSame(['Administrator', 'Editors', 'Moderators', 'Users'], array_column($definitions['groupId']['options'], 'label'));
        $this->assertSame(['1', '4', '3', '2'], array_column($definitions['groupId']['options'], 'value'));

        // Every option is a value `GET /api/v2/user` accepts; "default" is the select's label.
        $this->assertSame('Sort', $definitions['sort']['label']);
        $sorts = array_column($definitions['sort']['options'], 'value');
        $this->assertSame(['firstname', 'lastname', 'lastlogin'], $sorts);
        $this->assertEmpty(array_diff($sorts, UserList::SORTS));
        $this->assertSame(['First name', 'Last name', 'Last login'], array_column($definitions['sort']['options'], 'label'));

        $this->assertSame('Status', $definitions['scope']['label']);
        $this->assertSame(['followers', 'following', 'friends', 'pendingFriends'], array_column($definitions['scope']['options'], 'value'));
        $this->assertSame(['Followers', 'Following', 'Friends', 'Pending Requests'], array_column($definitions['scope']['options'], 'label'));

        foreach ($definitions as $definition) {
            $this->assertSame('primary', $definition['placement']);
            // The selects show their label as the "all" state.
            $this->assertArrayNotHasKey('placeholder', $definition['type'] === 'select' ? $definition : []);
            foreach ($definition['options'] ?? [] as $option) {
                $this->assertSame(['value', 'label'], array_keys($option), 'options carry no params');
                $this->assertNotSame('', $option['value'], 'no empty option');
            }
        }
    }

    public function testGroupsShownInTheDirectoryOnly()
    {
        Group::updateAll(['show_at_directory' => 0], ['id' => [1, 3]]);
        $this->assertSame(['Editors', 'Users'], array_column($this->definitions()['groupId']['options'], 'label'));

        Group::updateAll(['show_at_directory' => 0]);
        $this->assertArrayNotHasKey('groupId', $this->definitions());
    }

    public function testScopeDefinitionOfTheEnabledFeaturesOnly()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        Yii::$app->getModule('friendship')->settings->set('enable', 0);
        $this->assertSame(['followers', 'following'], array_column($this->definitions()['scope']['options'], 'value'));

        $module->disableFollow = true;
        try {
            $this->assertArrayNotHasKey('scope', $this->definitions(), 'no select while only "all" is left');
            $this->assertCount(5, $this->ids(['scope' => 'all']), 'which is still accepted');

            Yii::$app->getModule('friendship')->settings->set('enable', 1);
            $this->assertSame(['friends', 'pendingFriends'], array_column($this->definitions()['scope']['options'], 'value'));
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testOneFilterPerProfileFieldFilter()
    {
        ProfileField::updateAll(['directory_filter' => 0]);
        self::filterField('gender', Select::class, ['options' => "male=>Male\nfemale=>Female"]);
        self::filterField('test_skills', CheckboxList::class, ['options' => "php=>PHP\njs=>JavaScript", 'allowOther' => 1]);
        self::filterField('city', Text::class);
        self::filterField('country', CountrySelect::class);

        $definitions = $this->definitions();

        $gender = $definitions['fields[gender]'];
        $this->assertSame('select', $gender['type']);
        $this->assertSame('Gender', $gender['label']);
        $this->assertSame([['value' => 'male', 'label' => 'Male'], ['value' => 'female', 'label' => 'Female']], $gender['options']);
        $this->assertArrayNotHasKey('optionsUrl', $gender);

        // Values users entered are a single-choice picker searching the field-values endpoint:
        // "Other:" of a checkbox list, a text field, the countries users picked.
        foreach (['test_skills', 'city', 'country'] as $name) {
            $definition = $definitions['fields[' . $name . ']'];
            $this->assertSame('picker', $definition['type']);
            $this->assertArrayNotHasKey('multiple', $definition);
            $this->assertArrayNotHasKey('options', $definition);
            $this->assertStringEndsWith('api/v2/user/field-values?field=' . $name, $definition['optionsUrl']);
        }

        $this->assertSame(
            ['q', 'groupId', 'sort', 'scope', 'fields[gender]', 'fields[test_skills]', 'fields[city]', 'fields[country]'],
            array_keys($definitions),
            'the profile fields after the core filters, in the order of the profile',
        );
    }

    public function testOnlyATextFieldPickerTakesTheTypedText()
    {
        ProfileField::updateAll(['directory_filter' => 0]);
        $options = implode("\n", array_map(static fn(int $i) => 'o' . $i . '=>Option ' . $i, range(1, ProfileFieldFilter::MAX_SELECT_OPTIONS + 1)));
        self::filterField('level', Select::class, ['options' => $options]);
        self::filterField('test_skills', CheckboxList::class, ['options' => "php=>PHP\njs=>JavaScript", 'allowOther' => 1]);
        self::filterField('city', Text::class);
        self::filterField('country', CountrySelect::class);

        $definitions = $this->definitions();

        $this->assertTrue($definitions['fields[city]']['custom'], 'matched by a part of the text');
        foreach (['level', 'test_skills', 'country'] as $name) {
            $this->assertSame('picker', $definitions['fields[' . $name . ']']['type']);
            $this->assertArrayNotHasKey('custom', $definitions['fields[' . $name . ']'], $name . ' matches a key');
        }
    }

    public function testADropdownWithManyOptionsIsAPickerOverThem()
    {
        $options = implode("\n", array_map(static fn(int $i) => 'o' . $i . '=>Option ' . $i, range(1, ProfileFieldFilter::MAX_SELECT_OPTIONS + 1)));
        self::filterField('level', Select::class, ['options' => $options]);

        $definition = $this->definitions()['fields[level]'];
        $this->assertSame('picker', $definition['type']);
        $this->assertCount(ProfileFieldFilter::MAX_SELECT_OPTIONS + 1, $definition['options']);
        $this->assertArrayNotHasKey('optionsUrl', $definition);
    }

    public function testStaticOptionsOfACheckboxListWithoutOther()
    {
        self::filterField('test_skills', CheckboxList::class, ['options' => "php=>PHP\njs=>JavaScript\nother=>Other:"]);

        $this->assertSame(['php', 'js'], array_column($this->definitions()['fields[test_skills]']['options'], 'value'), 'no "Other:" option');
    }

    public function testNoFilterForAnUnsupportedFieldType()
    {
        ProfileField::updateAll(['directory_filter' => 1], ['internal_name' => ['birthday', 'firstname']]);

        $keys = array_keys($this->definitions());

        $this->assertContains('fields[firstname]', $keys);
        $this->assertNotContains('fields[birthday]', $keys);
    }

    public function testFilterValuesOfADropdown()
    {
        $this->becomeUser('User3');
        $field = self::filterField('gender', Select::class, ['options' => "male=>Male\nfemale=>Female"]);

        $this->assertSame(
            [['id' => 'male', 'name' => 'Male'], ['id' => 'female', 'name' => 'Female']],
            (new UserList())->filterValues($field, new ListContext($this->user(4))),
        );
        $this->assertSame(
            [['id' => 'female', 'name' => 'Female']],
            (new UserList())->filterValues($field, new ListContext($this->user(4)), 'FEM'),
            'searched by label, ignoring case',
        );
    }

    public function testFilterValuesOfACheckboxListIncludeOtherValues()
    {
        $this->becomeUser('User3');
        $field = self::filterField('test_skills', CheckboxList::class, ['options' => "php=>PHP\njs=>JavaScript", 'allowOther' => 1]);
        Profile::updateAll(['test_skills' => 'other', 'test_skills_other_selection' => 'Go'], ['user_id' => 4]);
        Profile::updateAll(['test_skills' => 'other', 'test_skills_other_selection' => 'Rust'], ['user_id' => 5]);

        $this->assertSame(
            [['id' => 'php', 'name' => 'PHP'], ['id' => 'js', 'name' => 'JavaScript'], ['id' => 'Go', 'name' => 'Go', 'count' => 1]],
            (new UserList())->filterValues($field, new ListContext($this->user(4))),
            'no "Other:" option itself, and no value of a disabled user',
        );
        $this->assertSame(
            [['id' => 'js', 'name' => 'JavaScript']],
            (new UserList())->filterValues($field, new ListContext($this->user(4)), 'java'),
            'the options by label',
        );
        $this->assertSame(
            [['id' => 'Go', 'name' => 'Go', 'count' => 1]],
            (new UserList())->filterValues($field, new ListContext($this->user(4)), 'go'),
            'the "Other:" values by value',
        );

        ProfileField::updateAll(['field_type_config' => json_encode(['options' => "php=>PHP\njs=>JavaScript", 'allowOther' => 0])], ['internal_name' => 'test_skills']);
        $this->assertCount(2, (new UserList())->filterValues(ProfileField::findOne(['internal_name' => 'test_skills']), new ListContext($this->user(4))));
    }

    public function testFilterValuesOfATextFieldAreWhatUsersEntered()
    {
        $this->becomeUser('User3');
        $field = self::filterField('city', Text::class);
        Profile::updateAll(['city' => 'Hamburg'], ['user_id' => [2, 3]]);
        Profile::updateAll(['city' => 'Berlin'], ['user_id' => 4]);
        Profile::updateAll(['city' => ''], ['user_id' => 1]);
        Profile::updateAll(['city' => 'Secret'], ['user_id' => 6]);

        $this->assertSame(
            [['id' => 'Hamburg', 'name' => 'Hamburg', 'count' => 2], ['id' => 'Berlin', 'name' => 'Berlin', 'count' => 1]],
            (new UserList())->filterValues($field, new ListContext($this->user(4))),
            'distinct, the most frequent first, nothing empty and nothing of a user the list does not show',
        );
        $this->assertSame(
            [['id' => 'Berlin', 'name' => 'Berlin', 'count' => 1]],
            (new UserList())->filterValues($field, new ListContext($this->user(4)), 'erl'),
        );
        $this->assertSame([], (new UserList())->filterValues($field, new ListContext($this->user(4)), 'Secret'));
        $this->assertCount(2, (new UserList())->filterValues($field, new ListContext($this->user(4)), '  '), 'a blank search is none');
    }

    public function testFilterValuesOfACountryFieldAreThePickedCountries()
    {
        $this->becomeUser('User3');
        $field = self::filterField('country', CountrySelect::class);
        Profile::updateAll(['country' => 'DE'], ['user_id' => [2, 3]]);
        Profile::updateAll(['country' => 'AT'], ['user_id' => 4]);

        $this->assertSame(
            [['id' => 'DE', 'name' => 'Germany', 'count' => 2], ['id' => 'AT', 'name' => 'Austria', 'count' => 1]],
            (new UserList())->filterValues($field, new ListContext($this->user(4))),
        );
        $this->assertSame(
            [['id' => 'AT', 'name' => 'Austria', 'count' => 1]],
            (new UserList())->filterValues($field, new ListContext($this->user(4)), 'aus'),
            'searched by the country name, not the stored code',
        );
    }

    public function testTagFilterListsUsersHavingAllTags()
    {
        $this->becomeUser('User3');
        $this->tag(2, ['PHP', 'Go']);
        $this->tag(3, ['PHP']);
        $this->tag(4, ['Go', 'Rust']);

        $this->assertSame([2, 3], $this->sorted($this->ids(['tag' => ['PHP']])));
        $this->assertSame([2], $this->ids(['tag' => ['PHP', 'Go']]), 'all of the tags');
        $this->assertSame([2], $this->ids(['tag' => 'Go,php']), 'comma-separated, ignoring case');
        $this->assertSame([2], $this->ids(['tag' => ['php', 'PHP', 'Go']]), 'a tag named twice is one');
        $this->assertSame([], $this->ids(['tag' => ['PHP', 'Rust']]));
        $this->assertSame([], $this->ids(['tag' => ['Nothing']]));
        $this->assertCount(5, $this->ids(['tag' => []]), 'no tags, no restriction');
    }

    public function testTagFilterNeverWidensAvailability()
    {
        $this->becomeUser('User3');
        $this->tag(2, ['PHP']);
        $this->tag(3, ['PHP']);
        User::updateAll(['visibility' => User::VISIBILITY_HIDDEN], ['id' => 3]);

        $this->assertSame([2], $this->ids(['tag' => ['PHP']]));
    }

    public function testTagFilterRefusesInvalidTags()
    {
        $this->assertSame(['tag'], array_keys($this->refused(['tag' => ['a,b']])), 'no comma inside a tag');
        $this->assertSame(['tag'], array_keys($this->refused(['tag' => [str_repeat('x', UserList::MAX_TAG_LENGTH + 1)]])));
    }

    public function testTagFilterTakesAtMostTwentyTags()
    {
        $tags = array_map(static fn(int $i) => 'tag' . $i, range(1, UserList::MAX_TAGS + 1));

        $this->assertSame(['tag'], array_keys($this->refused(['tag' => $tags])));
        $this->assertSame(['tag'], array_keys($this->refused(['tag' => implode(',', $tags)])), 'comma-separated as well');
        $this->assertSame([], $this->ids(['tag' => array_slice($tags, 0, UserList::MAX_TAGS)]));
    }

    public function testSearchIsAtMost255Characters()
    {
        $this->assertSame(['q'], array_keys($this->refused(['q' => str_repeat('x', UserList::MAX_SEARCH_LENGTH + 1)])));
        $this->assertSame([], $this->ids(['q' => str_repeat('x', UserList::MAX_SEARCH_LENGTH)]));
        $this->assertSame([], $this->ids(['q' => '  ' . str_repeat('x', UserList::MAX_SEARCH_LENGTH) . '  ']), 'trimmed first');
    }

    public function testTagPickerOnlyWhileUsersHaveTags()
    {
        $this->assertArrayNotHasKey('tag', $this->definitions());

        $this->tag(2, ['PHP']);
        $definition = $this->definitions()['tag'];

        $this->assertSame('picker', $definition['type']);
        $this->assertTrue($definition['multiple']);
        $this->assertSame('Tags', $definition['label']);
        $this->assertStringEndsWith('api/v2/user/tags', $definition['optionsUrl']);
        $this->assertSame(['q', 'groupId', 'tag'], array_slice(array_keys($this->definitions()), 0, 3), 'after the group');
    }

    public function testTagValuesAreTheTopTagsOfTheUsersTheListShows()
    {
        $this->becomeUser('User3');
        $this->tag(2, ['PHP', 'Go']);
        $this->tag(3, ['PHP']);
        $this->tag(4, ['Go', 'Rust', 'PHP']);
        $this->tag(5, ['Secret']);
        // A space's tag is no user tag.
        ContentContainerTagRelation::updateByContainer(Space::findOne(['id' => 1]), ['Spacey']);

        $this->assertSame(
            [['id' => 'PHP', 'name' => 'PHP', 'count' => 3], ['id' => 'Go', 'name' => 'Go', 'count' => 2], ['id' => 'Rust', 'name' => 'Rust', 'count' => 1]],
            (new UserList())->tagValues(new ListContext($this->user(4))),
            'the most frequent first, nothing of a user the list does not show',
        );
        $this->assertSame(
            [['id' => 'Go', 'name' => 'Go', 'count' => 2]],
            (new UserList())->tagValues(new ListContext($this->user(4)), 'g'),
        );
        $this->assertSame([], (new UserList())->tagValues(new ListContext($this->user(4)), 'Spacey'));
    }

    public function testSuggestionsFollowTheRestrictionsOfModules()
    {
        $this->becomeUser('User3');
        $field = self::filterField('city', Text::class);
        Profile::updateAll(['city' => 'Hamburg'], ['user_id' => 2]);
        Profile::updateAll(['city' => 'Hidden town'], ['user_id' => 3]);
        $this->tag(2, ['PHP']);
        $this->tag(3, ['Secret']);
        // A module hides user 3 from the directory, and only from it.
        Event::on(UserList::class, UserList::EVENT_BUILD, static function (ListEvent $event) {
            if ($event->context->purpose === UserList::PURPOSE_DIRECTORY) {
                $event->builder->query()->andWhere(['!=', 'user.id', 3]);
            }
        });
        $directory = new ListContext($this->user(4), UserList::PURPOSE_DIRECTORY);

        $this->assertSame(['PHP'], array_column((new UserList())->tagValues($directory), 'id'));
        $this->assertSame([], (new UserList())->tagValues($directory, 'Secret'));
        $this->assertSame(['Hamburg'], array_column((new UserList())->filterValues($field, $directory), 'id'));
        $this->assertSame([], (new UserList())->filterValues($field, $directory, 'Hidden'));

        $this->assertSame(['PHP', 'Secret'], array_column((new UserList())->tagValues(new ListContext($this->user(4))), 'id'), 'the restriction only concerns the directory');

        // Only the hidden user has a tag: no tag picker in the directory.
        ContentContainerTagRelation::deleteByContainer($this->user(2));
        $this->assertArrayNotHasKey('tag', $this->definitions());
        $this->assertContains('tag', array_column((new UserList())->definitions(new ListContext($this->user(4))), 'key'));
    }

    public function testSuggestionsAreSortedByFrequencyWhateverTheDirectoryOrder()
    {
        $this->becomeUser('User3');
        Yii::$app->settings->set('people.defaultSorting', UserList::SORT_FIRSTNAME);
        $field = self::filterField('city', Text::class);
        Profile::updateAll(['city' => 'Hamburg'], ['user_id' => [2, 3]]);
        Profile::updateAll(['city' => 'Berlin'], ['user_id' => 4]);

        try {
            $this->assertSame(['Hamburg', 'Berlin'], array_column((new UserList())->filterValues($field, new ListContext($this->user(4), UserList::PURPOSE_DIRECTORY)), 'id'));
        } finally {
            Yii::$app->settings->delete('people.defaultSorting');
        }
    }

    /**
     * @param string[] $tags
     */
    private function tag(int $userId, array $tags): void
    {
        ContentContainerTagRelation::updateByContainer($this->user($userId), $tags);
    }
}
