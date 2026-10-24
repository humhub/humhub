<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\controllers;

use Codeception\Test\Unit;
use humhub\controllers\PwaController;
use Yii;
use yii\web\Response;

/**
 * The functional suite has no response header accessor, so the raw JavaScript response of the
 * service worker is pinned here: a browser refuses to register a worker that is not served with
 * a JavaScript content type.
 *
 * @since 1.20
 */
class PwaControllerTest extends Unit
{
    protected function tearDown(): void
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        Yii::$app->response->getHeaders()->remove('Content-Type');
        parent::tearDown();
    }

    public function testServiceWorkerIsServedAsRawJavaScript()
    {
        $controller = new PwaController('pwa', Yii::$app);

        $script = $controller->actionServiceWorker();

        $this->assertSame(Response::FORMAT_RAW, Yii::$app->response->format);
        $this->assertSame('application/javascript', Yii::$app->response->getHeaders()->get('Content-Type'));
        $this->assertStringContainsString("addEventListener('install'", $script);
    }
}
