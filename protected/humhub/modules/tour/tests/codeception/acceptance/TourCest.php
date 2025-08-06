<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tour\acceptance;

use Exception;
use tour\AcceptanceTester;

class TourCest
{
    /**
     * @param AcceptanceTester $I
     * @throws Exception
     * @skip This test fails in travis environment, needs to be fixed!
     */
    public function testTour(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->amOnDashboard();

        $I->dontSeeElement('#getting-started-panel');

        $I->checkOptionShowTour();

        $I->amOnDashboard();
        $I->waitForText('You are the first user here', 10, '#globalModal');
        $I->click('Save and close', '#globalModal');

        $I->waitForElementVisible('#getting-started-panel');
        $I->see('Guide: Administration (Modules)');

        $I->wait(4);
        $I->click('Guide: Overview');

        $I->waitForElementVisible('.popover.tour');
        $I->see('Dashboard', '.popover.tour');
        $I->click('Next', '.popover.tour');

        $I->waitForText('Notifications', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Account Menu', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space Menu', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Start space guide', '.popover.tour');

        $I->wait(2);

        $I->waitForText('Once you have joined or created a new space', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space navigation menu', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space preferences', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Writing posts', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Yours, and other users\' posts will appear here.');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Most recent activities');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space members', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Yay! You\'re done.', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Profile Guide', '.popover.tour');

        $I->waitForText('User profile', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Profile photo', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Edit account', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Profile menu', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Profile stream', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Hurray! You\'re done!', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Administration (Modules)', '.popover.tour');

        $I->waitForText('As an admin, you can manage the whole platform from here', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Modules', 10, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Hurray! That\'s all for now.', 10, '.popover.tour');
        $I->wait(1);
        $I->click('End guide', '.popover.tour');

        $I->waitForElementVisible('#wallStream');
        $I->seeInCurrentUrl('dashboard');
    }
}
