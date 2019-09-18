<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace web\acceptance;

use web\AcceptanceTester;

class NonceCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testNoNonceScript(AcceptanceTester $I)
    {
        $I->amUser();
        $script = "$('body').html('Got ya!')";
        $I->executeJS("$('body').append(\"<script>$script</script>\")");
        $I->wait(1);
        $I->dontSee("Got ya!");
    }

    public function testStatistic(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute(['/admin/setting/statistic']);
        $I->fillField('#statisticsettingsform-trackinghtmlcode', '<script nonce="{{ nonce }}">alert("Tracking Script")</script>');
        $I->click('Save');
        $I->wait(2);
        $I->seeInPopup("Tracking Script");
    }

    public function testInvalidStatistic(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute(['/admin/setting/statistic']);
        $I->fillField('#statisticsettingsform-trackinghtmlcode', '<script>$("body").html("Tracking Script")</script>');
        $I->click('Save');
        $I->wait(2);
        $I->amOnDashboard();
        $I->dontSee("Tracking Script");
    }
}
