<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\topic;

use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\modules\content\models\ContentTag;
use humhub\modules\space\models\Space;
use humhub\modules\topic\components\TopicList;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The topic list: the topics a caller may see, searched, named or of one container.
 *
 * Fixture ground truth: space 2 (container 5) is public, space 5 (container 12) is private with
 * the members 1 and 2; user 4 (`User3`, profile container 8) is a member of no space, user 3's
 * profile is container 3. The `content_tag` fixture is empty — every topic is created here.
 *
 * @since 1.20
 */
class TopicListTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    /**
     * @var array<string, int> the ids of the topics of {@see self::setUp()} by name
     */
    private array $topics = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'Global Alpha' => null,
            'Private Beta' => 12,   // space 5, private
            'Public Gamma' => 5,    // space 2, public
            'Own Delta' => 8,       // user 4's profile
            'Other Epsilon' => 3,   // user 3's profile
            'Global Beta' => null,
        ] as $name => $container) {
            $topic = new Topic(['name' => $name, 'contentcontainer_id' => $container]);
            $this->assertTrue($topic->save(), $name);
            $this->topics[$name] = (int)$topic->id;
        }

        // Not a topic: a content tag of another module, global.
        $tag = new ContentTag(['name' => 'Global Alpha']);
        $tag->module_id = 'other'; // init() sets it from `$moduleId`
        $this->assertTrue($tag->save(false));
    }

    /**
     * @return string[] the names of the listed topics, in the list's order
     */
    private function names(array $params = [], ?int $userId = 4): array
    {
        $context = new ListContext($userId !== null ? User::findOne(['id' => $userId]) : null);

        return array_map(
            static fn(Topic $topic) => $topic->name,
            (new TopicList())->build($params, $context)->query()->all(),
        );
    }

    private function errors(array $params, ?int $userId = 4): array
    {
        try {
            $this->names($params, $userId);
        } catch (ListValidationException $e) {
            return $e->errors;
        }
        $this->fail('The list was built.');
    }

    public function testListsGlobalTopicsVisibleSpacesTopicsAndTheOwnProfilesInNameOrder(): void
    {
        $this->assertSame(
            ['Global Alpha', 'Global Beta', 'Own Delta', 'Public Gamma'],
            $this->names(),
            'global, a public space\'s and the own profile\'s; neither a private space\'s nor another profile\'s nor a tag of another module',
        );
    }

    public function testAPrivateSpacesTopicsAreVisibleToItsMembers(): void
    {
        $this->assertContains('Private Beta', $this->names([], 2));
        $this->assertNotContains('Other Epsilon', $this->names([], 2));
    }

    public function testAnArchivedSpacesTopicsStayVisible(): void
    {
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 2]);

        $this->assertContains('Public Gamma', $this->names());
        $this->assertSame(['Global Alpha', 'Global Beta', 'Public Gamma'], $this->names(['containerId' => 5]));
    }

    public function testOrdersByNameWithTheIdAsTiebreak(): void
    {
        $twin = new Topic(['name' => 'Public Gamma', 'contentcontainer_id' => 4]); // space 1
        $this->assertTrue($twin->save());

        $ids = array_map(
            static fn(Topic $topic) => (int)$topic->id,
            (new TopicList())->build(['q' => 'Gamma'], new ListContext(User::findOne(['id' => 4])))->query()->all(),
        );
        $this->assertSame([$this->topics['Public Gamma'], (int)$twin->id], $ids);
    }

    public function testSearchesByName(): void
    {
        $this->assertSame(['Global Beta'], $this->names(['q' => 'beta']), 'the private space\'s "Private Beta" stays out');
        $this->assertSame([], $this->names(['q' => 'Epsilon']));
    }

    public function testNamesTopicsByIdWithoutWideningTheList(): void
    {
        $this->assertSame(['Global Beta', 'Own Delta'], $this->names(['ids' => $this->topics['Own Delta'] . ',' . $this->topics['Global Beta']]));
        $this->assertSame([], $this->names(['ids' => (string)$this->topics['Private Beta']]));
    }

    public function testAContainersTopicsComeWithTheGlobalOnes(): void
    {
        $this->assertSame(['Global Alpha', 'Global Beta', 'Public Gamma'], $this->names(['containerId' => '5']));
        $this->assertSame(['Global Alpha', 'Global Beta', 'Own Delta'], $this->names(['containerId' => 8]), 'the own profile');
        $this->assertSame(['Global Alpha', 'Global Beta', 'Private Beta'], $this->names(['containerId' => '12'], 2), 'a member\'s private space');
        $this->assertSame(['Global Beta', 'Private Beta'], $this->names(['containerId' => '12', 'q' => 'Beta'], 2));
    }

    public function testRefusesAContainerTheCallerMayNotRead(): void
    {
        foreach (['12', '3', '9999', 'abc', '0'] as $id) {
            $this->assertSame(['containerId' => ['Container not found.']], $this->errors(['containerId' => $id]), $id);
        }
    }

    public function testNamesAtMostMaxIds(): void
    {
        $this->assertSame(20, TopicList::MAX_IDS);
        $this->assertSame(
            ['ids' => ['At most 20 ids can be named.']],
            $this->errors(['ids' => range(1, TopicList::MAX_IDS + 1)]),
        );
    }

    public function testRefusesAnUnknownParameter(): void
    {
        $this->assertSame(['sort' => ['Unknown parameter.']], $this->errors(['sort' => 'name']));
    }

    public function testAGuestSeesGlobalTopicsAndThoseOfPublicSpaces(): void
    {
        $this->assertSame(['Global Alpha', 'Global Beta', 'Public Gamma'], $this->names([], null));
    }
}
