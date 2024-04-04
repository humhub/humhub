<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\libs\SearchQuery;


class SearchQueryTest extends Unit
{
    public function testTermsWithSigns()
    {
        $query = new SearchQuery('Apple Pie +"Foo" -"Foo bar"');

        $this->assertContains('Apple', $query->orTerms);
        $this->assertContains('Pie', $query->orTerms);
        $this->assertContains('Foo', $query->andTerms);
        $this->assertContains('Foo bar', $query->notTerms);

        $query = new SearchQuery('Apple');
        $this->assertContains('Apple', $query->orTerms);
        $this->assertEmpty($query->notTerms);
        $this->assertEmpty($query->andTerms);

        $query = new SearchQuery('-Apple');
        $this->assertContains('Apple', $query->notTerms);
        $this->assertEmpty($query->orTerms);
        $this->assertEmpty($query->andTerms);

        $query = new SearchQuery('Apple +Banana');
        $this->assertContains('Apple', $query->orTerms);
        $this->assertContains('Banana', $query->andTerms);
    }

    public function testTermsWithWords()
    {
        $query = new SearchQuery('Apple Pie AND "Foo" NOT "Foo bar"');

        $this->assertContains('Apple', $query->orTerms);
        $this->assertContains('Pie', $query->orTerms);
        $this->assertContains('Foo', $query->andTerms);
        $this->assertContains('Foo bar', $query->notTerms);

        $query = new SearchQuery('Apple');
        $this->assertContains('Apple', $query->orTerms);
        $this->assertEmpty($query->notTerms);
        $this->assertEmpty($query->andTerms);

        $query = new SearchQuery('NOT Apple');
        $this->assertContains('Apple', $query->notTerms);
        $this->assertEmpty($query->orTerms);
        $this->assertEmpty($query->andTerms);

        $query = new SearchQuery('Apple OR Banana');
        $this->assertContains('Apple', $query->orTerms);
        $this->assertContains('Banana', $query->orTerms);

        $query = new SearchQuery('Apple AND Banana');
        $this->assertContains('Apple', $query->andTerms);
        $this->assertContains('Banana', $query->andTerms);

        $query = new SearchQuery('Apple AND Banana OR Grape');
        $this->assertContains('Apple', $query->andTerms);
        $this->assertContains('Banana', $query->andTerms);
        $this->assertContains('Grape', $query->orTerms);
    }

    public function testInvalid()
    {
        $query = new SearchQuery('Apple "Pie');
        $this->assertContains('Apple', $query->orTerms);
        $this->assertContains('Pie', $query->orTerms);

        $query = new SearchQuery('');
        $this->assertEmpty($query->orTerms);
        $this->assertEmpty($query->andTerms);
        $this->assertEmpty($query->notTerms);

        $query = new SearchQuery('"');
        $this->assertEmpty($query->orTerms);
        $this->assertEmpty($query->andTerms);
        $this->assertEmpty($query->notTerms);

        $query = new SearchQuery('" ');
        $this->assertEmpty($query->orTerms);
        $this->assertEmpty($query->andTerms);
        $this->assertEmpty($query->notTerms);

        $query = new SearchQuery('+" ');
        $this->assertEmpty($query->orTerms);
        $this->assertEmpty($query->andTerms);
        $this->assertEmpty($query->notTerms);
    }

    public function testTermsWithNumbers()
    {
        $query = new SearchQuery('Quote 2024');
        $this->assertContains('2024', $query->orTerms);
        $this->assertContains('Quote', $query->orTerms);
        $this->assertEmpty($query->andTerms);
        $this->assertEmpty($query->notTerms);

        $query = new SearchQuery('"Quote 2024"');
        $this->assertContains('Quote 2024', $query->orTerms);

    }
}
