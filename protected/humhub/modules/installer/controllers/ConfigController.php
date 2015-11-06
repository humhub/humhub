<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use yii\helpers\Url;
use humhub\models\Setting;

/**
 * ConfigController allows inital configuration of humhub.
 * E.g. Name of Network, Root User
 *
 * ConfigController can only run after SetupController wrote the initial
 * configuration.
 *
 * @author luke
 */
class ConfigController extends Controller
{

    const EVENT_INSTALL_SAMPLE_DATA = 'install_sample_data';

    /**
     * Use Cases
     */
    const USECASE_SOCIAL_INTRANET = 'social_intranet';
    const USECASE_SOCIAL_COLLABORATION = 'social_collab';
    const USECASE_EDUCATION = 'club';
    const USECASE_COMMUNITY = 'community';
    const USECASE_OTHER = 'other';

    /**
     * Before each config controller action check if
     *  - Database Connection works
     *  - Database Migrated Up
     *  - Not already configured (e.g. update)
     *
     * @param boolean
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            // Flush Caches
            Yii::$app->cache->flush();

            // Database Connection seems not to work
            if (!$this->module->checkDBConnection()) {
                $this->redirect(Url::to(['/installer/setup']));
                return false;
            }

            // When not at index action, verify that database is not already configured
            if ($action->id != 'finished') {
                if ($this->module->isConfigured()) {
                    $this->redirect(Url::to(['finished']));
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Index is only called on fresh databases, when there are already settings
     * in database, the user will directly redirected to actionFinished()
     */
    public function actionIndex()
    {
        if (Setting::Get('name') == "") {
            Setting::Set('name', "HumHub");
        }

        \humhub\modules\installer\libs\InitialData::bootstrap();

        return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
    }

    /**
     * Basic Settings Form
     */
    public function actionBasic()
    {
        $form = new \humhub\modules\installer\forms\ConfigBasicForm();
        $form->name = Setting::Get('name');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('name', $form->name);
            Setting::Set('systemEmailName', $form->name, 'mailing');
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        return $this->render('basic', array('model' => $form));
    }

    /**
     * UseCase
     */
    public function actionUseCase()
    {
        $form = new \humhub\modules\installer\forms\UseCaseForm();
        $form->useCase = Setting::Get('useCase');
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('useCase', $form->useCase);
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        return $this->render('useCase', array('model' => $form));
    }

    /**
     * Security
     */
    public function actionSecurity()
    {
        $form = new \humhub\modules\installer\forms\SecurityForm();

        $form->allowGuestAccess = Setting::Get('allowGuestAccess', 'authentication_internal');
        $form->internalRequireApprovalAfterRegistration = Setting::Get('needApproval', 'authentication_internal');
        $form->internalAllowAnonymousRegistration = Setting::Get('anonymousRegistration', 'authentication_internal');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->internalRequireApprovalAfterRegistration = Setting::Set('needApproval', $form->internalRequireApprovalAfterRegistration, 'authentication_internal');
            $form->internalAllowAnonymousRegistration = Setting::Set('anonymousRegistration', $form->internalAllowAnonymousRegistration, 'authentication_internal');
            $form->allowGuestAccess = Setting::Set('allowGuestAccess', $form->allowGuestAccess, 'authentication_internal');
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        return $this->render('security', array('model' => $form));
    }

