<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\functional;

use FunctionalTester;
use humhub\components\Response;
use humhub\controllers\CspReportController;
use humhub\services\PwaService;

/**
 * Covers the security headers applied by {@see Response::prepare()}.
 *
 * @since 1.20
 */
class SecurityHeadersCest
{
    public function testHtmlPageCarriesThePolicy(FunctionalTester $I)
    {
        $I->wantTo('ensure an HTML page is served with the security headers');

        $I->amOnRoute('/user/auth/login');

        $I->assertStringContainsString('default-src', (string)$I->grabHttpHeader('Content-Security-Policy'));
        $I->assertSame('nosniff', $I->grabHttpHeader('X-Content-Type-Options'));
        $I->assertSame('sameorigin', $I->grabHttpHeader('X-Frame-Options'));
        $I->assertSame('max-age=31536000', $I->grabHttpHeader('Strict-Transport-Security'));
    }

    /**
     * The nonce in the policy has to be the one the page's script tags carry, otherwise the
     * browser blocks every inline script on the page.
     */
    public function testPolicyNonceMatchesTheRenderedScriptTags(FunctionalTester $I)
    {
        $I->wantTo('ensure the policy nonce is the one used in the markup');

        $I->amOnRoute('/user/auth/login');

        $policy = (string)$I->grabHttpHeader('Content-Security-Policy');
        $I->assertSame(1, preg_match("/'nonce-([^']+)'/", $policy, $matches), 'policy carries a nonce');

        $I->seeInSource('nonce="' . $matches[1] . '"');
    }

    /**
     * Error pages used to be excluded from the policy: the headers were applied on
     * `EVENT_BEFORE_ACTION`, and the error handler clears the response before rendering.
     */
    public function testErrorPageCarriesThePolicy(FunctionalTester $I)
    {
        $I->wantTo('ensure an error page is served with the policy as well');

        $I->amOnPage('/index-test.php?r=this%2Fdoes%2Fnot-exist');

        $I->seeResponseCodeIs(404);
        $I->assertStringContainsString('default-src', (string)$I->grabHttpHeader('Content-Security-Policy'));
    }

    /**
     * A policy sent with a non-document response protects nothing; the remaining headers
     * still apply.
     */
    public function testNonDocumentResponseHasNoPolicy(FunctionalTester $I)
    {
        $I->wantTo('ensure a JSON response is served without the policy');

        $I->amOnRoute(PwaService::ROUTE_MANIFEST);

        $I->assertNull($I->grabHttpHeader('Content-Security-Policy'));
        $I->assertSame('nosniff', $I->grabHttpHeader('X-Content-Type-Options'));
    }

    public function testCspReportEndpointAcknowledges(FunctionalTester $I)
    {
        $I->wantTo('ensure the violation report endpoint accepts a report');

        // A browser posts the report without a CSRF token; the endpoint has to accept that.
        $I->sendAjaxPostRequest('/index-test.php?r=' . urlencode(trim(CspReportController::ROUTE, '/')));

        $I->seeResponseCodeIs(204);
    }
}
