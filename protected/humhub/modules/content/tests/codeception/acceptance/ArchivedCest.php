<?php
namespace content\acceptance;

use content\AcceptanceTester;

class ArchivedCest
{
    public function testProfileArchivedContents(AcceptanceTester $I)
    {
        $I->wantTo('ensure that profile stream page filters archived content.');

        $I->amUser1();
        $I->amOnProfile();

        // See not archived content on Profile page
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Archived');
        $I->see('User 2 Space 2 Post Public');
        $I->see('User 2 Space 2 Post Private');
        $I->see('User 2 Profile Post Public');
        $I->see('User 2 Profile Post Private');

        // See only archived content on Profile page
        $I->filterStreamArchived();
        $I->see('User 2 Space 2 Post Archived');
        $I->dontSee('User 2 Space 2 Post Public');
        $I->dontSee('User 2 Space 2 Post Private');
        $I->dontSee('User 2 Profile Post Public');
        $I->dontSee('User 2 Profile Post Private');
    }

    public function testArchivedSpace(AcceptanceTester $I)
    {
        $I->wantTo('ensure that profile stream page filters content from archived Space.');

        $I->amUser1();

        // Archive Space fully
        $I->amOnSpace(2, '/space/manage');
        $I->click('Archive');

        $I->amOnProfile();

        // See not archived content on Profile page
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Archived');
        $I->dontSee('User 2 Space 2 Post Public');
        $I->dontSee('User 2 Space 2 Post Private');
        $I->see('User 2 Profile Post Public');
        $I->see('User 2 Profile Post Private');

        // See only archived content on Profile page
        $I->filterStreamArchived();
        $I->see('User 2 Space 2 Post Archived');
        $I->see('User 2 Space 2 Post Public');
        $I->see('User 2 Space 2 Post Private');
        $I->dontSee('User 2 Profile Post Public');
        $I->dontSee('User 2 Profile Post Private');
    }

    public function testSpaceArchivedContent(AcceptanceTester $I)
    {
        $I->wantTo('ensure that space stream page filters archived content.');

        $I->amUser1();
        $I->amOnSpace2();

        // See not archived content on Space page
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Archived');
        $I->see('User 2 Space 2 Post Public');
        $I->see('User 2 Space 2 Post Private');

        // See only archived content on Space page
        $I->filterStreamArchived();
        $I->see('User 2 Space 2 Post Archived');
        $I->dontSee('User 2 Space 2 Post Public');
        $I->dontSee('User 2 Space 2 Post Private');
    }

    public function testDashboardArchivedContent(AcceptanceTester $I)
    {
        $I->wantTo('ensure that dashboard stream hides archived content.');

        $I->amUser1();
        $I->amOnDashboard();

        // See not archived content on Dashboard
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Archived');
        $I->see('User 2 Space 2 Post Public');
        $I->see('User 2 Space 2 Post Private');
        $I->see('Admin Space 2 Post Public');
        $I->see('Admin Space 2 Post Private');
        $I->see('User 2 Profile Post Public');
        $I->see('User 2 Profile Post Private');

        // Archive one content
        $I->jsClick('[data-content-key=10] [data-toggle=dropdown]');
        $I->jsClick('[data-content-key=10] [data-action-click=archive]');
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Public');

        // Archive Space
        $I->amOnSpace(2, '/space/manage');
        $I->click('Archive');
        $I->amOnDashboard();
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Archived');
        $I->dontSee('User 2 Space 2 Post Public');
        $I->dontSee('User 2 Space 2 Post Private');
        $I->dontSee('Admin Space 2 Post Public');
        $I->dontSee('Admin Space 2 Post Private');
        $I->see('User 2 Profile Post Public');
        $I->see('User 2 Profile Post Private');
    }
}
