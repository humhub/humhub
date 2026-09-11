<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\services\PwaService;
use humhub\services\ServiceWorkerService;
use Yii;
use yii\web\Response;

/**
 * PwaController serves the Progressive Web App endpoints: the web app manifest, the
 * service worker script and the offline fallback page.
 *
 * @since 1.20
 */
class PwaController extends Controller
{
    /**
     * Allow guest access independently from guest mode setting.
     *
     * @var string
     */
    public $access = ControllerAccess::class;

    /**
     * Serves the web app manifest as `/manifest.json`.
     */
    public function actionManifest(): Response
    {
        return $this->asJson(PwaService::getManifest());
    }

    /**
     * Serves the service worker as `/sw.js`.
     */
    public function actionServiceWorker(): string
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->getHeaders()->set('Content-Type', 'application/javascript');

        return ServiceWorkerService::buildScript();
    }

    /**
     * Serves the offline fallback page as `/offline.pwa.html`, cached by the service worker
     * on installation.
     */
    public function actionOffline(): string
    {
        return $this->renderPartial('@humhub/views/pwa/offline');
    }
}
