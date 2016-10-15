<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;

use yii\web\HttpException;
use humhub\compat\HForm;
use humhub\modules\admin\components\Controller;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\fieldtype\BaseType;

/**
 * UserprofileController provides manipulation of the user's profile fields & categories.
 * 
 * @since 0.5
 */
class UserProfileController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Userprofiles'));
        $this->subLayout = '@admin/views/layouts/user';
        return parent::init();
    }

    /**
     * Shows overview of all
     *
     */
    public function actionIndex()
    {
        return $this->render('index', array());
    }

    /**
     * Edits a Profile Field Category
     */
    public function actionEditCategory()
    {
        $id = (int) Yii::$app->request->get('id');

        $category = ProfileFieldCategory::findOne(['id' => $id]);
        if ($category == null) {
            $category = new ProfileFieldCategory;
        }

        $category->translation_category = $category->getTranslationCategory();

        if ($category->load(Yii::$app->request->post()) && $category->validate() && $category->save()) {
            return $this->redirect(['/admin/user-profile']);
        }

        return $this->render('editCategory', array('category' => $category));
    }

    /**
     * Deletes a Profile Field Category
     */
    public function actionDeleteCategory()
    {
        $id = (int) Yii::$app->request->get('id');

        $category = ProfileFieldCategory::findOne(['id' => $id]);
        if ($category == null)
            throw new HttpException(500, Yii::t('AdminModule.controllers_UserprofileController', 'Could not load category.'));

        if (count($category->fields) != 0)
            throw new HttpException(500, Yii::t('AdminModule.controllers_UserprofileController', 'You can only delete empty categories!'));

        $category->delete();

        return $this->redirect(['/admin/user-profile']);
    }

    public function actionEditField()
    {
        $id = (int) Yii::$app->request->get('id');

        // Get Base Field
        $field = ProfileField::findOne(['id' => $id]);
        if ($field == null)
            $field = new ProfileField;

        // Get all Available Field Class Instances, also bind current profilefield to the type
        $profileFieldTypes = new BaseType();
        $fieldTypes = $profileFieldTypes->getTypeInstances($field);

        // Build Form Definition
        $definition = array();
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
        $form->models['ProfileField'] = $field;
        foreach ($fieldTypes as $fieldType) {
            $form->models[get_class($fieldType)] = $fieldType;
        }

        // Form Submitted?
        if ($form->submitted('save') && $form->validate()) {

            // Use ProfileField Instance from Form with new Values
            $field = $form->models['ProfileField'];
            $fieldType = $form->models[$field->field_type_class];

            if ($field->save() && $fieldType->save()) {
                return $this->redirect(['/admin/user-profile']);
            }
        }
        if ($form->submitted('delete')) {
            $field->delete();
            return $this->redirect(['/admin/user-profile']);
        }


        return $this->render('editField', array('hForm' => $form, 'field' => $field));
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
