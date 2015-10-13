<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class UserProfileController extends Controller
{

    public $subLayout = "/_layout";

    public function behaviors()
    {
        return array(
            'HReorderContentBehavior' => array(
                'class' => 'application.behaviors.HReorderContentBehavior',
            )
        );
    }

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
     * Shows overview of all
     *
     */
    public function actionIndex()
    {
        $this->render('index', array());
    }

    /**
     * Edits a Profile Field Category
     */
    public function actionEditCategory()
    {

        $id = (int) Yii::app()->request->getQuery('id');

        $category = ProfileFieldCategory::model()->findByPk($id);
        if ($category == null)
            $category = new ProfileFieldCategory;

        $category->translation_category = $category->getTranslationCategory();

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'admin-userprofile-editcategory') {
            echo CActiveForm::validate($category);
            Yii::app()->end();
        }

        if (isset($_POST['ProfileFieldCategory'])) {
            $_POST = Yii::app()->input->stripClean($_POST);
            $category->attributes = $_POST['ProfileFieldCategory'];

            if ($category->validate()) {
                $category->save();
                $this->redirect(Yii::app()->createUrl('//admin/userprofile'));
            }
        }

        $this->render('editCategory', array('category' => $category));
    }

    /**
     * Deletes a Profile Field Category
     */
    public function actionDeleteCategory()
    {

        $this->forcePostRequest();

        $id = (int) Yii::app()->request->getQuery('id');

        $category = ProfileFieldCategory::model()->findByPk($id);
        if ($category == null)
            throw new CHttpException(500, Yii::t('AdminModule.controllers_UserprofileController', 'Could not load category.'));

        if (count($category->fields) != 0)
            throw new CHttpException(500, Yii::t('AdminModule.controllers_UserprofileController', 'You can only delete empty categories!'));

        $category->delete();

        $this->redirect(Yii::app()->createUrl('//admin/userprofile'));
    }

    public function actionEditField()
    {

        // XSS Protection
        $_POST = Yii::app()->input->stripClean($_POST);

        $id = (int) Yii::app()->request->getQuery('id');

        // Get Base Field
        $field = ProfileField::model()->findByPk($id);
        if ($field == null)
            $field = new ProfileField;

        // Get all Available Field Class Instances, also bind current profilefield to the type
        $profileFieldTypes = new ProfileFieldType();
        $fieldTypes = $profileFieldTypes->getTypeInstances($field);

        // Build Form Definition
        $definition = array();

        #$definition['activeForm'] = array(
        #    'class' => 'CActiveForm',
        #    'enableAjaxValidation' => true,
        #    'id' => 'login-form',
        #);

        $definition['elements'] = array();

        // Add all sub forms
        $definition['elements'] = array_merge($definition['elements'], $field->getFormDefinition());
        foreach ($fieldTypes as $fieldType) {
            $definition['elements'] = array_merge($definition['elements'], $fieldType->getFormDefinition());
        }

        // Add Form Buttons
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'label' => Yii::t('AdminModule.controllers_UserprofileController', 'Save'),
                'class' => 'btn btn-primary'
            ),
        );

        if (!$field->isNewRecord && !$field->is_system) {
            $definition['buttons']['delete'] = array(
                'type' => 'submit',
                'label' => Yii::t('AdminModule.controllers_UserprofileController', 'Delete'),
                'class' => 'btn btn-danger pull-right'
            );
        }

        // Create Form Instance
        $form = new HForm($definition);

        // Add used models to the CForm, so we can validate it
        $form['ProfileField']->model = $field;
        foreach ($fieldTypes as $fieldType) {
            $form[get_class($fieldType)]->model = $fieldType;
        }

        // Form Submitted?
        if ($form->submitted('save') && $form->validate()) {
            $this->forcePostRequest();

            // Use ProfileField Instance from Form with new Values
            $field = $form['ProfileField']->model;
            $fieldType = $form[$field->field_type_class]->model;

            $field->save();
            $fieldType->save();

            $this->redirect(Yii::app()->createUrl('//admin/userprofile'));
        }

        if ($form->submitted('delete')) {
            $this->forcePostRequest();
            $field->delete();
            $this->redirect(Yii::app()->createUrl('//admin/userprofile'));
        }


        $this->render('editField', array('form' => $form, 'field' => $field));
    }

    /**
     * Reorder Fields action.
     * @uses behaviors.ReorderContentBehavior
     */
    public function actionReorderFields()
    {
        // generate json response
        echo json_encode($this->reorderContent('ProfileField', 200, 'The item order was successfully changed.'));
    }

}
