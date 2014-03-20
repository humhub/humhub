<?php

/**
 * PermaController is used to create permanent links to content.
 *
 * @package humhub.modules_core.wall.controllers
 * @since 0.5
 * @author Luke
 */
class PermaController extends Controller {

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
     * On given WallEntryId redirect the user to the corresponding content object.
     *
     * This is mainly used by ActivityStream or Permalinks.
     */
    public function actionWallEntry() {

        // Id of wall entry
        $id = Yii::app()->request->getParam('id', "");

        $wallEntry = WallEntry::model()->with('content')->findByPk($id);

        if ($wallEntry != null) {
            $obj = $wallEntry->content; // Type of IContent
            if ($obj) {
                $this->redirect($obj->container->getUrl(array('wallEntryId' => $id)));
                return;
            }
        }

        throw new CHttpException(404, Yii::t('WallModule.base', 'Could not find requested permalink!'));
    }

    /**
     * On given Content Class and id redirect the user to it.
     */
    public function actionContent() {

        // Id of wall entry
        $id = (int) Yii::app()->request->getParam('id', "");
        $model = Yii::app()->request->getParam('model', "");

        // Check given model
        if (!class_exists($model)) {
            throw new CHttpException(404, Yii::t('WallModule.base', 'Unknown Content Model!'));
        } else {
            // Load Model and check type
            $foo = new $model;
            if (!$foo instanceOf HActiveRecordContent && $foo->asa('HContentAddonBehavior') === null) {
                throw new CHttpException(404, Yii::t('WallModule.base', 'Invalid model!'));
            }
        }

        $model = call_user_func(array($model, 'model'));
        $object = $model->findByPk($id);

        if ($object != null) {

            // Get Content Object of ContentAddon
            if ($object->asa('HContentAddonBehavior') !== null) {
                $object = $object->getContentObject();
            }

            $url = $this->createUrl('WallEntry', array('id' => $object->content->getFirstWallEntryId()));
            $this->redirect($url);
        }

        throw new CHttpException(404, Yii::t('WallModule.base', 'Requested content not found!'));
    }

}

?>
