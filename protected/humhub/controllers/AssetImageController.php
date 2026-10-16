<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\assets\AssetImage;
use humhub\components\Controller;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\content\models\ContentContainer;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Serves the permanent URLs of AssetImages ({@see AssetImage::getPermanentUrl()}), which are used
 * wherever an image URL leaves the platform, e.g. in emails: the published assets are volatile,
 * so an email cannot link to them directly. This controller publishes the image if needed and
 * redirects to its current URL in the published assets.
 *
 * @since 1.19.1
 */
class AssetImageController extends Controller
{
    /**
     * The images are fetched by email clients without any session, so neither a login nor the
     * guest mode can be required. The published copies are publicly accessible anyway.
     * @inheritdoc
     */
    protected $access = ControllerAccess::class;

    /**
     * Image of the {@see \humhub\components\assets\AssetImageRegistry} (`Yii::$app->img`),
     * e.g. the mail header image.
     *
     * @param string $name the name of the image in the registry
     */
    public function actionRegistry(string $name): Response
    {
        if (!Yii::$app->img->has($name)) {
            throw new NotFoundHttpException();
        }

        return $this->redirectToImage(Yii::$app->img->$name);
    }

    /**
     * Profile or banner image of a content container (user or space).
     *
     * @param string $guid the GUID of the content container
     * @param string $type {@see ContainerImageController::TYPE_PROFILE_IMAGE} or
     * {@see ContainerImageController::TYPE_PROFILE_BANNER_IMAGE}
     */
    public function actionContainer(string $guid, string $type = ContainerImageController::TYPE_PROFILE_IMAGE): Response
    {
        $container = ContentContainer::findRecord($guid);
        if (!$container instanceof ContentContainerActiveRecord) {
            throw new NotFoundHttpException();
        }

        return $this->redirectToImage(
            $type === ContainerImageController::TYPE_PROFILE_BANNER_IMAGE
                ? $container->bannerImage
                : $container->image,
        );
    }

    private function redirectToImage(AssetImage $image): Response
    {
        $url = $image->getUrl(null, true);
        if ($url === '') {
            throw new NotFoundHttpException();
        }

        return $this->redirect($url);
    }
}
