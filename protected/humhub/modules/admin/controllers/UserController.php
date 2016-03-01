<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\compat\HForm;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\admin\components\Controller;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;

/**
 * User management
 * 
 * @since 0.5
 */
class UserController extends Controller
{

    public function init() {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Users'));
        return parent::init();
    }
    
    /**
     * Returns a List of Users
     */
    public function actionIndex()
    {
        $searchModel = new \humhub\modules\admin\models\UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ));
    }

    /**
     * Edits a user
     *
     * @return type
     */
    public function actionEdit()
    {
        $user = User::findOne(['id' => Yii::$app->request->get('id')]);

        if ($user == null)
            throw new \yii\web\HttpException(404, Yii::t('AdminModule.controllers_UserController', 'User not found!'));

        $user->scenario = 'editAdmin';
        $user->profile->scenario = 'editAdmin';
        $profile = $user->profile;

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();
        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
            'title' => 'Account',
            'elements' => array(
                'username' => array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 25,
                ),
                'email' => array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 100,
                ),
                'group_id' => array(
                    'type' => 'dropdownlist',
                    'class' => 'form-control',
                    'items' => \yii\helpers\ArrayHelper::map(Group::find()->all(), 'id', 'name'),
                ),
                'super_admin' => array(
                    'type' => 'checkbox',
                ),
                'status' => array(
                    'type' => 'dropdownlist',
                    'class' => 'form-control',
                    'items' => array(
                        User::STATUS_ENABLED => Yii::t('AdminModule.controllers_UserController', 'Enabled'),
                        User::STATUS_DISABLED => Yii::t('AdminModule.controllers_UserController', 'Disabled'),
                        User::STATUS_NEED_APPROVAL => Yii::t('AdminModule.controllers_UserController', 'Unapproved'),
                    ),
                ),
            ),
        );

        // Add Profile Form
        $definition['elements']['Profile'] = array_merge(array('type' => 'form'), $profile->getFormDefinition());

        // Get Form Definition
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'label' => Yii::t('AdminModule.controllers_UserController', 'Save'),
                'class' => 'btn btn-primary',
            ),
            'become' => array(
                'type' => 'submit',
                'label' => Yii::t('AdminModule.controllers_UserController', 'Become this user'),
                'class' => 'btn btn-danger',
            ),
            'delete' => array(
                'type' => 'submit',
                'label' => Yii::t('AdminModule.controllers_UserController', 'Delete'),
                'class' => 'btn btn-danger',
            ),
        );

        $form = new HForm($definition);
        $form->models['User'] = $user;
        $form->models['Profile'] = $profile;

        if ($form->submitted('save') && $form->validate()) {
            if ($form->save()) {
                return $this->redirect(Url::toRoute('/admin/user'));
            }
        }

        // This feature is used primary for testing, maybe remove this in future
        if ($form->submitted('become')) {

            Yii::$app->user->switchIdentity($form->models['User']);
            return $this->redirect(Url::toRoute("/"));
        }

        if ($form->submitted('delete')) {
            return $this->redirect(Url::toRoute(['/admin/user/delete', 'id' => $user->id]));
        }

        return $this->render('edit', array('hForm' => $form));
    }

    public function actionAdd()
    {
        $registration = new Registration();
        $registration->enableEmailField = true;
        $registration->enableUserApproval = false;
        if ($registration->submitted('save') && $registration->validate() && $registration->register()) {
            return $this->redirect(Url::to(['edit', 'id' => $registration->getUser()->id]));
        }
        return $this->render('add', array('hForm' => $registration));
    }

    /**
     * Deletes a user permanently
     */
    public function actionDelete()
    {

        $id = (int) Yii::$app->request->get('id');
        $doit = (int) Yii::$app->request->get('doit');


        $user = User::findOne(['id' => $id]);

        if ($user == null) {
            throw new HttpException(404, Yii::t('AdminModule.controllers_UserController', 'User not found!'));
        } elseif (Yii::$app->user->id == $id) {
            throw new HttpException(400, Yii::t('AdminModule.controllers_UserController', 'You cannot delete yourself!'));
        }

        if ($doit == 2) {

            $this->forcePostRequest();

            foreach (\humhub\modules\space\models\Membership::GetUserSpaces($user->id) as $space) {
                if ($space->isSpaceOwner($user->id)) {
                    $space->addMember(Yii::$app->user->id);
                    $space->setSpaceOwner(Yii::$app->user->id);
                }
            }
            $user->delete();
            return $this->redirect(Url::to(['/admin/user']));
        }

        return $this->render('delete', array('model' => $user));
    }

}
