<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\content\models\WallEntry;
use humhub\modules\content\models\Content;
use yii\web\HttpException;

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
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index', 'wall-entry']
            ]
        ];
    }

    /**
     * Redirects to given HActiveRecordContent or HActiveRecordContentAddon
     */
    public function actionIndex()
    {
        $id = (int) Yii::$app->request->get('id', "");

        $content = Content::findOne(['id' => $id]);

        if (method_exists($content->getPolymorphicRelation(), 'getUrl')) {
            $url = $content->getPolymorphicRelation()->getUrl();
        } else if($content->container !== null) {
            $url = $content->container->createUrl(null, ['contentId' => $id]);
        }
        
        if ($url) {
            return $this->redirect($url);
        }

        throw new HttpException(404, Yii::t('ContentModule.controllers_PermaController', 'Could not find requested content!'));
    }
}

?>