    /**
     * Modules
     */
    public function actionModules()
    {
        // Only showed purchased modules
        $marketplace = new \humhub\modules\admin\libs\OnlineModuleManager();
        $modules = $marketplace->getModules(false);
        foreach ($modules as $i => $module) {
            if (!isset($module['useCases']) || strpos($module['useCases'], Setting::Get('useCase')) === false) {
                unset($modules[$i]);
            }
        }

        if (Yii::$app->request->method == 'POST') {
            $enableModules = Yii::$app->request->post('enableModules');
            if (is_array($enableModules)) {
                foreach (array_keys($enableModules) as $moduleId) {
                    $marketplace->install($moduleId);
                    $module = Yii::$app->moduleManager->getModule($moduleId);
                    if ($module !== null) {
                        $module->enable();
                    }
                }
            }
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        /*
          if (Yii::$app->request->get('ok') == 1) {
          return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
          }
         */

        return $this->render('modules', array('modules' => $modules));
    }

    /**
     * Sample Data
     */
    public function actionSampleData()
    {
        if (Setting::Get('sampleData', 'installer') == 1) {
            // Sample Data already created
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        $form = new \humhub\modules\installer\forms\SampleDataForm();

        $form->sampleData = Setting::Get('sampleData', 'installer');
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('sampleData', $form->sampleData, 'installer');

            if (Setting::Get('sampleData', 'installer') == 1) {
                $this->trigger(self::EVENT_INSTALL_SAMPLE_DATA);
                // ToDo: Create Sample Data
            }

            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        return $this->render('sample-data', array('model' => $form));
    }

    /**
     * Setup Administrative User
     *
     * This should be the last step, before the user is created also the
     * application secret will created.
     */
    public function actionAdmin()
    {

        // Admin account already created
        if (User::find()->count() > 0) {
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }


        $userModel = new User();
        $userModel->scenario = 'registration';
        $userPasswordModel = new Password();
        $userPasswordModel->scenario = 'registration';
        $profileModel = $userModel->profile;
        $profileModel->scenario = 'registration';

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();

        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
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
                )
            ),
        );

        // Add User Password Form
        $definition['elements']['Password'] = array(
            'type' => 'form',
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
                'label' => Yii::t('InstallerModule.controllers_ConfigController', 'Create Admin Account'),
            ),
        );

        $form = new \humhub\compat\HForm($definition);
        $form->models['User'] = $userModel;
        $form->models['User']->group_id = 1;
        $form->models['Password'] = $userPasswordModel;
        $form->models['Profile'] = $profileModel;

        if ($form->submitted('save') && $form->validate()) {

            $form->models['User']->status = User::STATUS_ENABLED;
            $form->models['User']->super_admin = true;
            $form->models['User']->language = '';
            $form->models['User']->last_activity_email = new \yii\db\Expression('NOW()');
            $form->models['User']->save();

            $form->models['Profile']->user_id = $form->models['User']->id;
            $form->models['Profile']->title = "System Administration";
            $form->models['Profile']->save();

            // Save User Password
            $form->models['Password']->user_id = $form->models['User']->id;
            $form->models['Password']->setPassword($form->models['Password']->newPassword);
            $form->models['Password']->save();

            $userId = $form->models['User']->id;

            // Switch Identity
            Yii::$app->user->switchIdentity($form->models['User']);

            // Create Welcome Space
            $space = new Space();
            $space->name = 'Welcome Space';
            $space->description = 'Your first sample space to discover the platform.';
            $space->join_policy = Space::JOIN_POLICY_FREE;
            $space->visibility = Space::VISIBILITY_ALL;
            $space->created_by = $userId;
            $space->auto_add_new_members = 1;
            $space->save();

            $profileImage = new \humhub\libs\ProfileImage($space->guid);
            $profileImage->setNew(Yii::getAlias("@webroot/resources/installer/welcome_space.jpg"));

            // Add Some Post to the Space
            $post = new \humhub\modules\post\models\Post();
            $post->message = "Yay! I've just installed HumHub :-)";
            $post->content->container = $space;
            $post->content->visibility = \humhub\modules\content\models\Content::VISIBILITY_PUBLIC;
            $post->save();

            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        return $this->render('admin', array('hForm' => $form));
    }

    public function actionFinish()
    {
        if (Setting::Get('secret') == "") {
            Setting::Set('secret', \humhub\libs\UUID::v4());
        }

        $this->redirect(['finished']);
    }

    /**
     * Last Step, finish up the installation
     */
    public function actionFinished()
    {
        // Should not happen
        if (Setting::Get('secret') == "") {
            throw new CException("Finished without secret setting!");
        }

        Setting::Set('timeZone', Yii::$app->timeZone);

        // Set to installed
        $this->module->setInstalled();

        try {
            Yii::$app->user->logout();
        } catch (Exception $e) {
            ;
        }
        return $this->render('finished');
    }

}
