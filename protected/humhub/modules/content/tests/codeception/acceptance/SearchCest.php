<?php

namespace content\acceptance;

use content\AcceptanceTester;

class SearchCest
{
    public function testMetaSearch(AcceptanceTester $I)
    {
        $I->amAdmin();

        $I->amGoingTo('test meta search');
        $I->click('#search-menu');
        $I->waitForText('Search', 10, '#dropdown-search');

        $contentProviderSelector = '.search-provider[data-provider$=ContentSearchProvider]';
        $userProviderSelector = '.search-provider[data-provider$=UserSearchProvider]';
        $spaceProviderSelector = '.search-provider[data-provider$=SpaceSearchProvider]';

        $I->amGoingTo('search only content');
        $I->metaSearch('Post');
        $I->waitForElementVisible($contentProviderSelector);
        $I->see('Show all results', $contentProviderSelector);
        $I->waitForElementNotVisible($userProviderSelector);
        $I->waitForElementNotVisible($spaceProviderSelector);

        $I->amGoingTo('search only users');
        $I->metaSearch('Sara');
        $I->waitForElementVisible($contentProviderSelector);
        $I->see('No results', $contentProviderSelector);
        $I->see('Advanced Content Search', $contentProviderSelector);
        $I->waitForElementVisible($userProviderSelector);
        $I->see('Show all results', $userProviderSelector);
        $I->waitForElementNotVisible($spaceProviderSelector);

        $I->amGoingTo('search content and spaces');
        $I->metaSearch('Space');
        $I->waitForElementVisible($contentProviderSelector);
        $I->see('Show all results', $contentProviderSelector);
        $I->waitForElementNotVisible($userProviderSelector);
        $I->waitForElementVisible($spaceProviderSelector);

        $I->amGoingTo('search with no results for all providers');
        $I->metaSearch('SomeUnknownWord', false);
        $I->waitForElementVisible($contentProviderSelector);
        $I->waitForText('No results', 10, $contentProviderSelector);
        $I->see('Advanced Content Search', $contentProviderSelector);
        $I->waitForElementNotVisible($userProviderSelector);
        $I->waitForElementNotVisible($spaceProviderSelector);
    }

    public function testContentSearch(AcceptanceTester $I)
    {
        $I->amAdmin();

        $I->amGoingTo('test content search');
        $I->amOnPage('/content/search');
        $I->waitForText('Find Content based on keywords');
        $I->dontSee('No results found!', '.panel');

        $I->fillField('.form-search [name=keyword]', 'Post');
        $I->click('.form-button-search');
        $I->waitForText('Results', 10, '.search-results-header');
        $I->see('Post', '.highlight');

        $I->fillField('.form-search [name=keyword]', 'UnknownWord');
        $I->click('.form-button-search');
        $I->waitForText('No results found!', 10, '[data-stream-content]');
    }
}
