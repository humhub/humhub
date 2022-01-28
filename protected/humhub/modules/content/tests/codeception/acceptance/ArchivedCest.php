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

        $archivedContents = ['User 2 Space 2 Post Archived'];
        $notArchivedContents = [
            'User 2 Space 2 Post Public',
            'User 2 Space 2 Post Private',
            'User 2 Profile Post Public',
            'User 2 Profile Post Private',
        ];

        // See not archived content on Profile page
        $I->dontSeeArchivedContents($archivedContents, $notArchivedContents);

        // See only archived content on Profile page
        $I->filterStreamArchived();
        $I->seeArchivedContents($archivedContents, $notArchivedContents);
    }

    public function testArchivedSpace(AcceptanceTester $I)
    {
        $I->wantTo('ensure that profile stream page filters content from archived Space.');

        $I->amUser1();

        // Archive Space fully
        $I->amOnSpace(2, '/space/manage');
        $I->click('Archive');

        $I->amOnProfile();

        $archivedContents = [
            'User 2 Space 2 Post Archived',
            'User 2 Space 2 Post Public',
            'User 2 Space 2 Post Private',
        ];
        $notArchivedContents = [
            'User 2 Profile Post Public',
            'User 2 Profile Post Private',
        ];

        // See not archived content on Profile page
        $I->dontSeeArchivedContents($archivedContents, $notArchivedContents);

        // See only archived content on Profile page
        $I->filterStreamArchived();
        $I->seeArchivedContents($archivedContents, $notArchivedContents);
    }

    public function testSpaceArchivedContent(AcceptanceTester $I)
    {
        $I->wantTo('ensure that space stream page filters archived content.');

        $I->amUser1();
        $I->amOnSpace2();

        $archivedContents = ['User 2 Space 2 Post Archived'];
        $notArchivedContents = [
            'User 2 Space 2 Post Public',
            'User 2 Space 2 Post Private',
        ];

        // See not archived content on Space page
        $I->dontSeeArchivedContents($archivedContents, $notArchivedContents);

        // See only archived content on Space page
        $I->filterStreamArchived();
        $I->seeArchivedContents($archivedContents, $notArchivedContents);
    }

    public function testDashboardArchivedContent(AcceptanceTester $I)
    {
        $I->wantTo('ensure that dashboard stream hides archived content.');

        $I->amUser1();
        $I->amOnDashboard();

        $archivedContents = ['User 2 Space 2 Post Archived'];
        $notArchivedContents = [
            'User 2 Space 2 Post Public',
            'User 2 Space 2 Post Private',
            'Admin Space 2 Post Public',
            'Admin Space 2 Post Private',
            'User 2 Profile Post Public',
            'User 2 Profile Post Private',
        ];

        // See not archived content on Dashboard
        $I->dontSeeArchivedContents($archivedContents, $notArchivedContents);

        // Archive one content
        $I->jsClick('[data-content-key=10] [data-toggle=dropdown]');
        $I->jsClick('[data-content-key=10] [data-action-click=archive]');
        $I->wait(2);
        $I->dontSee('User 2 Space 2 Post Public');

        // Archive Space
        $I->amOnSpace(2, '/space/manage');
        $I->click('Archive');
        $I->amOnDashboard();
        $archivedContents = array_merge($archivedContents, array_slice($notArchivedContents, 0, 4));
        $notArchivedContents = array_slice($notArchivedContents, 4);
        $I->dontSeeArchivedContents($archivedContents, $notArchivedContents);
    }

    public function testDashboardArchivedContentWithFilter(AcceptanceTester $I)
    {
        $I->wantTo('ensure that dashboard stream displays archived content.');

        $I->amAdmin();
        $I->wait(2);

        // Enable filter panel on Dashboard
        $I->amOnRoute(['/admin/setting/basic']);
        $I->wait(2);
        $I->click('[data-action-click=clickCollab]');
        $I->click('[for="basicsettingsform-dashboardshowprofilepostform"]');
        $I->click('Save');
        $I->seeSuccess('Saved');

        $archivedContents = ['User 2 Space 2 Post Archived'];
        $notArchivedContents = [
            'Admin Space 2 Post Private',
            'Admin Space 2 Post Public',
            'User 2 Space 2 Post Public',
            'User 3 Space 1 Post Public',
            'User 1 Space 1 Post Private',
            'User 1 Space 1 Post Public',
            'User 2 Profile Post Public',
            'User 1 Profile Post Public',
            'User 1 Profile Post Private',
        ];

        $I->amOnDashboard();
        $I->wait(1);
        $I->scrollToBottom();
        $I->dontSeeArchivedContents($archivedContents, $notArchivedContents);

        $I->filterStreamArchived();
        $I->seeArchivedContents($archivedContents, $notArchivedContents);
    }
}
