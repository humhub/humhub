<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\ZendLucenceDriver;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
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

    protected function _before()
    {
        $this->searchDriver = $this->createDriver();

        /* @var Module $module */
        $module = Yii::$app->getModule('content');
        $module->set('search', ['class' => get_class($this->searchDriver)]);

        // Link it to object from Module because it is used in other methods as global,
        // it fixes issue on deleting item from indexing after unpublish a Content
        $this->searchDriver = $module->getSearchDriver();
        $this->searchDriver->purge();

        parent::_before();
    }

    public function testKeywords()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Something Other']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Marabru Leav Test X']))->save();

        $this->assertCount(1, $this->getSearchResultByKeyword('"Marabru" Tes')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('"Marabr" Test')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"Marabru Leav" "Leav Test"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"Something Other"')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('Some Test')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('Some -Test')->results);

        $this->assertCount(1, $this->getSearchResultByKeyword('Marabru Leav')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('Marabru Leav NOT Abcd')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('Marabru -Leav')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('+Marabru +Leav* +Abcd')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('Marabru Leav +Abcd')->results);

        $this->assertCount(1, $this->getSearchResultByKeyword('Something -Marabru')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('Something -Marab')->results);

        // Wildcards (it is applied automatically even if the char `*` is not typed)
        $this->assertCount(1, $this->getSearchResultByKeyword('Marabr*')->results);
    }

    public function testShortKeywords()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Some Other']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Marabru Leav Y Test X']))->save();

        // Short keywords
        $this->assertCount(0, $this->getSearchResultByKeyword('R')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('T')->results);

        // Most search indexes do not index individual letters.

    }

    public function testUrlKeywords()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'https://site.com/home.html']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'https://site.com/category/subcat/page/index.html']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'https://web.net/index.php?page=2&from=string']))->save();

        $this->assertCount(1, $this->getSearchResultByKeyword('"https://site.com/category/subcat/page/index.html"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('https://site.com/category/subcat/page/index.html')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('/site.com/category/subcat/')->results);
        $this->assertCount(2, $this->getSearchResultByKeyword('site.com')->results);
        $this->assertCount(2, $this->getSearchResultByKeyword('"site.com"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('https://web.net/index.php?page=2&from=string')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"https://web.net/index.php?page=2&from=string"')->results);
    }

    private function getSearchRequest(): SearchRequest
    {
        foreach (Content::find()->where(['visibility' => Content::VISIBILITY_PUBLIC])->each() as $content) {
            (new ContentSearchService($content))->delete(false);
            (new ContentSearchService($content))->update(false);
        }

        return new SearchRequest();
    }

    protected function getSearchResultByKeyword(string $keyword): ResultSet
    {
        $request = $this->getSearchRequest();
        $request->keyword = $keyword;
        return $this->searchDriver->search($request);
    }

    public function testFilterDate()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Driver Test1']);
        $post1->content->created_at = '2024-02-01 10:00:00';
        $post1->save();
        $post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Driver Test2']);
        $post2->content->created_at = '2024-01-02 12:00:00';
        $post2->save();
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Driver Test3']);
        $post3->content->created_at = '2023-12-04 22:00:00';
        $post3->save();

        $request = $this->getSearchRequest();
        $request->keyword = 'Driver';

        $request->dateFrom = '2024-02-01';
        $this->assertCount(1, $this->searchDriver->search($request)->results);

        $request->dateFrom = '2024-01-02';
        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->dateFrom = null;
        $request->dateTo = '2024-01-02';
        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->dateFrom = '2023-12-01';
        $request->dateTo = '2024-01-02';
        $this->assertCount(2, $this->searchDriver->search($request)->results);
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
        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->topic = [$topic1->id, $topic2->id];
        $this->assertCount(3, $this->searchDriver->search($request)->results);

        $request->topic = [$topic2->id];
        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->topic = [$topic3->id];
        $this->assertCount(1, $this->searchDriver->search($request)->results);
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

        $this->assertCount(2, $this->searchDriver->search($request)->results);
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

        $this->assertCount(3, $this->searchDriver->search($request)->results);

        // Search by keyword without filter "Author"
        $request = new SearchRequest();
        $request->keyword = 'TestAuthor';

        $this->assertCount(4, $this->searchDriver->search($request)->results);
    }

    public function testFilterBySpace()
    {
        $this->becomeUser('Admin');

        $space1 = Space::findOne(['id' => 1]);
        (new Post($space1, Content::VISIBILITY_PUBLIC, ['message' => 'TestSpace Post1']))->save();

        $space2 = Space::findOne(['id' => 2]);
        (new Post($space2, Content::VISIBILITY_PUBLIC, ['message' => 'TestSpace Post2']))->save();
        (new Post($space2, Content::VISIBILITY_PUBLIC, ['message' => 'TestSpace Post3']))->save();

        $space3 = Space::findOne(['id' => 3]);
        (new Post($space3, Content::VISIBILITY_PUBLIC, ['message' => 'TestSpace Post4']))->save();
        (new Post($space3, Content::VISIBILITY_PUBLIC, ['message' => 'TestSpace Post5']))->save();
        (new Post($space3, Content::VISIBILITY_PUBLIC, ['message' => 'TestSpace Post6']))->save();

        // Search by filter "Space"
        $request = $this->getSearchRequest();
        $request->keyword = 'TestSpace';

        $this->assertCount(6, $this->searchDriver->search($request)->results);

        $request->contentContainerClass = Space::class;
        $request->contentContainer = [$space1->guid];
        $this->assertCount(1, $this->searchDriver->search($request)->results);

        $request->contentContainer = [$space2->guid];
        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->contentContainer = [$space3->guid];
        $this->assertCount(3, $this->searchDriver->search($request)->results);

        $request->contentContainer = [$space1->guid, $space3->guid];
        $this->assertCount(4, $this->searchDriver->search($request)->results);

        $request->contentContainer = [$space2->guid, $space3->guid];
        $result = $this->searchDriver->search($request);
        $this->assertCount(5, $this->searchDriver->search($request)->results);

        $request->contentContainer = [$space1->guid, $space2->guid, $space3->guid];
        $this->assertCount(6, $this->searchDriver->search($request)->results);
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

        $this->assertCount(3, $result->results);
        $this->assertEquals($post2->content->id, $result->results[0]->content->id);
        $this->assertEquals($post3->content->id, $result->results[1]->content->id);
        $this->assertEquals($post1->content->id, $result->results[2]->content->id);

        /*
        $request = $this->getSearchRequest();
        $request->keyword = 'Keyword';
        $request->orderBy = SearchRequest::ORDER_BY_SCORE;
        $result = $this->searchDriver->search($request);

        $this->assertCount(3, $result->results);

        $this->assertEquals($post3->content->id, $result->results[0]->content->id); // +2 Best hit, keyword position, keyword twice
        $this->assertEquals($post1->content->id, $result->results[1]->content->id); // +1 Keyword position
        $this->assertEquals($post2->content->id, $result->results[2]->content->id);
        */
    }

    public function testPagination()
    {
        $space = Space::findOne(['id' => 1]);
        $this->becomeUser('Admin');
        for ($i = 1; $i <= 5; $i++) {
            (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Pagination ' . $i]))->save();
        }

        $request = $this->getSearchRequest();
        $request->keyword = 'Pagination';
        $request->pageSize = 2;

        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->page = 2;
        $this->assertCount(2, $this->searchDriver->search($request)->results);

        $request->page = 3;
        $this->assertCount(1, $this->searchDriver->search($request)->results);
    }

    public function testContentState()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 1]);

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestState Post1']);
        $post1->save();
        $post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestState Post2']);
        $post2->save();
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'TestState Post3']);
        $post3->content->getStateService()->set(Content::STATE_DRAFT);
        $post3->save();

        $this->assertCount(2, $this->getSearchResultByKeyword('TestState')->results);

        $post1->content->getStateService()->draft();
        $this->assertCount(1, $this->getSearchResultByKeyword('TestState')->results);

        $post1->content->getStateService()->publish();
        $post3->content->getStateService()->publish();
        $this->assertCount(3, $this->getSearchResultByKeyword('TestState')->results);
    }

    public function testContentVisibility()
    {
        $this->becomeUser('Admin');

        $space1 = Space::findOne(['id' => 1]);
        $space1->visibility = Space::VISIBILITY_ALL;
        $space1->save();
        ($allPublicPost = new Post($space1, Content::VISIBILITY_PUBLIC, ['message' => 'TestVisibility All Public']))->save();
        ($allPrivatePost = new Post($space1, Content::VISIBILITY_PRIVATE, ['message' => 'TestVisibility All Private']))->save();

        $space2 = Space::findOne(['id' => 2]);
        $space2->visibility = Space::VISIBILITY_REGISTERED_ONLY;
        $space2->save();
        ($userPublicPost = new Post($space2, Content::VISIBILITY_PUBLIC, ['message' => 'TestVisibility User Public']))->save();
        ($userPrivatePost = new Post($space2, Content::VISIBILITY_PRIVATE, ['message' => 'TestVisibility User Private']))->save();

        $space3 = Space::findOne(['id' => 3]);
        $space3->visibility = Space::VISIBILITY_NONE; // Private
        $space3->save();
        ($memberPublicPost = new Post($space3, Content::VISIBILITY_PUBLIC, ['message' => 'TestVisibility Member Public']))->save();
        ($memberPrivatePost = new Post($space3, Content::VISIBILITY_PRIVATE, ['message' => 'TestVisibility Member Private']))->save();

        // Admin
        $this->assertCount(6, $this->getSearchResultByKeyword('TestVisibility')->results);

        // Guest - Disabled guest access
        $this->logout();
        $this->assertCount(0, $this->getSearchResultByKeyword('TestVisibility')->results);

        // Guest - Enabled guest access
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', true);
        $results = $this->getSearchResultByKeyword('TestVisibility')->results;
        $this->assertCount(1, $results);
        $this->assertEquals($allPublicPost->id, $results[0]->object_id);

        // User - not member of the test Spaces
        $this->becomeUser('User3');
        $results = $this->getSearchResultByKeyword('TestVisibility')->results;
        $this->assertCount(1, $results);
        $this->assertEquals($allPublicPost->id, $results[0]->object_id);

        // User - member of the Space 1
        $user = User::findOne(['username' => 'User3']);
        $space1->addMember($user->id);
        $results = $this->getSearchResultByKeyword('TestVisibility')->results;
        $this->assertCount(2, $results);
        $this->assertEquals([
            $allPublicPost->id,
            $allPrivatePost->id,
        ], $this->getObjectIds($results));

        // User - member of the Space 1 and Space 2
        $space2->addMember($user->id);
        $results = $this->getSearchResultByKeyword('TestVisibility')->results;
        $this->assertCount(4, $results);
        $this->assertEquals([
            $allPublicPost->id,
            $allPrivatePost->id,
            $userPublicPost->id,
            $userPrivatePost->id,
        ], $this->getObjectIds($results));

        // User - member of all Spaces
        $space3->addMember($user->id);
        $results = $this->getSearchResultByKeyword('TestVisibility')->results;
        $this->assertCount(6, $results);
        $this->assertEquals([
            $allPublicPost->id,
            $allPrivatePost->id,
            $userPublicPost->id,
            $userPrivatePost->id,
            $memberPublicPost->id,
            $memberPrivatePost->id,
        ], $this->getObjectIds($results));
    }

    private function getObjectIds(array $results): array
    {
        $results = array_map(function (Content $content) {return $content->object_id;}, $results);
        sort($results);
        return $results;
    }
}
