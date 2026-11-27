<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace web\acceptance;

use web\AcceptanceTester;

class CsrfTokenCest
{
    public function testOutdatedCsrfTokenIsReplacedByAjaxResponse(AcceptanceTester $I)
    {
        $I->wantTo('ensure that an outdated CSRF token of an open page is replaced by the next AJAX response');
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForJS('return $.active === 0;', 10);

        $I->amGoingTo('simulate a CSRF token which became outdated in the meantime');
        $I->executeJS("yii.setCsrfToken(yii.getCsrfParam(), 'outdated'); yii.refreshCsrfToken();");
        $I->seeElementInDOM('#contentFormBody form input[name="_csrf"]');

        $I->amGoingTo('perform an AJAX request');
        $I->executeJS('$.get(window.location.href);');

        $I->expectTo('see the CSRF token of the page and its forms updated');
        $I->waitForJS("return yii.getCsrfToken() !== 'outdated';", 10);
        $I->waitForJS("return $('form input[name=\"_csrf\"]').filter((i, input) => input.value === 'outdated').length === 0;", 1);

        $I->expectTo('be able to submit a form');
        $I->createPost('Post with updated CSRF token');
    }

    public function testCsrfTokenIsUpdatedAfterReLoginInAnotherTab(AcceptanceTester $I)
    {
        $I->wantTo('ensure that an open page keeps working after a re-login in another browser tab');
        $I->amUser1();
        $I->amOnSpace2();

        $I->amGoingTo('log out and log in again in another tab, which creates a new CSRF token');
        $I->openNewTab();
        $I->amOnSpace2();
        $I->amUser1(true);
        $I->closeTab();

        $I->amGoingTo('perform an AJAX request in the first tab, e.g. a live update');
        $I->executeJS('window.csrfTokenBefore = yii.getCsrfToken(); $.get(window.location.href);');
        $I->waitForJS('return yii.getCsrfToken() !== window.csrfTokenBefore;', 10);

        $I->expectTo('be able to submit a form in the first tab');
        $I->createPost('Post after re-login in another tab');
    }

    public function testRequestRejectedByOutdatedCsrfTokenUpdatesToken(AcceptanceTester $I)
    {
        $I->wantTo('ensure that a request rejected because of an outdated CSRF token provides the current token');
        $I->amUser1();
        $I->amOnSpace2();
        $I->waitForJS('return $.active === 0;', 10);
        $I->executeJS("yii.setCsrfToken(yii.getCsrfParam(), 'outdated'); yii.refreshCsrfToken();");

        $I->amGoingTo('submit a POST request with the outdated CSRF token');
        $I->executeJS('$.post(window.location.href).fail((xhr) => window.rejectedResponse = {
            status: xhr.status,
            token: xhr.getResponseHeader("X-CSRF-Token"),
        });');
        $I->waitForJS('return window.rejectedResponse && window.rejectedResponse.status === 400;', 10);

        $I->expectTo('see the current CSRF token provided by the rejected response and used by the page');
        $I->waitForJS('return !!window.rejectedResponse.token;', 1);
        $I->waitForJS('return yii.getCsrfToken() === window.rejectedResponse.token;', 1);
        $I->createPost('Post after a rejected request');
    }

    public function testUpdatedCsrfTokenDoesNotMarkFormAsChanged(AcceptanceTester $I)
    {
        $I->wantTo('ensure that an updated CSRF token is not considered as unsaved form change');
        $I->amUser1();
        $I->amOnRoute(['/notification/user']);
        $I->waitForElementVisible('form[data-ui-addition="acknowledgeForm"]');

        $I->amGoingTo('perform an AJAX request which updates the CSRF token');
        $I->executeJS('window.csrfTokenBefore = yii.getCsrfToken(); $.get(window.location.href);');
        $I->waitForJS('return yii.getCsrfToken() !== window.csrfTokenBefore;', 10);

        $I->expectTo('leave the page without a warning about unsaved changes');
        $I->click('#top-menu-nav a.nav-link[href*="dashboard"]');
        $I->waitForElementVisible('#wallStream', 30);
    }
}
