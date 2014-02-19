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
class ConfigController extends Controller {

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
    protected function beforeAction($action) {

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
    public function actionIndex() {

        if (HSetting::Get('name') == "") {
            HSetting::Set('name', "My HumHub Network");
        }

        $this->setupInitialData();

        $this->redirect(Yii::app()->createUrl('//installer/config/basic'));
    }

    /**
     * Basic Settings Form
     */
    public function actionBasic() {
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
    public function actionAdmin() {
        Yii::import('installer.forms.*');

        $user = new User('register');
        $user->group_id = Group::model()->findByAttributes(array())->id;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'admin-form') {
            echo CActiveForm::validate($user);
            Yii::app()->end();
        }
        if (isset($_POST['User'])) {
            $_POST['User'] = Yii::app()->input->stripClean($_POST['User']);
            $user->attributes = $_POST['User'];

            if ($user->validate()) {

                // Setup Application Secret
                if (HSetting::Get('secret') == "")
                    HSetting::Set('secret', UUID::v4());


                // Save Admin User
                $user->status = User::STATUS_ENABLED;
                $user->super_admin = true;
                $user->save();

                // Create Welcome Space
                $space = new Space();
                $space->name = 'Welcome Space';
                $space->join_policy = Space::JOIN_POLICY_FREE;
                $space->visibility = Space::VISIBILITY_ALL;
                $space->created_by = $user->id;
                $space->save();

                // Add Membership
                $membership = new UserSpaceMembership;
                $membership->space_id = $space->id;
                $membership->user_id = $user->id;
                $membership->status = UserSpaceMembership::STATUS_MEMBER;
                $membership->invite_role = 1;
                $membership->admin_role = 1;
                $membership->share_role = 1;
                $membership->save();

                // Add Some Post to the Space
                $post = new Post();
                $post->contentMeta->visibility = Content::VISIBILITY_PUBLIC;
                $post->contentMeta->space_id = $space->id;
                $post->message = "I´ve just installed HumHub - Yeah! :-)";
                $post->created_by = $user->id;
                $post->save();
                $post->contentMeta->addToWall($space->wall_id);

                // Set new DefaultSpace
                HSetting::Set('defaultSpaceId', $space->id);

                $this->redirect($this->createUrl('finished'));
            }
        }

        $this->render('admin', array('model' => $user));
    }

    /**
     * Last Step, finish up the installation
     */
    public function actionFinished() {

        // Should not happen
        if (HSetting::Get('secret') == "") {
            print "got no secret to finish!";
            die();
        }
        
        // Rewrite whole configuration file, also sets application
        // in installed state.
        HSetting::RewriteConfiguration();

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
    private function setupInitialData() {

        // Seems database is already initialized
        if (HSetting::Get('paginationSize') == 10) 
            return;
        
        // Rebuild Search
        HSearch::getInstance()->rebuild();
        HSetting::Set('baseUrl', Yii::app()->getBaseUrl(true));

        HSetting::Set('paginationSize', 10);

        // Authentication
        HSetting::Set('authInternal', '1', 'authentication');
        HSetting::Set('authLdap', '0', 'authentication');
        HSetting::Set('needApproval', '1', 'authentication_internal');
        HSetting::Set('anonymousRegistration', '1', 'authentication_internal');

        // Mailing
        HSetting::Set('transportType', 'php', 'mailing');
        HSetting::Set('systemEmailAddress', 'social@example.com', 'mailing');
        HSetting::Set('systemEmailName', 'My Social Network', 'mailing');

        // File
        HSetting::Set('maxFileSize', '1048576', 'file');
        HSetting::Set('forbiddenExtensions', 'exe', 'file');

        // Caching
        HSetting::Set('type', 'CFileCache', 'cache');
        HSetting::Set('expireTime', '3600', 'cache');

        // Design
        HSetting::Set('theme', "HumHub");


        // Add Categories
        $cGeneral = new ProfileFieldCategory;
        $cGeneral->title = "General";
        $cGeneral->sort_order = 100;
        $cGeneral->visibility = 1;
        $cGeneral->save();

        $cCommunication = new ProfileFieldCategory;
        $cCommunication->title = "Communication";
        $cCommunication->sort_order = 200;
        $cCommunication->visibility = 1;
        $cCommunication->save();

        $cSocial = new ProfileFieldCategory;
        $cSocial->title = "Social bookmarks";
        $cSocial->sort_order = 300;
        $cSocial->visibility = 1;
        $cSocial->save();

        // Add Fields
        $field = new ProfileField();
        $field->internal_name = "firstname";
        $field->title = 'Firstname';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "lastname";
        $field->title = 'Lastname';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "title";
        $field->title = 'Title';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "street";
        $field->title = 'Street';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
        if ($field->save()) {
            $field->fieldType->maxLength = 150;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "zip";
        $field->title = 'Zip';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeNumber';
        if ($field->save()) {
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "city";
        $field->title = 'City';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeText';
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
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "about";
        $field->title = 'About';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = 'ProfileFieldTypeTextArea';
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
        if ($field->save()) {
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "im_xmpp";
        $field->title = 'XMPP Jabber Address';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = 'ProfileFieldTypeText';
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
