<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\MysqlDriver;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use yii\base\Exception;

class MysqlDriverTest extends AbstractDriverTestSuite
{
    protected function createDriver(): AbstractDriver
    {
        return new MysqlDriver();
    }

    /**
     * @throws Exception
     */
    public function testKeywords()
    {
        parent::testKeywords();

        $space = Space::findOne(['id' => 1]);

        // Bases
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Apple & Banana']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('Apple & Banana')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"Apple & Banana"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('baNA')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('nanaba')->results);

        // Apostrophe and quotation marks
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Here\'s a "sentence"']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Here’s a “sentence”']))->save();
        $this->assertCount(2, $this->getSearchResultByKeyword('Here\'s a sentence')->results);
        $this->assertCount(2, $this->getSearchResultByKeyword('"Here\'s a sentence"')->results);

        // Markdown syntax
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '**bold** _italic_ ~~strike~~ `code` [link](https://target)

- list

| table |
| ----- |']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('bold italic strike code link list table')->results);

        // Currencies
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '111 222€ $333']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('222€')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"222€"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('$333')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"$333"')->results);

        // Small words
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'a ab abc abcd']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('a ab abc abcd')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"a ab abc abcd"')->results);
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '444 55 444']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('444 55 444')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"444 55 444"')->results);

        // Special chars
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '¿fff? ¡ggg! "hhh" \'iiii\' <jjj> : kkk ; lll , mmm . nnn \nnn/ = ooo * ppp + qqq – rrr - (sss) ttt° @ uuu%']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('¿fff?')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"¿fff?"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('¡ggg!')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"¡ggg!"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"hhh"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('\'iiii\'')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"\'iiii\'"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('<jjj>')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"<jjj>"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('kkk ; lll , mmm . nnn')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"kkk ; lll , mmm . nnn"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('\nnn/')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"\nnn/"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('= ooo * ppp + qqq')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"= ooo * ppp + qqq"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('rrr - (sss)')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"rrr - (sss)"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('ttt°')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"ttt°"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('@ uuu%')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"@ uuu%"')->results);

        // Accents
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'çùêàÉÑÏ']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('çùêàÉÑÏ')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"çùêàÉÑÏ"')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('CUEAeni')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('"CUEAeni"')->results);

        // Arabic
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'مُدُنٌ عَرَبَيَّة']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('مُدُنٌ عَرَبَيَّة')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('نٌ عَبَ')->results);

        // Chinese
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '北京旅游']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('北京旅游')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('北旅游')->results);

        // Hebrew
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'ירושלים היסטוריה']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('ירושלים היסטוריה')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('ירושלים הסטוריה')->results);

        // URL
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'https://github.com/humhub/humhub/blob/master/README.md#humhub---putting-people-and-pieces-together']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('https://github.com/humhub/humhub/blob/master/README.md#humhub---putting-people-and-pieces-together')->results);
        $this->assertCount(1, $this->getSearchResultByKeyword('https://github.com/humhub/humhub/blob/master/README.md')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('https://github.com/humhub/blob/master/README.md')->results);

        // Email
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'humhub@humhub.com']))->save();
        $this->assertCount(1, $this->getSearchResultByKeyword('humhub@humhub.com')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('test@humhub.com')->results);
        $this->assertCount(0, $this->getSearchResultByKeyword('humhub@test.com')->results);
    }
}
