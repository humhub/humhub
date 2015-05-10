<?php

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
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Deletes a content object
     *
     * Returns a JSON list of affected wallEntryIds.
     */
    public function actionDelete()
    {
        $this->forcePostRequest();
        $json = [
            'success' => 'false'
        ];

        $model = Yii::app()->request->getParam('model');
        $id = (int) Yii::app()->request->getParam('id');

        $contentObj = Content::get($model, $id);

        if ($contentObj !== null && $contentObj->content->canDelete() && $contentObj->delete()) {
            $json = [
                'success' => true,
                'uniqueId' => $contentObj->getUniqueId(),
                'model' => $model,
                'pk' => $id
            ];
        }

        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Archives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionArchive()
    {

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);
        if ($object != null && $object->content->canArchive()) {
            $object->content->archive();

            $json['success'] = true;
            $json['wallEntryIds'] = $object->content->getWallEntryIds();
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * UnArchives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnarchive()
    {

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);
        if ($object != null && $object->content->canArchive()) {
            $object->content->unarchive();

            $json['success'] = true;
            $json['wallEntryIds'] = $object->content->getWallEntryIds();
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Sticks an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionStick()
    {

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);
        if ($object != null && $object->content->canStick()) {

            if ($object->content->countStickedItems() < 2) {
                $object->content->stick();

                $json['success'] = true;
                $json['wallEntryIds'] = $object->content->getWallEntryIds();
            } else {
                $json['errorMessage'] = Yii::t('WallModule.controllers_ContentController', "Maximum number of sticked items reached!\n\nYou can stick only two items at once.\nTo however stick this item, unstick another before!");
            }
        } else {
            $json['errorMessage'] = Yii::t('WallModule.controllers_ContentController', "Could not load requested object!");
        }
        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Sticks an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionUnStick()
    {

        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);

        if ($object != null && $object->content->canStick()) {
            $object->content->unstick();

            $json['success'] = true;
            $json['wallEntryIds'] = $object->content->getWallEntryIds();
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    public function actionNotificationSwitch()
    {
        $this->forcePostRequest();

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");
        $switch = Yii::app()->request->getParam('switch', true);

        $object = Content::Get($className, $id);

        if ($object != null) {
            $object->follow(Yii::app()->user->id, ($switch == 1) ? true : false );
            $json['success'] = true;
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

}

?>
