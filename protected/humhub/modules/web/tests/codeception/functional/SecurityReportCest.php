<?php

namespace web\functional;

use web\FunctionalTester;
use Yii;
use yii\helpers\Url;

class SecurityReportCest
{
    public function testGuestReportIsAcceptedWithoutGuestAccess(FunctionalTester $I)
    {
        $I->wantTo('post a CSP report as guest while guest access is disabled');
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);

        $I->sendAjaxPostRequest(Url::to(['/web/security-report']));
        $I->seeResponseCodeIs(204);
    }

    public function testUserReportIsAccepted(FunctionalTester $I)
    {
        $I->wantTo('post a CSP report as logged in user');
        $I->amUser();

        $I->sendAjaxPostRequest(Url::to(['/web/security-report']));
        $I->seeResponseCodeIs(204);
    }
}
