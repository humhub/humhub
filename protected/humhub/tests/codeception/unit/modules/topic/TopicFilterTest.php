<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\topic;

use humhub\components\listing\FilterableList;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\topic\assets\TopicVueAsset;
use humhub\modules\topic\components\listing\TopicFilter;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The topic filter: one or several topic ids the caller may see, parsed, applied and presented.
 *
 * Fixture ground truth (see {@see TopicListTest}): space 2 (container 5) is public, space 5
 * (container 12) is private with the members 1 and 2; user 4 (`User3`, profile container 8) is
 * a member of no space. The `content_tag` fixture is empty — every topic is created here.
 *
 * @since 1.20
 */
class TopicFilterTest extends HumHubDbTestCase
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
            'Global' => null,
            'Private' => 12,   // space 5, private
            'Public' => 5,     // space 2, public
            'Own' => 8,        // user 4's profile
        ] as $name => $container) {
            $topic = new Topic(['name' => $name, 'contentcontainer_id' => $container]);
            $this->assertTrue($topic->save(), $name);
            $this->topics[$name] = (int)$topic->id;
        }
    }

    private function context(?int $userId = 4): ListContext
    {
        return new ListContext($userId !== null ? User::findOne(['id' => $userId]) : null);
    }

    private function parse(mixed $raw, ?int $userId = 4, ?TopicFilter $filter = null): FilterValue
    {
        $filter ??= new TopicFilter();

        return $filter->parse($raw === null ? [] : [$filter->key() => $raw], $this->context($userId));
    }

    private function assertTopicNotFound(FilterValue $value, string $message = ''): void
    {
        $this->assertFalse($value->present, $message);
        $this->assertSame(['topicId' => ['Topic not found.']], $value->errors, $message);
    }

    private function ids(string ...$names): array
    {
        $ids = array_map(fn(string $name) => $this->topics[$name], $names);
        sort($ids);

        return $ids;
    }

    public function testParsesTheIdOfATopicTheCallerMaySee(): void
    {
        $this->assertSame('topicId', (new TopicFilter())->key());

        $value = $this->parse((string)$this->topics['Public']);
        $this->assertTrue($value->present);
        $this->assertSame([$this->topics['Public']], $value->value, 'always a list');
        $this->assertSame([$this->topics['Global']], $this->parse($this->topics['Global'])->value, 'typed from PHP');
    }

    public function testParsesSeveralIds(): void
    {
        $expected = $this->ids('Global', 'Public', 'Own');

        $this->assertSame($expected, $this->parse([(string)$this->topics['Own'], (string)$this->topics['Global'], (string)$this->topics['Public']])->value, 'repeated, sorted');
        $this->assertSame($expected, $this->parse(implode(',', [$this->topics['Public'], $this->topics['Own'], $this->topics['Global'], $this->topics['Own']]))->value, 'comma-separated, unique');
    }

    public function testRefusesATopicOfAPrivateSpaceTheCallerIsNotAMemberOf(): void
    {
        $this->assertTopicNotFound($this->parse((string)$this->topics['Private']));
        $this->assertSame([$this->topics['Private']], $this->parse((string)$this->topics['Private'], 2)->value, 'a member still may');
    }

    public function testRefusesAMixedVisibleAndHiddenSet(): void
    {
        $this->assertTopicNotFound($this->parse($this->topics['Public'] . ',' . $this->topics['Private']));
    }

    public function testRefusesAnUnknownTopicWithTheSameMessage(): void
    {
        $this->assertTopicNotFound($this->parse('99999'));
    }

    public function testRefusesWhatIsNoId(): void
    {
        foreach (['abc', '0', '-2', '1.5'] as $raw) {
            $this->assertSame(['topicId' => ['topicId must be an integer.']], $this->parse($raw)->errors, var_export($raw, true));
        }
    }

    public function testRefusesMoreThanThePickersMaxIds(): void
    {
        $this->assertSame(
            ['topicId' => ['At most 20 ids can be named.']],
            $this->parse(range(1, TopicFilter::MAX_IDS + 1))->errors,
        );
        $this->assertSame(20, TopicFilter::MAX_IDS);
    }

    public function testIsAbsentWithoutAValue(): void
    {
        foreach ([null, '', []] as $raw) {
            $value = $this->parse($raw);
            $this->assertFalse($value->present, var_export($raw, true));
            $this->assertTrue($value->isValid());
        }
    }

    public function testWithAContainerTakesItsTopicsAndTheGlobalOnesOnly(): void
    {
        $filter = new TopicFilter(containerId: 5);

        $this->assertSame($this->ids('Global', 'Public'), $this->parse($this->topics['Public'] . ',' . $this->topics['Global'], 4, $filter)->value);
        $this->assertTopicNotFound($this->parse((string)$this->topics['Own'], 4, $filter), 'a topic of another container');
        $this->assertTopicNotFound(
            $this->parse((string)$this->topics['Global'], 4, new TopicFilter(containerId: 12)),
            'a container the caller may not read lists nothing',
        );
    }

    /**
     * Without a `containerId`, the context's container (the space a list lives in) is the one:
     * its topics and the global ones, suggested and accepted alike. A `containerId` wins.
     */
    public function testWithoutAContainerIdTakesTheContainerOfTheContext(): void
    {
        $filter = new TopicFilter(definition: []);
        $inSpace = new ListContext(User::findOne(['id' => 4]), null, Space::findOne(['id' => 2]));

        $this->assertSame(
            $this->ids('Global', 'Public'),
            $filter->parse(['topicId' => $this->topics['Public'] . ',' . $this->topics['Global']], $inSpace)->value,
        );
        $this->assertTopicNotFound($filter->parse(['topicId' => (string)$this->topics['Own']], $inSpace), 'a topic of another container');
        $this->assertSame(['containerId' => 5], $filter->definition($inSpace)->props);

        $explicit = new TopicFilter(definition: [], containerId: 8);
        $this->assertSame($this->ids('Own'), $explicit->parse(['topicId' => (string)$this->topics['Own']], $inSpace)->value);
        $this->assertSame(['containerId' => 8], $explicit->definition($inSpace)->props);

        // Without a container in the context: every topic the caller may see, no container prop.
        $this->assertSame($this->ids('Own'), $filter->parse(['topicId' => (string)$this->topics['Own']], $this->context())->value);
        $this->assertArrayNotHasKey('containerId', $filter->definition($this->context())->props ?? []);
    }

    public function testNarrowsAContentQueryToContentWithAnyOfTheTopics(): void
    {
        $this->becomeUser('User1');
        $space = Space::findOne(['id' => 2]);

        $post = function (string $message, array $topics) use ($space): int {
            $post = new Post(['message' => $message]);
            $post->content->visibility = Content::VISIBILITY_PUBLIC;
            $post->content->setContainer($space);
            $this->assertTrue($post->save(), $message);
            Topic::attach($post->content, $topics);

            return (int)$post->content->id;
        };
        $public = $post('Public only', [$this->topics['Public']]);
        $global = $post('Global only', [$this->topics['Global']]);
        $both = $post('Both', [$this->topics['Public'], $this->topics['Global']]);
        $post('None', []);
        $other = new Topic(['name' => 'Other', 'contentcontainer_id' => 5]);
        $this->assertTrue($other->save());
        $post('Other', [$other->id]);

        $contentIds = function (array $topicIds): array {
            $list = new QueryListBuilder(Content::find());
            (new TopicFilter())->apply($list, FilterValue::of($topicIds), $this->context());
            $ids = array_map('intval', $list->query()->select('content.id')->column());
            sort($ids);

            return $ids;
        };

        $this->assertSame([$public, $both], $contentIds([$this->topics['Public']]));
        $this->assertSame([$public, $global, $both], $contentIds($this->ids('Public', 'Global')), 'any of the topics');
    }

    public function testNarrowsByItsCallback(): void
    {
        $applied = null;
        $filter = new TopicFilter('topics', apply: static function (ListBuilder $list, array $ids) use (&$applied) {
            $applied = $ids;
        });
        $list = new QueryListBuilder(Content::find());

        $filter->apply($list, FilterValue::of([2, 3]), $this->context());

        $this->assertSame([2, 3], $applied);
        $this->assertNull($list->query()->where, 'the callback replaces the default');
    }

    public function testIsATopicTypeDefinitionInAList(): void
    {
        $this->assertSame([
            'key' => 'topicId',
            'type' => 'topic',
            'label' => 'Topic',
            'multiple' => true,
            'placement' => 'primary',
            'props' => ['containerId' => 5],
        ], (new TopicFilterTestList(5))->definitions($this->context())[0]);

        $this->assertSame([
            'key' => 'topicId',
            'type' => 'topic',
            'label' => 'Topic',
            'multiple' => true,
            'placement' => 'primary',
        ], (new TopicFilterTestList(null))->definitions($this->context())[0], 'no props without a container');

        $this->assertNull((new TopicFilter())->definition($this->context()), 'no definition without one');
    }

    public function testMergesTheContainerIntoTheDefinitionsProps(): void
    {
        $definition = (new TopicFilter(definition: ['props' => ['extra' => 1]], containerId: 5))->definition($this->context());

        $this->assertSame(['extra' => 1, 'containerId' => 5], $definition->props);
    }

    public function testDefinitionCannotOverrideTypeOrMultiple(): void
    {
        $definition = (new TopicFilter(definition: ['type' => 'text', 'multiple' => false, 'label' => 'Tag']))->definition($this->context());

        $this->assertSame('topic', $definition->type);
        $this->assertTrue($definition->multiple);
        $this->assertSame('Tag', $definition->label);
    }

    public function testIsNotAvailableToGuests(): void
    {
        $this->assertTrue((new TopicFilter())->isAvailable($this->context()));
        $this->assertFalse((new TopicFilter())->isAvailable($this->context(null)));
        $this->assertSame([], (new TopicFilterTestList(null))->definitions($this->context(null)), 'no definition for a guest');

        $this->expectException(ListValidationException::class);
        (new TopicFilterTestList(null))->build(['topicId' => (string)$this->topics['Global']], $this->context(null));
    }

    public function testTheControlShipsInTheTopicModulesVueBundle(): void
    {
        $bundle = new TopicVueAsset();

        $this->assertSame(Yii::getAlias('@topic/resources'), $bundle->sourcePath);
        $this->assertSame(['js/humhub.topic.vue.js'], $bundle->js);
        $this->assertFileExists(Yii::getAlias('@topic/resources/js/humhub.topic.vue.js'));
    }
}

/**
 * A list with one topic filter, of a container or not.
 */
class TopicFilterTestList extends FilterableList
{
    public function __construct(private readonly ?int $containerId)
    {
        parent::__construct();
    }

    protected function filters(): array
    {
        return [
            new TopicFilter(definition: ['sortOrder' => 110], containerId: $this->containerId),
        ];
    }

    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new QueryListBuilder(Content::find());
    }
}
