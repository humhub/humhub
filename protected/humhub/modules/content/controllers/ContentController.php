<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use Yii;
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

    /**
     * Deletes a content object
     *
     * Returns a JSON list of affected wallEntryIds.
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();
        $json = [
            'success' => 'false'
        ];

        $model = Yii::$app->request->get('model');
        $id = (int) Yii::$app->request->get('id');

        $contentObj = Content::get($model, $id);

        if ($contentObj !== null && $contentObj->content->canEdit() && $contentObj->delete()) {
            $json = [
                'success' => true,
                'uniqueId' => $contentObj->getUniqueId(),
                'model' => $model,
                'pk' => $id
            ];
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
            $json['wallEntryIds'] = $content->getWallEntryIds();
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
            $json['wallEntryIds'] = $content->getWallEntryIds();
        }

        return $json;
    }

    /**
     * Sticks an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionStick()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;

        $content = Content::findOne(['id' => Yii::$app->request->get('id', "")]);
        if ($content !== null && $content->canStick()) {
            if ($content->countStickedItems() < 2) {
                $content->stick();

                $json['success'] = true;
                $json['wallEntryIds'] = $content->getWallEntryIds();
            } else {
                $json['errorMessage'] = Yii::t('ContentModule.controllers_ContentController', "Maximum number of sticked items reached!\n\nYou can stick only two items at once.\nTo however stick this item, unstick another before!");
            }
        } else {
            $json['errorMessage'] = Yii::t('ContentModule.controllers_ContentController', "Could not load requested object!");
        }

        return $json;
    }

    /**
     * Sticks an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnStick()
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;

        $content = Content::findOne(['id' => Yii::$app->request->get('id', "")]);
        if ($content !== null && $content->canStick()) {
            $content->unstick();
            $json['success'] = true;
            $json['wallEntryIds'] = $content->getWallEntryIds();
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
