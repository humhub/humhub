<?php


namespace humhub\modules\ui\controllers;

use humhub\components\Controller;
use humhub\modules\ui\Module;
use Yii;
use yii\web\Response;

/**
 * Class ServiceWorker
 *
 * @since 1.4
 * @property Module $module
 * @package humhub\modules\ui\controllers
 */
class ServiceWorkerController extends Controller
{

    public $baseJs;
    public $additionalJs;

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->getHeaders()->set('Content-Type', 'application/javascript');

        $this->addInstallEvent();
        $this->addFetchEvent();

        return $this->baseJs . $this->additionalJs;
    }


    private function addInstallEvent()
    {
        $this->baseJs .= <<<JS
            var CACHE = 'humhub-sw-cache';
        
            self.addEventListener('install', function (event) {
                console.log('********** The service worker is being installed.');
    
                // Store "Offline" page
                var offlineRequest = new Request('/ui/offline');
                event.waitUntil(
                    fetch(offlineRequest).then(function (response) {
                        return caches.open('offline').then(function (cache) {
                                console.log('[oninstall] Cached offline page', response.url);
                                return cache.put(offlineRequest, response);
                            });
                    })
                );
            });
JS;
    }


    private function addFetchEvent()
    {
        $this->baseJs .= <<<JS
            self.addEventListener('fetch', function (event) {
                var request = event.request;
                
                // Check is "page" request
                if (request.method === 'GET' && request.destination === 'document') {
                    event.respondWith(
                        fetch(request).catch(function (error) {
                        console.error('[onfetch] Failed. Serving cached offline fallback ' + error);
                        return caches.open('offline').then(function (cache) {
                                return cache.match('/ui/offline');
                            });
                        })
                    );
                }
            });
JS;
    }

}
