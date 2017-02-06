<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use Yii;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\modules\content\models\Content;

/**
 * ContentController is responsible for basic content objects.
 *
 * @package humhub.modules_core.wall.controllers
 * @since 0.5
 * @author Luke
 */
class ContentController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    public function actionDeleteById()
    {
        Yii::$app->response->format = 'json';
        $id = (int) Yii::$app->request->get('id');
        Content::findOne($id);
    }
    
    /**
     * Deletes a content object
     *
     * Returns a JSON list of affected wallEntryIds.
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $model = Yii::$app->request->get('model');
        
        //Due to backward compatibility we use the old delte mechanism in case a model parameter is provided
        $id = (int) ($model != null) ? Yii::$app->request->get('id') : Yii::$app->request->post('id');

        $contentObj = ($model != null) ? Content::Get($model, $id) : Content::findOne($id);

        if(!$contentObj->canDelete()) {
            throw new HttpException(400, Yii::t('ContentModule.controllers_ContentController', 'Could not delete content: Access denied!'));
        }
        
        if ($contentObj !== null && $contentObj->delete()) {
            $json = [
                'success' => true,
                'uniqueId' => $contentObj->getUniqueId(),
                'model' => $model,
                'pk' => $id
            ];
        } else {
            throw new HttpException(500, Yii::t('ContentModule.controllers_ContentController', 'Could not delete content!'));
        }

        return $json;
    }

    /**
     * Archives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionArchive()
    {
        Yii::$app->response->format = 'json';
        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;

        $id = (int) Yii::$app->request->get('id', "");

        $content = Content::findOne(['id' => $id]);
        if ($content !== null && $content->canArchive()) {
            $content->archive();

            $json['success'] = true;
        }

        return $json;
    }

    /**
     * UnArchives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnarchive()
    {
        Yii::$app->response->format = 'json';
        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::$app->request->get('id', "");

        $content = Content::findOne(['id' => $id]);
        if ($content !== null && $content->canArchive()) {
            $content->unarchive();

            $json['success'] = true;
        }

        return $json;
    }

    /**
     * Pins an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionPin()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;

        $content = Content::findOne(['id' => Yii::$app->request->get('id', "")]);
        if ($content !== null && $content->canPin()) {
            if ($content->countPinnedItems() < 2) {
                $content->pin();

                $json['success'] = true;
                $json['contentId'] = $content->id;
            } else {
                $json['info'] = Yii::t('ContentModule.controllers_ContentController', "Maximum number of pinned items reached!\n\nYou can pin to top only two items at once.\nTo however pin this item, unpin another before!");
            }
        } else {
            $json['error'] = Yii::t('ContentModule.controllers_ContentController', "Could not load requested object!");
        }

        return $json;
    }

    /**
     * Unpins an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnPin()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;

        $content = Content::findOne(['id' => Yii::$app->request->get('id', "")]);
        if ($content !== null && $content->canPin()) {
            $content->unpin();
            $json['success'] = true;
        }

        return $json;
    }

    public function actionNotificationSwitch()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $content = Content::findOne(['id' => Yii::$app->request->get('id', "")]);
        if ($content !== null) {
            $switch = (Yii::$app->request->get('switch', true) == 1) ? true : false;
            $obj = $content->getPolymorphicRelation();
            $obj->follow(Yii::$app->user->id, $switch);
            $json['success'] = true;
        }

        return $json;
    }

}

?>
