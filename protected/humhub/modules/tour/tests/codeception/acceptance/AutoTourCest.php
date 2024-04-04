<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\tests\codeception\acceptance;

use Exception;
use tour\AcceptanceTester;
use Yii;

class AutoTourCest
{
    /**
     * @skip
     * @param AcceptanceTester $I
     * @throws Exception
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

        $I->wait(1);
        $I->waitForElementVisible('.popover.tour');
        $I->see('Dashboard', '.popover.tour');
        $I->click('Next', '.popover.tour');

        $I->waitForText('Notifications', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Account Menu', null, '.popover.tour');
        $I->wait(1);
        $I->click('Next', '.popover.tour');

        $I->waitForText('Space Menu', null, '.popover.tour');
        $I->wait(1);
        $I->click('Start space guide', '.popover.tour');

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

        $I->waitForText('Hurray! The End.', null, '.popover.tour');
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
