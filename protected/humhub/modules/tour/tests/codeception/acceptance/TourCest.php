<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tour\acceptance;

use tour\AcceptanceTester;

class TourCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testTour(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->amOnDashboard();

        $I->dontSeeElement('#getting-started-panel');

        $I->amOnRoute(['/admin/setting/basic']);

        $I->see('Show introduction tour for new users');
        $I->click('.field-basicsettingsform-tour label');

        $I->click('Save');
        $I->seeSuccess();

        $I->amOnDashboard();
        $I->waitForText('You are the first user here', null, '#globalModal');
        $I->click('Save and close', '#globalModal');

        $I->waitForElementVisible('#getting-started-panel');
        $I->see('Guide: Administration (Modules)');

        $I->wait(4);
        $I->click('Guide: Overview');

        $I->waitForElementVisible('.popover.tour');
        $I->see('Dashboard', '.popover.tour');
        $I->click('Next', '.popover.tour');

        $I->waitForText('Notifications', null,  '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Account Menu',  null,'.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space Menu', null, '.popover.tour');
        $I->wait(1);
        $I->click('Start space guide', '.popover.tour');

        $I->wait(2);

        $I->waitForText('Once you have joined or created a new space', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space navigation menu', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space preferences', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Writing posts', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Yours, and other users\' posts will appear here.');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Most recent activities');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space members', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Yay! You\'re done.', null, '.popover.tour');
        $I->wait(1);
        $I->click('Profile Guide', '.popover.tour');

        $I->waitForText('User profile', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Profile photo', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Edit account', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Profile menu', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Profile stream', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Hurray! You\'re done!', null, '.popover.tour');
        $I->wait(1);
        $I->click('Administration (Modules)', '.popover.tour');

        $I->waitForText('As an admin, you can manage the whole platform from here', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Modules', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Hurray! That\'s all for now.', null, '.popover.tour');
        $I->wait(1);
        $I->click('End guide', '.popover.tour');

        $I->waitForElementVisible('#wallStream');
        $I->seeInCurrentUrl('dashboard');
    }
}
