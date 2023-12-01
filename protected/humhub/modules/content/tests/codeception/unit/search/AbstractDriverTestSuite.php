<?php

namespace humhub\modules\content\tests\codeception\unit\search;


use humhub\modules\content\models\Content;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\content\Module;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class AbstractDriverTestSuite extends HumHubDbTestCase
{

    protected ?AbstractDriver $searchDriver = null;

    public function _before()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('content');
        $this->searchDriver = $module->getSearchDriver();
        $this->searchDriver->purge();

        parent::_before();
    }

    public function testOrderBy()
    {

        $this->assertTrue(true);
    }

    public function testFilterContentType()
    {
        $space = Space::findOne(['id' => 1]);

        $this->becomeUser('Admin');

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test0']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test1']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test Test2']))->save();

        $request = new SearchRequest();
        $request->keyword = 'Test';

        $result = $this->searchDriver->search($request);

        $this->assertEquals(count($result->results), 3);
    }

    public function testFilterAuthor()
    {
        $this->assertTrue(true);
    }

}
