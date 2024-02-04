<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

abstract class AbstractDriverTestSuite extends HumHubDbTestCase
{

    protected ?AbstractDriver $searchDriver = null;

    abstract protected function createDriver(): AbstractDriver;

    public function _before()
    {
        $this->searchDriver = $this->createDriver();

        /** @var Module $module */
        $module = Yii::$app->getModule('content');

        $module->setComponents([
            'search' => [
                'class' => get_class($this->searchDriver)
            ]
        ]);

        $this->searchDriver->purge();

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


    /**
     * @skip Not possible on MySQLDriver
     */
    public function testShortKeywords()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Some Other']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Marabru Leav Test X']))->save();

        // Short keywords
        $this->assertEquals(0, count($this->getSearchResultByKeyword('M')->results));
        $this->assertEquals(1, count($this->getSearchResultByKeyword('X')->results));
    }


    private function getSearchResultByKeyword(string $keyword): ResultSet
    {
        $request = new SearchRequest();
        $request->keyword = $keyword;
        return $this->searchDriver->search($request);
    }

    public function testFilterCnontentType()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test0']))->save();
        (new TestContent($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test1']))->save();
        (new TestContent($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test2']))->save();

        $request = new SearchRequest();
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

        // Search by filter

        $request = new SearchRequest();
        $request->keyword = 'TestAuthor';
        $request->author = User::findOne(['username' => 'User2']);

        $result = $this->searchDriver->search($request);

        $this->assertEquals(3, count($result->results));

        // Search by keyword
        $request = new SearchRequest();
        $request->keyword = 'Sara Tester';
        $result = $this->searchDriver->search($request);

        $this->assertEquals(3, count($result->results));
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

        $this->searchDriver->purge();
        $this->searchDriver->update($post1->content);
        $this->searchDriver->update($post2->content);
        $this->searchDriver->update($post3->content);

        $request = new SearchRequest();
        $request->keyword = 'Keyword';
        $request->orderBy = SearchRequest::ORDER_BY_CREATION_DATE;
        $result = $this->searchDriver->search($request);

        $this->assertEquals(3, count($result->results));
        $this->assertEquals($post2->content->id, $result->results[0]->content->id);
        $this->assertEquals($post3->content->id, $result->results[1]->content->id);
        $this->assertEquals($post1->content->id, $result->results[2]->content->id);

        $request = new SearchRequest();
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
