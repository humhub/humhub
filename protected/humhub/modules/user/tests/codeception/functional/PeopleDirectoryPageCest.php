<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\PeopleTagList;
use humhub\modules\user\widgets\UserTags;
use PHPUnit\Framework\Assert;
use user\FunctionalTester;

/**
 * `/people` renders the `PeopleDirectory` island; the cards load from the HTTP API.
 */
class PeopleDirectoryPageCest
{
    public function testRendersTheIsland(FunctionalTester $I)
    {
        $I->wantTo('see the People directory island with its placeholder');
        $I->amUser1();

        $I->amOnRoute('/user/people');
        $I->seeResponseCodeIs(200);
        $I->seeElement('people-directory');
        $I->seeElement('people-directory .c-page-toolbar');
        $I->see('People', 'people-directory .c-page-toolbar__title');
        $I->seeElement('people-directory .c-people-card-skeleton');
        $I->dontSeeElement('.card-panel');
        $I->dontSeeElement('.cards-end');
    }

    public function testTheServerRenderedCardsAreGone(FunctionalTester $I)
    {
        $I->wantTo('see that the cards are no longer rendered by the server');
        $I->amUser1();

        $I->amOnRoute('/user/people/load-more');
        $I->seeResponseCodeIs(404);
    }

    public function testTheDeprecatedFilterSuggestionsStillAnswer(FunctionalTester $I)
    {
        $I->wantTo('keep the default route of the deprecated PeopleFilterPicker working');
        ProfileField::updateAll(['directory_filter' => 1], ['internal_name' => 'firstname']);
        $I->amUser1();

        $I->amOnRoute('/user/people/filter-people-json', ['field' => 'firstname', 'keyword' => 'Sar']);
        $I->seeResponseCodeIs(200);
        $suggestions = json_decode($I->grabPageSource(), true);
        Assert::assertIsArray($suggestions);
        Assert::assertContains('Sara', array_column($suggestions, 'text'));
    }

    public function testTagLinksUseTheSearchParameter(FunctionalTester $I)
    {
        $I->wantTo('see the tags of a user link the directory search by its current parameter');
        $I->amUser1();
        $user = User::findOne(['id' => 2]);
        ContentContainer::updateAll(['tags_cached' => 'php'], ['id' => $user->contentcontainer_id]);
        $user = User::findOne(['id' => 2]);

        foreach ([UserTags::widget(['user' => $user]), PeopleTagList::widget(['user' => $user])] as $html) {
            Assert::assertStringContainsString('q=php', $html);
            Assert::assertStringNotContainsString('keyword=', $html);
        }
    }
}
