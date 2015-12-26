<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\components\Controller;
use humhub\modules\user\models\Invite;
use humhub\compat\HForm;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;

/**
 * RegistrationController handles new user registration 
 * 
 * @since 1.1
 */
class RegistrationController extends Controller
{

    /**
     * @inheritdoc
     */
    public $layout = "@humhub/modules/user/views/layouts/main";

    /**
     * @inheritdoc
     */
    public $subLayout = "_layout";

    /**
     * Registration Form
     * 
     * @return type
     * @throws HttpException
     */
    public function actionIndex()
    {
        $needApproval = \humhub\models\Setting::Get('needApproval', 'authentication_internal');

        if (!Yii::$app->user->isGuest)
            throw new HttpException(401, 'Your are already logged in! - Logout first!');


        $userInvite = Invite::findOne(['token' => Yii::$app->request->get('token')]);
        if (!$userInvite)
            throw new HttpException(404, 'Token not found!');

        if ($userInvite->language)
            Yii::$app->language = $userInvite->language;

        $userModel = new User();
        $userModel->scenario = 'registration';
        $userModel->email = $userInvite->email;

        $userPasswordModel = new Password();
        $userPasswordModel->scenario = 'registration';

        $profileModel = $userModel->profile;
        $profileModel->scenario = 'registration';

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();


        $groupModels = \humhub\modules\user\models\Group::find()->orderBy('name ASC')->all();
        $defaultUserGroup = \humhub\models\Setting::Get('defaultUserGroup', 'authentication_internal');
        $groupFieldType = "dropdownlist";
        if ($defaultUserGroup != "") {
            $groupFieldType = "hidden";
        } else if (count($groupModels) == 1) {
            $groupFieldType = "hidden";
            $defaultUserGroup = $groupModels[0]->id;
        }
        if ($groupFieldType == 'hidden') {
            $userModel->group_id = $defaultUserGroup;
        }

        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
            'title' => Yii::t('UserModule.controllers_AuthController', 'Account'),
            'elements' => array(
                'username' => array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 25,
                ),
                'group_id' => array(
                    'type' => $groupFieldType,
                    'class' => 'form-control',
                    'items' => \yii\helpers\ArrayHelper::map($groupModels, 'id', 'name'),
                    'value' => $defaultUserGroup,
                ),
            ),
        );

        // Add User Password Form
        $definition['elements']['UserPassword'] = array(
            'type' => 'form',
            #'title' => 'Password',
            'elements' => array(
                'newPassword' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
                'newPasswordConfirm' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
            ),
        );

        // Add Profile Form
        $definition['elements']['Profile'] = array_merge(array('type' => 'form'), $profileModel->getFormDefinition());

        // Get Form Definition
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'class' => 'btn btn-primary',
                'label' => Yii::t('UserModule.controllers_AuthController', 'Create account'),
            ),
        );

        $form = new HForm($definition);
        $form->models['User'] = $userModel;
        $form->models['UserPassword'] = $userPasswordModel;
        $form->models['Profile'] = $profileModel;

        if ($form->submitted('save') && $form->validate()) {

            $this->forcePostRequest();

            // Registe User
            $form->models['User']->email = $userInvite->email;
            $form->models['User']->language = Yii::$app->language;
            if ($form->models['User']->save()) {

                // Save User Profile
                $form->models['Profile']->user_id = $form->models['User']->id;
                $form->models['Profile']->save();

                // Save User Password
                $form->models['UserPassword']->user_id = $form->models['User']->id;
                $form->models['UserPassword']->setPassword($form->models['UserPassword']->newPassword);
                $form->models['UserPassword']->save();

                // Autologin user
                if (!$needApproval) {
                    Yii::$app->user->switchIdentity($form->models['User']);
                    return $this->redirect(Url::to(['/dashboard/dashboard']));
                }

                return $this->render('success', array(
                            'form' => $form,
                            'needApproval' => $needApproval,
                ));
            }
        }

        return $this->render('index', array(
                    'hForm' => $form,
                    'needAproval' => $needApproval)
        );
    }

}

?>
