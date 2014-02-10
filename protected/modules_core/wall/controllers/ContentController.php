<?php

/**
 * ContentController is responsible for basic content objects.
 *
 * @package humhub.modules_core.wall.controllers
 * @since 0.5
 * @author Luke
 */
class ContentController extends Controller {

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
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
    public function actionDelete() {

        // Json Array
        $json = array();
        $json['success'] = false;

        $model = Yii::app()->request->getParam('model', "");
        $id = (int) Yii::app()->request->getParam('id', 1);

        $content = Content::get($model, $id);

        if ($content->contentMeta->canDelete()) {

            // Save wall entry ids which belongs to this post
            $json['wallEntryIds'] = array();

            // Wall Entry Ids
            foreach ($content->contentMeta->getWallEntries() as $entry) {
                $json['wallEntryIds'][] = $entry->id;
            }

            $json['wallEntryIds'][] = 0;

            if ($content->delete()) {
                $json['success'] = true;
            }
        }

        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Archives an wall entry & corresponding content object.
     *
     * Returns JSON Output.
     */
    public function actionArchive() {

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);
        if ($object != null && $object->contentMeta->canArchive()) {
            $object->contentMeta->archive();

            $json['success'] = true;
            $json['wallEntryIds'] = $object->contentMeta->getWallEntryIds();
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
    public function actionUnarchive() {

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);
        if ($object != null && $object->contentMeta->canArchive()) {
            $object->contentMeta->unarchive();

            $json['success'] = true;
            $json['wallEntryIds'] = $object->contentMeta->getWallEntryIds();
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
    public function actionStick() {

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);
        if ($object != null && $object->contentMeta->canStick()) {

            if ($object->contentMeta->countStickedItems() < 2) {
                $object->contentMeta->stick();

                $json['success'] = true;
                $json['wallEntryIds'] = $object->contentMeta->getWallEntryIds();
            } else {
                $json['errorMessage'] = Yii::t('WallModule.base', "Maximum number of sticked items reached!\n\nYou can stick only two items at once.\nTo however stick this item, unstick another before!");
            }
        } else {
            $json['errorMessage'] = Yii::t('WallModule.base', "Could not load requested object!");
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
    public function actionUnStick() {

        $json = array();
        $json['success'] = false;   // default

        $id = (int) Yii::app()->request->getParam('id', "");
        $className = Yii::app()->request->getParam('className', "");

        $object = Content::Get($className, $id);

        if ($object != null && $object->contentMeta->canStick()) {
            $object->contentMeta->unstick();

            $json['success'] = true;
            $json['wallEntryIds'] = $object->contentMeta->getWallEntryIds();
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

}

?>
