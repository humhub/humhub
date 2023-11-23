<?php

use content\AcceptanceTester;

class DraftCest
{
    public function testCreateDraftPost(AcceptanceTester $I)
    {
        $I->amSpaceAdmin(false, 3);

        $I->wantTo('create a draft post.');
        $I->waitForText('What\'s on your mind?');
        $I->click('#contentFormBody .humhub-ui-richtext[contenteditable]');
        $I->fillField('#contentFormBody .humhub-ui-richtext[contenteditable]', 'Some Schabernack');
        $I->click('#contentFormBody ul.preferences');
        $I->waitForText('Create as draft');
        $I->click('Create as draft');
        $I->waitForText('DRAFT', '10', '.label-container');
        $I->see('Save as draft', '#post_submit_button');
        $I->click('#post_submit_button', '#contentFormBody');

        $I->wantTo('ensure draft has a draft badge.');
        $I->waitForText('DRAFT', '5', '//div[@class="wall-entry"][1]');
        $I->wantTo('ensure author can see the draft content on dashboard.');
        $I->amOnDashboard();
        $I->waitForText('Schabernack', null, '[data-stream-entry="1"]');
        $I->waitForText('DRAFT', null, '[data-stream-entry="1"]');

        $I->wantTo('ensure draft is not visible for other users.');
        $I->amUser2(true);
        $I->amOnSpace3();
        $I->dontSee('Schabernack');
        $I->amOnDashboard();
        $I->waitForElementVisible('[data-stream-entry="1"]');
        $I->dontSee('Schabernack');

        $I->wantTo('publish draft');
        $I->amSpaceAdmin(true, 3);
        $I->waitForText('Schabernack');
        $I->click('//div[@class="wall-entry"][1]//ul[contains(@class, "preferences")]');
        $I->waitForText('Publish draft', '5');
        $I->click('Publish draft');
        $I->waitForText('The content has been successfully published.');
        $I->wait(2);
        $I->dontSee('DRAFT');

        $I->wantTo('ensure published draft is now visible for other users.');
        $I->amUser2(true);
        $I->amOnSpace3();
        $I->waitForText('Schabernack');
    }
}
