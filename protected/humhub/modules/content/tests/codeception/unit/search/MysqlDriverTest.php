<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\MysqlDriver;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;

class MysqlDriverTest extends AbstractDriverTestSuite
{
    protected function createDriver(): AbstractDriver
    {
        return new MysqlDriver();
    }

    public function testKeywords()
    {
        parent::testKeywords();

        $space = Space::findOne(['id' => 1]);

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Apple & Banana']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('Apple & Banana')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"Apple & Banana"')->results);

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Here\'s a sentence']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('Here\'s a sentence')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"Here\'s a sentence"')->results);

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'a ab bc']))->save();
        $this->assertCount(0, $this->getSearchResultByKeyword('a ab bc')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('"a ab bc"')->results);

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Test with `code1234` in text']))->save();
        $this->assertCount(0, $this->getSearchResultByKeyword('with code1234')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"with code1234"')->results);
    }
}
