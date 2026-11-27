<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\components\Request;
use humhub\components\Response;
use Yii;
use yii\web\Controller;

/**
 * Tests that AJAX responses provide the current CSRF token, so that pages which are open for a longer time
 * can update an outdated token, e.g. after a re-login in another browser tab.
 */
class ResponseTest extends Unit
{
    public function testAjaxResponseContainsCurrentCsrfToken()
    {
        $response = $this->prepareResponse();

        $token = $response->headers->get(Request::CSRF_HEADER);
        $this->assertNotEmpty($token);
        $this->assertSame(
            Yii::$app->security->unmaskToken(Yii::$app->request->getCsrfToken()),
            Yii::$app->security->unmaskToken($token),
        );
    }

    public function testNonAjaxResponseContainsNoCsrfToken()
    {
        $response = $this->prepareResponse(isAjax: false);

        $this->assertFalse($response->headers->has(Request::CSRF_HEADER));
    }

    public function testNoCsrfTokenIfValidationIsDisabled()
    {
        $response = $this->prepareResponse(enableCsrfValidation: false);

        $this->assertFalse($response->headers->has(Request::CSRF_HEADER));
    }

    public function testNoCsrfTokenIfControllerDisablesValidation()
    {
        $response = $this->prepareResponse(enableControllerCsrfValidation: false);

        $this->assertFalse($response->headers->has(Request::CSRF_HEADER));
    }

    private function prepareResponse(
        bool $isAjax = true,
        bool $enableCsrfValidation = true,
        bool $enableControllerCsrfValidation = true,
    ): Response {
        $request = new Request([
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => $enableCsrfValidation,
        ]);
        if ($isAjax) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }
        Yii::$app->set('request', $request);

        $response = new Response();
        Yii::$app->set('response', $response);

        Yii::$app->controller = new Controller('test', Yii::$app);
        Yii::$app->controller->enableCsrfValidation = $enableControllerCsrfValidation;

        $response->trigger(Response::EVENT_BEFORE_SEND);

        return $response;
    }
}
