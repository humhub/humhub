<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\MysqlDriver;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

abstract class AbstractDriverTestSuite extends HumHubDbTestCase
{

    protected ?AbstractDriver $searchDriver = null;

    abstract protected function createDriver(): AbstractDriver;

    abstract protected function updateNewAddedContents(): void;

    protected function _before()
    {
        $this->searchDriver = $this->createDriver();

        /** @var Module $module */
        $module = Yii::$app->getModule('content');

        $module->set('search', ['class' => get_class($this->searchDriver)]);

        parent::_before();
    }

    public function testKeywords()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Some Other']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Marabru Leav Test X']))->save();

        // Test Multiple AND Keywords
        $this->assertEquals(1, count($this->getSearchResultByKeyword('Marabru')->results));
        $this->assertEquals(0, count($this->getSearchResultByKeyword('Marabru Leav Abcd')->results));

        // Wildcards
        $this->assertEquals(1, count($this->getSearchResultByKeyword('Marabr*')->results));
    }

    public function testShortKeywords()
    {
        if ($this->searchDriver instanceof MysqlDriver) {
            // Not possible on MySQLDriver
            return;
        }

        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Some Other']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Marabru Leav Test X']))->save();

        // Short keywords
        $this->assertEquals(0, count($this->getSearchResultByKeyword('M')->results));
        $this->assertEquals(1, count($this->getSearchResultByKeyword('X')->results));
    }

    private function getSearchRequest(): SearchRequest
    {
        $this->updateNewAddedContents();

        return new SearchRequest();
    }

    private function getSearchResultByKeyword(string $keyword): ResultSet
    {
        $request = $this->getSearchRequest();
        $request->keyword = $keyword;
        return $this->searchDriver->search($request);
    }

    public function testFilterDate()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test1']);
        $post1->content->created_at = '2024-02-01 10:00:00';
        $post1->save();
        $post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test2']);
        $post2->content->created_at = '2024-01-02 12:00:00';
        $post2->save();
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test3']);
        $post3->content->created_at = '2023-12-04 22:00:00';
        $post3->save();

        $request = $this->getSearchRequest();
        $request->keyword = 'Test';

        $request->dateFrom = '2024-02-01';
        $result = $this->searchDriver->search($request);
        $this->assertEquals(1, count($result->results));

        $request->dateFrom = '2024-01-02';
        $result = $this->searchDriver->search($request);
        $this->assertEquals(2, count($result->results));

        $request->dateFrom = null;
        $request->dateTo = '2024-01-02';
        $result = $this->searchDriver->search($request);
        $this->assertEquals(2, count($result->results));

        $request->dateFrom = '2023-12-01';
        $request->dateTo = '2024-01-02';
        $result = $this->searchDriver->search($request);
        $this->assertEquals(2, count($result->results));
    }

    public function testFilterTopics()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test1']);
        if ($post1->save()) {
            Topic::attach($post1->content, ['_add:red']);
        }
        $topic1 = Topic::findOne(['name' => 'red']);
        $post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test2']);
        if ($post2->save()) {
            Topic::attach($post2->content, [$topic1, '_add:green']);
        }
        $topic2 = Topic::findOne(['name' => 'green']);
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test3']);
        if ($post3->save()) {
            Topic::attach($post3->content, [$topic2, '_add:blue']);
        }
        $topic3 = Topic::findOne(['name' => 'blue']);

        $request = $this->getSearchRequest();
        $request->keyword = 'Test';

        $request->topic = [$topic1->id];
        $result = $this->searchDriver->search($request);
        $this->assertEquals(2, count($result->results));

        $request->topic = [$topic1->id, $topic2->id];
        $result = $this->searchDriver->search($request);
        $this->assertEquals(3, count($result->results));

        $request->topic = [$topic2->id];
        $result = $this->searchDriver->search($request);
        $this->assertEquals(2, count($result->results));

        $request->topic = [$topic3->id];
        $result = $this->searchDriver->search($request);
        $this->assertEquals(1, count($result->results));
    }

    public function testFilterContentType()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test0']))->save();
        (new TestContent($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test1']))->save();
        (new TestContent($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test2']))->save();

        $request = $this->getSearchRequest();
        $request->keyword = 'Test';
        $request->contentType = TestContent::class;

        $result = $this->searchDriver->search($request);

        $this->assertEquals(2, count($result->results));
    }

    public function testFilterAuthor()
    {
        $space = Space::findOne(['id' => 1]);

        $this->becomeUser('Admin');

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestAuthor Test2']))->save();

        $this->becomeUser('User2');

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestAuthor Test3']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestAuthor Test4']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestAuthor Test5']))->save();

        // Search by filter "Author"
        $request = $this->getSearchRequest();
        $request->keyword = 'TestAuthor';
        $request->author = [User::findOne(['username' => 'User2'])->guid];

        $result = $this->searchDriver->search($request);

        $this->assertEquals(3, count($result->results));

        // Search by keyword without filter "Author"
        $request = new SearchRequest();
        $request->keyword = 'TestAuthor';
        $result = $this->searchDriver->search($request);

        $this->assertEquals(4, count($result->results));
    }

    public function testOrderBy()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('User2');

        $post1 = (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Keyword Abcd Test3']));
        $post1->save();
        $post1->content->updateAttributes(['created_at' => '2023-01-01 12:00:00']);

        $post2 = (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Bla Keyword Test3']));
        $post2->save();
        $post2->content->updateAttributes(['created_at' => '2023-03-01 13:00:00']);

        $post3 = (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Keyword Keyword Test3']));
        $post3->save();
        $post3->content->updateAttributes(['created_at' => '2023-02-01 11:00:00']);

        $request = $this->getSearchRequest();
        $request->keyword = 'Keyword';
        $request->orderBy = SearchRequest::ORDER_BY_CREATION_DATE;
        $result = $this->searchDriver->search($request);

        $this->assertEquals(3, count($result->results));
        $this->assertEquals($post2->content->id, $result->results[0]->content->id);
        $this->assertEquals($post3->content->id, $result->results[1]->content->id);
        $this->assertEquals($post1->content->id, $result->results[2]->content->id);

        $request = $this->getSearchRequest();
        $request->keyword = 'Keyword';
        $request->orderBy = SearchRequest::ORDER_BY_SCORE;
        $result = $this->searchDriver->search($request);

        $this->assertEquals(3, count($result->results));

        $this->assertEquals($post3->content->id, $result->results[0]->content->id); // +2 Best hit, keyword position, keyword twice
        $this->assertEquals($post1->content->id, $result->results[1]->content->id); // +1 Keyword position
        $this->assertEquals($post2->content->id, $result->results[2]->content->id);
    }


    public function testFilterBySpace()
    {

        $this->assertTrue(true);
    }
}