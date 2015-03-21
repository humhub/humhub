<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

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

    /**
     * @var String layout to use
     */
    public $layout = '_layout';

    /**
     * Before each config controller action check if
     *  - Database Connection works
     *  - Database Migrated Up
     *  - Not already configured (e.g. update)
     *
     * @param type $action
     */
    protected function beforeAction($action)
    {

        // Flush Caches
        Yii::app()->cache->flush();

        // Database Connection seems not to work
        if (!$this->getModule()->checkDBConnection()) {
            $this->redirect(Yii::app()->createUrl('//installer/setup/'));
        }

        // When not at index action, verify that database is not already configured
        if ($action->id != 'finished') {
            if ($this->getModule()->isConfigured()) {
                $this->redirect($this->createUrl('finished'));
            }
        }

        return true;
    }

    /**
     * Index is only called on fresh databases, when there are already settings
     * in database, the user will directly redirected to actionFinished()
     */
    public function actionIndex()
    {

        if (HSetting::Get('name') == "") {
            HSetting::Set('name', "HumHub");
        }

        $this->setupInitialData();

        $this->redirect(Yii::app()->createUrl('//installer/config/basic'));
    }

    /**
     * Basic Settings Form
     */
    public function actionBasic()
    {
        Yii::import('installer.forms.*');

        $form = new ConfigBasicForm;
        $form->name = HSetting::Get('name');

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'basic-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['ConfigBasicForm'])) {
            $_POST['ConfigBasicForm'] = Yii::app()->input->stripClean($_POST['ConfigBasicForm']);
            $form->attributes = $_POST['ConfigBasicForm'];

            if ($form->validate()) {
                // Set some default settings
                HSetting::Set('name', $form->name);
                HSetting::Set('systemEmailName', $form->name, 'mailing');
                $this->redirect(Yii::app()->createUrl('//installer/config/admin'));
            }
        }

        $this->render('basic', array('model' => $form));
    }

    /**
     * Setup Administrative User
     *
     * This should be the last step, before the user is created also the
     * application secret will created.
     */
    public function actionAdmin()
    {
        Yii::import('installer.forms.*');

        $userModel = new User('register');
        $userPasswordModel = new UserPassword('newPassword');
        $profileModel = $userModel->profile;
        $profileModel->scenario = 'register';

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();

        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
            #'title' => 'Account',
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
        $definition['elements']['UserPassword'] = array(
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

        $form = new HForm($definition);
        $form['User']->model = $userModel;
        $form['User']->model->group_id = 1;
        $form['UserPassword']->model = $userPasswordModel;
        $form['Profile']->model = $profileModel;

        if (isset($_POST['Profile'])) {
            $_POST['Profile'] = Yii::app()->input->stripClean($_POST['Profile']);
        }
        
        if (isset($_GET['Profile'])) {
            $_GET['Profile'] = Yii::app()->input->stripClean($_GET['Profile']);        
        }

        if ($form->submitted('save') && $form->validate()) {
            $this->forcePostRequest();

            if (HSetting::Get('secret') == "") {
                HSetting::Set('secret', UUID::v4());
            }

            $form['User']->model->status = User::STATUS_ENABLED;
            $form['User']->model->super_admin = true;
            $form['User']->model->language = '';
            $form['User']->model->last_activity_email = new CDbExpression('NOW()');
            $form['User']->model->save();

            $form['Profile']->model->user_id = $form['User']->model->id;
            $form['Profile']->model->title = "System Administration";
            $form['Profile']->model->save();

            // Save User Password
            $form['UserPassword']->model->user_id = $form['User']->model->id;
            $form['UserPassword']->model->setPassword($form['UserPassword']->model->newPassword);
            $form['UserPassword']->model->save();

            $userId = $form['User']->model->id;

            // Switch Identity
            Yii::import('application.modules_core.user.components.*');
            $newIdentity = new UserIdentity($form['User']->model->username, '');
            $newIdentity->fakeAuthenticate();
            Yii::app()->user->login($newIdentity);

            // Create Welcome Space
            $space = new Space();
            $space->name = 'Welcome Space';
            $space->description = 'Your first sample space to discover the platform.';
            $space->join_policy = Space::JOIN_POLICY_FREE;
            $space->visibility = Space::VISIBILITY_ALL;
            $space->created_by = $userId;
            $space->auto_add_new_members = 1;
            $space->save();

            $profileImage = new ProfileImage($space->guid);
            $profileImage->setNew($this->getModule()->getPath() . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . 'welcome_space.jpg');

            // Add Some Post to the Space
            $post = new Post();
            $post->message = "Yay! I've just installed HumHub :-)";
            $post->content->container = $space;
            $post->content->visibility = Content::VISIBILITY_PUBLIC;
            $post->save();

            $this->redirect($this->createUrl('finished'));
        }

        $this->render('admin', array('form' => $form));
    }

    /**
     * Last Step, finish up the installation
     */
    public function actionFinished()
    {

        // Should not happen
        if (HSetting::Get('secret') == "") {
            throw new CException("Finished without secret setting!");
        }

        // Rewrite whole configuration file, also sets application
        // in installed state.
        HSetting::RewriteConfiguration();

        // Set to installed
        $this->module->setInstalled();

        try {
            Yii::app()->user->logout();
        } catch (Exception $e) {
            ;
        }
        $this->render('finished');
    }

    /**
     * Setup some inital database settings.
     *
     * This will be done at the first step.
     */
    private function setupInitialData()
    {

        // Seems database is already initialized
        if (HSetting::Get('paginationSize') == 10)
            return;

        // Rebuild Search
        
        HSearch::getInstance()->rebuild();   
        HSetting::Set('baseUrl', Yii::app()->getBaseUrl(true));
        HSetting::Set('paginationSize', 10);
        HSetting::Set('displayNameFormat', '{profile.firstname} {profile.lastname}');

        // Authentication
        HSetting::Set('authInternal', '1', 'authentication');
        HSetting::Set('authLdap', '0', 'authentication');
        HSetting::Set('refreshUsers', '1', 'authentication_ldap');
        HSetting::Set('needApproval', '0', 'authentication_internal');
        HSetting::Set('anonymousRegistration', '1', 'authentication_internal');
        HSetting::Set('internalUsersCanInvite', '1', 'authentication_internal');

        // Mailing
        HSetting::Set('transportType', 'php', 'mailing');
        HSetting::Set('systemEmailAddress', 'social@example.com', 'mailing');
        HSetting::Set('systemEmailName', 'My Social Network', 'mailing');
        HSetting::Set('receive_email_activities', User::RECEIVE_EMAIL_DAILY_SUMMARY, 'mailing');
        HSetting::Set('receive_email_notifications', User::RECEIVE_EMAIL_WHEN_OFFLINE, 'mailing');

        // File
        HSetting::Set('maxFileSize', '1048576', 'file');
        HSetting::Set('maxPreviewImageWidth', '200', 'file');
        HSetting::Set('maxPreviewImageHeight', '200', 'file');
        HSetting::Set('hideImageFileInfo', '0', 'file');

        // Caching
        HSetting::Set('type', 'CFileCache', 'cache');
        HSetting::Set('expireTime', '3600', 'cache');
        HSetting::Set('installationId', md5(uniqid("", true)), 'admin');

        // Design
        HSetting::Set('theme', "HumHub");
        HSetting::Set('spaceOrder', 0, 'space');

        // Basic
        HSetting::Set('enable', 1, 'tour');
        HSetting::Set('defaultLanguage', Yii::app()->getLanguage());

        // Notification
        HSetting::Set('enable_html5_desktop_notifications', 0, 'notification');

        // Add Categories
        $cGeneral = new ProfileFieldCategory;
        $cGeneral->title = "General";
        $cGeneral->sort_order = 100;
        $cGeneral->visibility = 1;
        $cGeneral->is_system = 1;
        $cGeneral->description = '';
        $cGeneral->save();

        $cCommunication = new ProfileFieldCategory;
        $cCommunication->title = "Communication";
        $cCommunication->sort_order = 200;
        $cCommunication->visibility = 1;
        $cCommunication->is_system = 1;
        $cCommunication->description = '';
        $cCommunication->save();

        $cSocial = new ProfileFieldCategory;
        $cSocial->title = "Social bookmarks";
        $cSocial->sort_order = 300;
        $cSocial->visibility = 1;
        $cSocial->is_system = 1;
        $cSocial->description = '';
        $cSocial->save();

        // Add Fields
        $field = new ProfileField();
        $field->internal_name = "firstname";
        $field->title = 'Firstname';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->ldap_attribute = 'givenName';
        $field->is_system = 1;
        $field->required = 1;
        $field->show_at_registration = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 20;
            $field->fieldType->save();
        } else {
            throw new CHttpException(500, print_r($field->getErrors(), true));
        }

        $field = new ProfileField();
        $field->internal_name = "lastname";
        $field->title = 'Lastname';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->ldap_attribute = 'sn';
        $field->show_at_registration = 1;
        $field->required = 1;
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 30;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "title";
        $field->title = 'Title';
        $field->sort_order = 300;
        $field->ldap_attribute = 'title';
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 50;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "gender";
        $field->title = 'Gender';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeSelect';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->options = "male=>Male\nfemale=>Female\ncustom=>Custom";
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "street";
        $field->title = 'Street';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 150;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "zip";
        $field->title = 'Zip';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cGeneral->id;
        $field->is_system = 1;
        $field->field_type_class = 'ProfileFieldTypeText';
        if ($field->save()) {
            $field->fieldType->maxLength = 10;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "city";
        $field->title = 'City';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "country";
        $field->title = 'Country';
        $field->sort_order = 700;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "state";
        $field->title = 'State';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "birthday";
        $field->title = 'Birthday';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeBirthday';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "about";
        $field->title = 'About';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeTextArea';
        $field->is_system = 1;
        if ($field->save()) {
            #$field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "phone_private";
        $field->title = 'Phone Private';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "phone_work";
        $field->title = 'Phone Work';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "mobile";
        $field->title = 'Mobile';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "fax";
        $field->title = 'Fax';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "im_skype";
        $field->title = 'Skype Nickname';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "im_msn";
        $field->title = 'MSN';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "im_icq";
        $field->title = 'ICQ Number';
        $field->sort_order = 700;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeNumber';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "im_xmpp";
        $field->title = 'XMPP Jabber Address';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'email';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url";
        $field->title = 'Url';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_facebook";
        $field->title = 'Facebook URL';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_linkedin";
        $field->title = 'LinkedIn URL';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_xing";
        $field->title = 'Xing URL';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_youtube";
        $field->title = 'Youtube URL';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_vimeo";
        $field->title = 'Vimeo URL';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_flickr";
        $field->title = 'Flickr URL';
        $field->sort_order = 700;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_myspace";
        $field->title = 'MySpace URL';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_googleplus";
        $field->title = 'Google+ URL';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_twitter";
        $field->title = 'Twitter URL';
        $field->sort_order = 1000;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $group = new Group();
        $group->name = "Users";
        $group->description = "Example Group by Installer";
        $group->save();
    }

}
