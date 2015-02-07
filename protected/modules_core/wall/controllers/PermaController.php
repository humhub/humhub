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

        throw new CHttpException(404, Yii::t('WallModule.controllers_PermaController', 'Could not find requested permalink!'));
    }

    /**
     * On given Content Class and id redirect the user to it.
     */
    public function actionContent() {

        $content = Content::model()->findByAttributes(array('object_model' => Yii::app()->request->getParam('model'), 'object_id' => Yii::app()->request->getParam('id')));
        if ($content != null) {
            $url = $this->createUrl('WallEntry', array('id' => $content->getFirstWallEntryId()));
            $this->redirect($url);

            return;
        }

        $id = (int) Yii::app()->request->getParam('id', "");
        $model = Yii::app()->request->getParam('model');

        // Check given model
        if (!Helpers::CheckClassType($model, 'HActiveRecord')) {
            throw new CHttpException(404, Yii::t('WallModule.controllers_PermaController', 'Unknown content class!'));
        }

        $model = call_user_func(array($model, 'model'));
        $object = $model->findByPk($id);

        if ($object == null) {
            throw new CHttpException(404, Yii::t('WallModule.controllers_PermaController', 'Could not find requested content!'));
        }

        $url = $this->createUrl('WallEntry', array('id' => $object->content->getFirstWallEntryId()));
        $this->redirect($url);
    }

}

?>
