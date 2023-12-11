<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\tests\codeception\acceptance;

use tour\AcceptanceTester;
use Yii;

class AutoTourCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testAutoTour(AcceptanceTester $I)
    {
        $I->amAdmin();

        // Turn-on Show introduction tour for new users
        if (Yii::$app->settings->get('enable') == 0) {
            $I->checkOptionShowTour();
        }

        // Login how user
        $I->amUser1(true);

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

        $I->waitForText('Hurray! The End.', 10, '.popover.tour');
        $I->wait(1);
        $I->click('End guide', '.popover.tour');

        $I->waitForElementVisible('#wallStream');
        $I->wait(2);
        $I->seeInCurrentUrl('dashboard');

        // Re-login how user
        $I->amUser1(true);
        $I->wait(1);

        $I->dontSeeElement('.popover.tour');
    }
}
