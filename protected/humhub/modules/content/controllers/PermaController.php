<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\content\models\Content;
use humhub\modules\user\helpers\AuthHelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * PermaController is used to create permanent links to content.
 *
 * @package humhub.modules_core.wall.controllers
 * @since 0.5
 * @author Luke
 */
class PermaController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'guestAllowedActions' => ['index', 'wall-entry'],
            ],
        ];
    }

    /**
     * Redirects to given ContentActiveRecord or ContentAddonActiveRecord
     */
    public function actionIndex()
    {
        $id = (int)Yii::$app->request->get('id');
        $commentId = (int)Yii::$app->request->get('commentId');

        $content = Content::findOne(['id' => $id]);
        if ($content?->canView()) {
            $highlight = Yii::$app->request->get('highlight');
            if ($highlight !== null) {
                Yii::$app->session->set('contentHighlight', $highlight);
            }

            if (method_exists($content->getPolymorphicRelation(), 'getUrl')) {
                $url = $content->getPolymorphicRelation()->getUrl();
            } elseif ($content->container !== null) {
                $urlParams = ['contentId' => $id];
                if ($commentId) {
                    $urlParams['commentId'] = $commentId;
                }
                $url = $content->container->createUrl(null, $urlParams);
            }

            if (!empty($url)) {
                return $this->redirect($url);
            }
        }

        if (Yii::$app->user->isGuest) {
            if (AuthHelper::isGuestAccessEnabled()) {
                throw new UnauthorizedHttpException(Yii::t('ContentModule.base', 'You need to login to view this content!'));
            } else {
                return Yii::$app->user->loginRequired();
            }
        }

        throw new NotFoundHttpException(Yii::t('ContentModule.base', 'Could not find requested content!'));
    }
}
