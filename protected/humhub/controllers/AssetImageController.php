<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\assets\AssetImage;
use humhub\components\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Resolves the absolute URLs of AssetImages ({@see AssetImage::getUrl()}), which leave the platform,
 * e.g. in emails. The published assets are volatile, so these URLs cannot point there directly:
 * this controller republishes the image if needed and redirects to its current URL.
 *
 * @since 1.19.1
 */
class AssetImageController extends Controller
{
    /**
     * The images are fetched by email clients without any session, so neither a login nor the
     * guest mode can be required. The published copies are publicly accessible anyway.
     */
    protected $access = ControllerAccess::class;

    /**
     * @param string $i the signed token of {@see AssetImage::getUrl()}
     */
    public function actionIndex(string $i): Response
    {
        $resolved = AssetImage::fromToken($i);
        if ($resolved === null) {
            throw new NotFoundHttpException();
        }

        [$image, $options] = $resolved;

        $url = $image->getUrl($options);
        if ($url === '') {
            throw new NotFoundHttpException();
        }

        // Always answer with a Location header: the URL is fetched as a sub-resource (an image),
        // never by a script that would act on the X-Redirect header of an AJAX response
        return Yii::$app->response->redirect($url, 302, false);
    }
}
