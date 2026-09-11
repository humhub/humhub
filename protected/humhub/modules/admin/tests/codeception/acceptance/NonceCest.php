<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace admin\acceptance;

use admin\AcceptanceTester;
use Yii;

/**
 * Verifies in a real browser that the Content Security Policy is enforced: an inline script
 * without the nonce must not run, while the tracking code an admin configures does, because
 * `TrackingWidget` renders it with the nonce of the current session.
 *
 * Lived in the `web` module until it was dissolved in 1.20.
 */
class NonceCest
{
    public function _after(AcceptanceTester $I)
    {
        Yii::$app->settings->set('trackingHtmlCode', null);
    }

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
        $I->wait(2);
        $I->executeJS('_editor = document.querySelectorAll("div.CodeMirror")[0].CodeMirror; _editor.setValue("<script nonce=\"{{ nonce }}\">$(\".field-statisticsettingsform-trackinghtmlcode\").after(\'<div id=\"test_tracking_script\">Tracking Script</div>\')</script>");');
        $I->jsClick('button[type=submit]');
        $I->wait(2);
        $I->see("Tracking Script", '#test_tracking_script');
    }

    public function testInvalidStatistic(AcceptanceTester $I)
    {
        Yii::$app->settings->set('trackingHtmlCode', '<script>alert("Tracking Script")</script>');
        $I->amAdmin();
        $I->amOnRoute(['/admin/setting/statistic']);
        $I->wait(2);
        $I->executeJS('_editor = document.querySelectorAll("div.CodeMirror")[0].CodeMirror; _editor.setValue("<script>alert(\"Tracking Script\")</script>");');
        $I->jsClick('button[type=submit]');
        $I->wait(2);
        $I->amOnDashboard();
        $I->dontSee("Tracking Script");
    }
}
