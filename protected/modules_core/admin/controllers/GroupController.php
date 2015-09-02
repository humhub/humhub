<?php

/**
 * Group Administration Controller
 *
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class GroupController extends Controller
{

    /**
     * Layout View to use
     *
     * @var type
     */
    public $subLayout = "/_layout";

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
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * List all available user groups
     */
    public function actionIndex()
    {

        $model = new Group('search');
        if (isset($_GET['Group']))
            $model->attributes = $_GET['Group'];

        $this->render('index', array(
            'model' => $model
        ));
    }

    /**
     * Edits or Creates a user group
     */
    public function actionEdit()
    {

        // Create Group Edit Form
        $group = Group::model()->findByPk(Yii::app()->request->getQuery('id'));
        if ($group === null) {
            $group = new Group();
        }
        $group->scenario = 'edit';
        $group->populateDefaultSpaceGuid();
        $group->populateAdminGuids();


        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'admin-group-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['Group'])) {
            $_POST = Yii::app()->input->stripClean($_POST);
            $group->attributes = $_POST['Group'];

            if ($group->validate()) {
                $group->save();

                // Redirect to admin groups overview
                $this->redirect(Yii::app()->createUrl('//admin/group'));
            }
        }
        $this->render('edit', array('group' => $group));
    }

    /**
     * Deletes a group
     *
     * On deletion all group members will be moved to another group.
     */
    public function actionDelete()
    {
        Yii::import('admin.forms.*');

        $id = (int) Yii::app()->request->getQuery('id');

        $group = Group::model()->findByPk($id);

        if ($group == null)
            throw new CHttpException(404, Yii::t('AdminModule.controllers_GroupController', 'Group not found!'));

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'admin-deleteGroup-form') {
            echo CActiveForm::validate($group);
            Yii::app()->end();
        }

        $model = new AdminDeleteGroupForm;
        if (isset($_POST['AdminDeleteGroupForm'])) {
            $model->attributes = $_POST['AdminDeleteGroupForm'];
            if ($model->validate()) {
                foreach (User::model()->findAllByAttributes(array('group_id' => $group->id)) as $user) {
                    $user->group_id = $model->group_id;
                    $user->save();
                }
                $group->delete();
                $this->redirect(Yii::app()->createUrl('//admin/group'));
            }
        }

        $this->render('delete', array('group' => $group, 'model' => $model));
    }

}
