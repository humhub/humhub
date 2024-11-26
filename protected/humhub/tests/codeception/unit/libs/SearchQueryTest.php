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
        $this->assertEquals(['Apple*', 'Pie*', 'Foo'], $query->terms);
        $this->assertEquals(['Foo bar'], $query->notTerms);

        $query = new SearchQuery('Apple');
        $this->assertEquals(['Apple*'], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('-Apple');
        $this->assertEquals([], $query->terms);
        $this->assertEquals(['Apple*'], $query->notTerms);

        $query = new SearchQuery('Apple +Banana');
        $this->assertEquals(['Apple*', 'Banana*'], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('----Apple +++++Banana "---Orange" "++++Peach" ----"Apple pie" +++"Foo bar"');
        $this->assertEquals(['Banana*', 'Orange', 'Peach', 'Foo bar'], $query->terms);
        $this->assertEquals(['Apple*', 'Apple pie'], $query->notTerms);

        $query = new SearchQuery('"Apple Banana" +"Pie" -"Orange" -"Apple pie" AND "Foo bar" NOT "Banana tree"');
        $this->assertEquals(['Apple Banana', 'Pie', 'AND*', 'Foo bar'], $query->terms);
        $this->assertEquals(['Orange', 'Apple pie', 'Banana tree'], $query->notTerms);
    }

    public function testTermsWithWords()
    {
        $query = new SearchQuery('Apple Pie AND "Foo" NOT "Foo bar"');
        $this->assertEquals(['Apple*', 'Pie*', 'AND*', 'Foo'], $query->terms);
        $this->assertEquals(['Foo bar'], $query->notTerms);

        $query = new SearchQuery('Apple');
        $this->assertEquals(['Apple*'], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('NOT Apple');
        $this->assertEquals([], $query->terms);
        $this->assertEquals(['Apple*'], $query->notTerms);

        $query = new SearchQuery('Apple OR Banana');
        $this->assertEquals(['Apple*', 'OR*', 'Banana*'], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('Apple NOT Banana');
        $this->assertEquals(['Apple*'], $query->terms);
        $this->assertEquals(['Banana*'], $query->notTerms);

        $query = new SearchQuery('Apple AND Banana OR Grape NOT Orange');
        $this->assertEquals(['Apple*', 'AND*', 'Banana*', 'OR*', 'Grape*'], $query->terms);
        $this->assertEquals(['Orange*'], $query->notTerms);
    }

    public function testInvalid()
    {
        $query = new SearchQuery('Apple "Pie');
        $this->assertEquals(['Apple*', 'Pie*'], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('');
        $this->assertEquals([], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('"');
        $this->assertEquals([], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('" ');
        $this->assertEquals([], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('+" ');
        $this->assertEquals([], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('+"*"');
        $this->assertEquals([], $query->terms);
        $this->assertEquals([], $query->notTerms);
    }

    public function testTermsWithNumbers()
    {
        $query = new SearchQuery('Quote 2024');
        $this->assertEquals(['Quote*', '2024*'], $query->terms);
        $this->assertEquals([], $query->notTerms);

        $query = new SearchQuery('"Quote 2024"');
        $this->assertEquals(['Quote 2024'], $query->terms);
        $this->assertEquals([], $query->notTerms);
    }
}
