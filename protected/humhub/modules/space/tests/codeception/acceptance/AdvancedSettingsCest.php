<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace space\acceptance;

use space\AcceptanceTester;

class AdvancedSettingsCest
{
    public function testHideMembers(AcceptanceTester $I)
    {
        $I->amSpaceAdmin(false, 2);

        $I->amOnPage('/s/space-2/home');
        $I->seeElement('#space-members-panel');

        $I->amOnPage('/s/space-2/about');
        $I->seeElement('#space-members-panel');

        $I->amOnPage('/s/space-2/space/manage/default/advanced');
        $I->see('Members', '.statistics');
        $I->checkOption('#advancedsettings-hidemembers');
        $I->submitForm('#spaceIndexForm', []);

        $I->waitForText('Saved');
        $I->dontSee('Members', '.statistics');

        $I->amOnPage('/s/space-2/home');
        $I->dontSeeElement('#space-members-panel');

        $I->amOnPage('/s/space-2/about');
        $I->dontSeeElement('#space-members-panel');
    }

    public function testHideActivities(AcceptanceTester $I)
    {
        $I->amSpaceAdmin(false, 2);

        $I->amOnPage('/s/space-2/home');
        $I->seeElement('#panel-activities');

        $I->amOnPage('/s/space-2/space/manage/default/advanced');
        $I->checkOption('#advancedsettings-hideactivities');
        $I->submitForm('#spaceIndexForm', []);

        $I->waitForText('Saved');

        $I->amOnPage('/s/space-2/home');
        $I->dontSeeElement('#panel-activities');
    }

    public function testHideAbout(AcceptanceTester $I)
    {
        $I->amSpaceAdmin(false, 2);

        $I->amOnPage('/s/space-2/space/manage/default/advanced');
        $I->waitForElementVisible('#space-header-controls-menu');
        $I->click('#space-header-controls-menu');
        $I->see('About', '.dropdown-menu');

        $I->checkOption('#advancedsettings-hideabout');
        $I->submitForm('#spaceIndexForm', []);

        $I->seeSuccess('Saved');

        $I->waitForElementVisible('#space-header-controls-menu');
        $I->click('#space-header-controls-menu');
        $I->dontSee('About', '.dropdown-menu');
    }

    public function testHideFollowers(AcceptanceTester $I)
    {
        $I->amSpaceAdmin(false, 2);

        $I->amOnPage('/s/space-2/space/manage/default/advanced');
        $I->see('Followers', '.statistics');
        $I->checkOption('#advancedsettings-hidefollowers');
        $I->submitForm('#spaceIndexForm', []);

        $I->waitForText('Saved');
        $I->dontSee('Followers', '.statistics');
    }

}
