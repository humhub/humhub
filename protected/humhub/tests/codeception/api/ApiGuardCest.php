<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use Yii;

/**
 * The guards of the API framework (see `humhub\components\api\BaseController` and
 * `docs/develop/concept-api.md`): an API action must be unreachable outside the API URL
 * space, must not run on the wrong HTTP method, and a session-authenticated write must carry
 * a CSRF token.
 *
 * These are the properties a regression would silently reopen, so they are pinned here
 * rather than only in the endpoint cests.
 */
class ApiGuardCest
{
    /**
     * Core ships no token authentication (that is the rest module's job), so every
     * authenticated case here logs in a session — exactly what the platform's own frontend
     * does. `amLoggedInAs()` must run before the first request of a test.
     */
    public function testApiActionIsUnreachableOutsideTheApiPrefix(ApiTester $I)
    {
        $I->wantTo('be refused when calling an API action outside the API URL space');
        $I->amLoggedInAs(1);

        // API controllers live in module namespaces that Yii's fallback routing would reach
        // (`/comment/api/comment/...`) — outside the prefix, and therefore outside CSRF
        // handling, verb constraints and the auth pipeline.
        $I->sendGet('http://localhost:8080/comment/api/comment/window-by-content?id=1');
        $I->seeResponseCodeIs(404);

        $I->sendGet('http://localhost:8080/like/api/like/state?recordId=1');
        $I->seeResponseCodeIs(404);

        // The mutating actions are not reachable that way either — this is the shape a
        // cross-site top-level navigation could otherwise trigger with the session cookie.
        $I->sendGet('http://localhost:8080/comment/api/comment/delete?id=1');
        $I->seeResponseCodeIs(404);
    }

    /**
     * Routes are registered per verb, so a URL simply does not exist for a method it was not
     * registered for: the rule does not match and the request 404s before reaching any
     * action. (The `VerbFilter` on each controller is the second layer — it guarantees an
     * action cannot EXECUTE on a wrong method should a rule ever be registered without verb
     * constraints.)
     */
    public function testWrongVerbNeverReachesTheAction(ApiTester $I)
    {
        $I->wantTo('see a method a route was not registered for refused');
        $I->amLoggedInAs(1);

        // A read route must not accept a write method …
        $I->sendPost('comment/content/1/window');
        $I->seeResponseCodeIs(404);

        // … and a mutating route must not be reachable with a safe method.
        $I->sendGet('comment');
        $I->seeResponseCodeIs(404);

        $I->sendGet('like');
        $I->seeResponseCodeIs(404);

        // Nothing was executed: no comment was created by the GET above
        $I->sendGet('comment/content/1/window');
        $I->seeResponseCodeIs(200);
    }

    public function testSessionAuthenticatedWriteRequiresCsrfToken(ApiTester $I)
    {
        $I->wantTo('see a session-authenticated write rejected without a CSRF token');
        $I->amLoggedInAs(1);

        $I->sendPost('comment?contentId=1', ['message' => 'No CSRF']);
        $I->seeResponseCodeIs(403);

        // With the token it goes through — the same mechanism `humhub.client` uses in the
        // browser (masked token in the X-CSRF-Token header, raw token in the cookie).
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));

        $I->sendPost('comment?contentId=1', ['message' => 'With CSRF']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['message' => 'With CSRF']);
    }

    public function testReadsNeedNoCsrfToken(ApiTester $I)
    {
        $I->wantTo('read without a CSRF token');
        $I->amLoggedInAs(1);

        $I->sendGet('comment/content/1/window');
        $I->seeResponseCodeIs(200);
    }

    public function testGuestIsUnauthorizedWithoutGuestAccess(ApiTester $I)
    {
        $I->wantTo('see guests rejected while guest access is disabled');

        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);

        $I->sendGet('comment/content/1/window');
        $I->seeResponseCodeIs(401);
    }

    public function testUnknownRecordsAndContentAnswerWithStatusCodes(ApiTester $I)
    {
        $I->wantTo('see plain HTTP status codes instead of an envelope');
        $I->amLoggedInAs(1);

        $I->sendGet('comment/content/99999/window');
        $I->seeResponseCodeIs(404);

        $I->sendGet('comment/99999');
        $I->seeResponseCodeIs(404);

        $I->sendGet('like/state?recordId=99999');
        $I->seeResponseCodeIs(404);

        // Yii's JSON error body, not a `{code, message}` success/failure envelope
        $I->seeResponseContainsJson(['status' => 404]);
    }
}
