<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Group;

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
    const USECASE_SOCIAL_INTRANET = 'intranet';
    const USECASE_EDUCATION = 'education';
    const USECASE_CLUB = 'club';
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
                $this->redirect(['/installer/setup']);
                return false;
            }

            // When not at index action, verify that database is not already configured
            if ($action->id != 'finished') {
                if ($this->module->isConfigured()) {
                    $this->redirect(['finished']);
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
        if (Yii::$app->settings->get('name') == "") {
            Yii::$app->settings->set('name', "HumHub");
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
        $form->name = Yii::$app->settings->get('name');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->settings->set('name', $form->name);
            Yii::$app->settings->set('mailer.systemEmailName', $form->name);
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
        $form->useCase = Yii::$app->settings->get('useCase');
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->settings->set('useCase', $form->useCase);
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

        if (Yii::$app->settings->get("useCase") == self::USECASE_SOCIAL_INTRANET) {
            $form->allowGuestAccess = false;
            $form->internalRequireApprovalAfterRegistration = false;
            $form->internalAllowAnonymousRegistration = false;
            $form->canInviteExternalUsersByEmail = false;
            $form->enableFriendshipModule = false;
        }

        if (Yii::$app->settings->get("useCase") == self::USECASE_EDUCATION) {
            $form->allowGuestAccess = false;
            $form->internalRequireApprovalAfterRegistration = true;
            $form->internalAllowAnonymousRegistration = true;
            $form->canInviteExternalUsersByEmail = false;
            $form->enableFriendshipModule = false;
        }

        if (Yii::$app->settings->get("useCase") == self::USECASE_CLUB) {
            $form->allowGuestAccess = false;
            $form->internalRequireApprovalAfterRegistration = false;
            $form->internalAllowAnonymousRegistration = false;
            $form->canInviteExternalUsersByEmail = true;
            $form->enableFriendshipModule = true;
        }

        if (Yii::$app->settings->get("useCase") == self::USECASE_COMMUNITY) {
            $form->allowGuestAccess = true;
            $form->internalRequireApprovalAfterRegistration = false;
            $form->internalAllowAnonymousRegistration = true;
            $form->canInviteExternalUsersByEmail = true;
            $form->enableFriendshipModule = false;
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->getModule('user')->settings->set('auth.needApproval', $form->internalRequireApprovalAfterRegistration);
            Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', $form->internalAllowAnonymousRegistration);
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', $form->allowGuestAccess);
            Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInvite', $form->canInviteExternalUsersByEmail);
            Yii::$app->getModule('friendship')->settings->set('enable', $form->enableFriendshipModule);
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        if (Yii::$app->settings->get("useCase") == self::USECASE_OTHER) {
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        } else {
            return $this->render('security', array('model' => $form));
        }
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
            if (!isset($module['useCases']) || strpos($module['useCases'], Yii::$app->settings->get('useCase')) === false) {
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
        if (Yii::$app->settings->get("useCase") == self::USECASE_OTHER) {
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        } else {
            return $this->render('modules', array('modules' => $modules));
        }
    }

    /**
     * Sample Data
     */
    public function actionSampleData()
    {
        if (Yii::$app->getModule('installer')->settings->get('sampleData') == 1) {
            // Sample Data already created
            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        $form = new \humhub\modules\installer\forms\SampleDataForm();

        $form->sampleData = 1;
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->getModule('installer')->settings->set('sampleData', $form->sampleData);

            if (Yii::$app->getModule('installer')->settings->get('sampleData') == 1) {

                // Add sample image to admin
                $admin = User::find()->where(['id' => 1])->one();
                $adminImage = new \humhub\libs\ProfileImage($admin->guid);
                $adminImage->setNew(Yii::getAlias("@webroot-static/resources/installer/user_male_1.jpg"));

                // Create second user
                $userModel = new User();
                $userModel->scenario = 'registration';
                $profileModel = $userModel->profile;
                $profileModel->scenario = 'registration';

                $userModel->status = User::STATUS_ENABLED;
                $userModel->username = "david1986";
                $userModel->email = "david.roberts@example.com";
                $userModel->language = '';
                $userModel->tags = "Microsoft Office, Marketing, SEM, Digital Native";
                $userModel->save();

                $profileImage = new \humhub\libs\ProfileImage($userModel->guid);
                $profileImage->setNew(Yii::getAlias("@webroot-static/resources/installer/user_male_2.jpg"));

                $profileModel->user_id = $userModel->id;
                $profileModel->firstname = "David";
                $profileModel->lastname = "Roberts";
                $profileModel->title = "Late riser";
                $profileModel->street = "2443 Queens Lane";
                $profileModel->zip = "24574";
                $profileModel->city = "Allwood";
                $profileModel->country = "Virginia";
                $profileModel->save();

                // Create third user
                $userModel2 = new User();
                $userModel2->scenario = 'registration';
                $profileModel2 = $userModel2->profile;
                $profileModel2->scenario = 'registration';

                $userModel2->status = User::STATUS_ENABLED;
                $userModel2->username = "sara1989";
                $userModel2->email = "sara.schuster@example.com";
                $userModel2->language = '';
                $userModel2->tags = "Yoga, Travel, English, German, French";
                $userModel2->save();

                $profileImage2 = new \humhub\libs\ProfileImage($userModel2->guid);
                $profileImage2->setNew(Yii::getAlias("@webroot-static/resources/installer/user_female_1.jpg"));

                $profileModel2->user_id = $userModel2->id;
                $profileModel2->firstname = "Sara";
                $profileModel2->lastname = "Schuster";
                $profileModel2->title = "Do-gooder";
                $profileModel2->street = "Schmarjestrasse 51";
                $profileModel2->zip = "17095";
                $profileModel2->city = "Friedland";
                $profileModel2->country = "Niedersachsen";
                $profileModel2->save();

                // Switch Identity
                $user = User::find()->where(['id' => 1])->one();
                Yii::$app->user->switchIdentity($user);


                $space = Space::find()->where(['id' => 1])->one();

                // Create a sample post
                $post = new \humhub\modules\post\models\Post();
                $post->message = Yii::t("InstallerModule.controllers_ConfigController", "We're looking for great slogans of famous brands. Maybe you can come up with some samples?");
                $post->content->container = $space;
                $post->content->visibility = \humhub\modules\content\models\Content::VISIBILITY_PRIVATE;
                $post->save();

                // Switch Identity
                Yii::$app->user->switchIdentity($userModel);

                $comment = new \humhub\modules\comment\models\Comment();
                $comment->message = Yii::t("InstallerModule.controllers_ConfigController", "Nike – Just buy it. ;Wink;");
                $comment->object_model = $post->className();
                $comment->object_id = $post->getPrimaryKey();
                $comment->save();

                // Switch Identity
                Yii::$app->user->switchIdentity($userModel2);

                $comment2 = new \humhub\modules\comment\models\Comment();
                $comment2->message = Yii::t("InstallerModule.controllers_ConfigController", "Calvin Klein – Between love and madness lies obsession.");
                $comment2->object_model = $post->className();
                $comment2->object_id = $post->getPrimaryKey();
                $comment2->save();

                // Create Like Object
                $like = new \humhub\modules\like\models\Like();
                $like->object_model = $comment->className();
                $like->object_id = $comment->getPrimaryKey();
                $like->save();

                $like = new \humhub\modules\like\models\Like();
                $like->object_model = $post->className();
                $like->object_id = $post->getPrimaryKey();
                $like->save();

                // trigger install sample data event
                $this->trigger(self::EVENT_INSTALL_SAMPLE_DATA);
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
        $userModel->scenario = 'registration_email';
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
        $form->models['Password'] = $userPasswordModel;
        $form->models['Profile'] = $profileModel;

        if ($form->submitted('save') && $form->validate()) {

            $form->models['User']->status = User::STATUS_ENABLED;
            $form->models['User']->language = '';
            $form->models['User']->tags = 'Administration, Support, HumHub';
            $form->models['User']->save();

            $form->models['Profile']->user_id = $form->models['User']->id;
            $form->models['Profile']->title = "System Administration";
            $form->models['Profile']->save();

            // Save User Password
            $form->models['Password']->user_id = $form->models['User']->id;
            $form->models['Password']->setPassword($form->models['Password']->newPassword);
            $form->models['Password']->save();

            $userId = $form->models['User']->id;

            Group::getAdminGroup()->addUser($form->models['User']);


            // Reload User
            $adminUser = User::findOne(['id' => 1]);


            // Switch Identity
            Yii::$app->user->switchIdentity($adminUser);

            // Create Welcome Space
            $space = new Space();
            $space->name = Yii::t("InstallerModule.controllers_ConfigController", "Welcome Space");
            $space->description = Yii::t("InstallerModule.controllers_ConfigController", "Your first sample space to discover the platform.");
            $space->join_policy = Space::JOIN_POLICY_FREE;
            $space->visibility = Space::VISIBILITY_ALL;
            $space->created_by = $adminUser->id;
            $space->auto_add_new_members = 1;
            $space->color = '#6fdbe8';
            $space->save();

            // activate all available modules for this space
            foreach ($space->getAvailableModules() as $module) {
                $space->enableModule($module->id);
            }

            // Add Some Post to the Space
            $post = new \humhub\modules\post\models\Post();
            $post->message = Yii::t("InstallerModule.controllers_ConfigController", "Yay! I've just installed HumHub ;Cool;");
            $post->content->container = $space;
            $post->content->visibility = \humhub\modules\content\models\Content::VISIBILITY_PUBLIC;
            $post->save();

            return $this->redirect(Yii::$app->getModule('installer')->getNextConfigStepUrl());
        }

        return $this->render('admin', array('hForm' => $form));
    }

    public function actionFinish()
    {
        if (Yii::$app->settings->get('secret') == "") {
            Yii::$app->settings->set('secret', \humhub\libs\UUID::v4());
        }

        \humhub\libs\DynamicConfig::rewrite();

        return $this->redirect(['finished']);
    }

    /**
     * Last Step, finish up the installation
     */
    public function actionFinished()
    {
        // Should not happen
        if (Yii::$app->settings->get('secret') == "") {
            throw new CException("Finished without secret setting!");
        }

        Yii::$app->settings->set('timeZone', Yii::$app->timeZone);

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
