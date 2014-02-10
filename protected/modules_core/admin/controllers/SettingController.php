<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class SettingController extends Controller {

    public $subLayout = "/_layout";

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
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Returns a List of Users
     */
    public function actionIndex() {
        Yii::import('admin.forms.*');

        $form = new BasicSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'basic-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['BasicSettingsForm'])) {
            $_POST['BasicSettingsForm'] = Yii::app()->input->stripClean($_POST['BasicSettingsForm']);
            $form->attributes = $_POST['BasicSettingsForm'];

            if ($form->validate()) {

                HSetting::Set('name', $form->name);
                HSetting::Set('baseUrl', $form->baseUrl);
                HSetting::Set('defaultLanguage', $form->defaultLanguage);

                if ($form->defaultSpaceGuid != "") {
                    $space = Space::model()->findByAttributes(array('guid' => $form->defaultSpaceGuid));
                    if ($space != null) {
                        HSetting::Set('defaultSpaceId', $space->id);
                    }
                }
                $this->redirect(Yii::app()->createUrl('//admin/setting/index'));
            }
        } else {
            $form->name = HSetting::Get('name');
            $form->baseUrl = HSetting::Get('baseUrl');
            $form->defaultLanguage = HSetting::Get('defaultLanguage');

            if (HSetting::Get('defaultSpaceId') != "") {
                $space = Space::model()->findByPk(HSetting::Get('defaultSpaceId'));
                if ($space != null)
                    $form->defaultSpaceGuid = $space->guid;
            }
        }

        $this->render('index', array('model' => $form));

        /*
          Yii::app()->interceptor->attachEventHandler('SuperAdminNavigationWidget', 'onInit', function($event) {
          $event->sender->addItem(array(
          'label' => 'Item Dynamic Inject Test',
          'url' => Yii::app()->createUrl('')
          ));
          });
         */
    }

    /**
     * Returns a List of Users
     */
    public function actionAuthentication() {

        Yii::import('admin.forms.*');

        $form = new AuthenticationSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'authentication-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['AuthenticationSettingsForm'])) {
            $_POST['AuthenticationSettingsForm'] = Yii::app()->input->stripClean($_POST['AuthenticationSettingsForm']);
            $form->attributes = $_POST['AuthenticationSettingsForm'];

            if ($form->validate()) {

                $form->authInternal = HSetting::Set('authInternal', $form->authInternal, 'authentication');
                $form->authLdap = HSetting::Set('authLdap', $form->authLdap, 'authentication');

                $form->internalUsersCanInvite = HSetting::Set('internalUsersCanInvite', $form->internalUsersCanInvite, 'authentication_internal');
                $form->internalRequireApprovalAfterRegistration = HSetting::Set('needApproval', $form->internalRequireApprovalAfterRegistration, 'authentication_internal');
                $form->internalAllowAnonymousRegistration = HSetting::Set('anonymousRegistration', $form->internalAllowAnonymousRegistration, 'authentication_internal');

                $this->redirect(Yii::app()->createUrl('//admin/setting/authentication'));
            }
        } else {
            $form->authInternal = HSetting::Get('authInternal', 'authentication');
            $form->authLdap = HSetting::Get('authLdap', 'authentication');

            $form->internalUsersCanInvite = HSetting::Get('internalUsersCanInvite', 'authentication_internal');
            $form->internalRequireApprovalAfterRegistration = HSetting::Get('needApproval', 'authentication_internal');
            $form->internalAllowAnonymousRegistration = HSetting::Get('anonymousRegistration', 'authentication_internal');
        }

        $this->render('authentication', array('model' => $form));
    }

    /**
     * Caching Options
     */
    public function actionCaching() {

        Yii::import('admin.forms.*');

        $form = new CacheSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'cache-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['CacheSettingsForm'])) {

            Yii::app()->cache->flush();
            Yii::app()->moduleManager->flushCache();

            $_POST['CacheSettingsForm'] = Yii::app()->input->stripClean($_POST['CacheSettingsForm']);
            $form->attributes = $_POST['CacheSettingsForm'];

            if ($form->validate()) {

                $form->type = HSetting::Set('type', $form->type, 'cache');
                $form->expireTime = HSetting::Set('expireTime', $form->expireTime, 'cache');

                $this->redirect(Yii::app()->createUrl('//admin/setting/caching'));
            }
        } else {
            $form->type = HSetting::Get('type', 'cache');
            $form->expireTime = HSetting::Get('expireTime', 'cache');
        }

        $cacheTypes = array(
            'CDummyCache' => Yii::t('AdminModule.base', 'No caching (Testing only!)'),
            'CFileCache' => Yii::t('AdminModule.base', 'File'),
            'CDbCache' => Yii::t('AdminModule.base', 'Database'),
            'CApcCache' => Yii::t('AdminModule.base', 'APC'),
        );

        $this->render('caching', array('model' => $form, 'cacheTypes' => $cacheTypes));
    }

    /**
     * Statistic Settings
     */
    public function actionStatistic() {
        Yii::import('admin.forms.*');

        $form = new StatisticSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'statistic-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['StatisticSettingsForm'])) {
            #$_POST['StatisticSettingsForm'] = Yii::app()->input->stripClean($_POST['StatisticSettingsForm']);
            $form->attributes = $_POST['StatisticSettingsForm'];

            if ($form->validate()) {

                $form->trackingHtmlCode = HSetting::SetText('trackingHtmlCode', $form->trackingHtmlCode);
                $this->redirect(Yii::app()->createUrl('//admin/setting/statistic'));
            }
        } else {
            $form->trackingHtmlCode = HSetting::GetText('trackingHtmlCode');
        }

        $this->render('statistic', array('model' => $form));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailing() {
        Yii::import('admin.forms.*');

        $form = new MailingSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'mailing-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['MailingSettingsForm'])) {
            $_POST['MailingSettingsForm'] = Yii::app()->input->stripClean($_POST['MailingSettingsForm']);
            $form->attributes = $_POST['MailingSettingsForm'];

            if ($form->validate()) {

                $form->transportType = HSetting::Set('transportType', $form->transportType, 'mailing');
                $form->hostname = HSetting::Set('hostname', $form->hostname, 'mailing');
                $form->username = HSetting::Set('username', $form->username, 'mailing');
                if ($form->password != '---invisible---')
                    $form->password = HSetting::Set('password', $form->password, 'mailing');
                $form->port = HSetting::Set('port', $form->port, 'mailing');
                $form->encryption = HSetting::Set('encryption', $form->encryption, 'mailing');
                $form->systemEmailAddress = HSetting::Set('systemEmailAddress', $form->systemEmailAddress, 'mailing');
                $form->systemEmailName = HSetting::Set('systemEmailName', $form->systemEmailName, 'mailing');

                $this->redirect(Yii::app()->createUrl('//admin/setting/mailing'));
            }
        } else {
            $form->transportType = HSetting::Get('transportType', 'mailing');
            $form->hostname = HSetting::Get('hostname', 'mailing');
            $form->username = HSetting::Get('username', 'mailing');
            #$form->password = HSetting::Get('password', 'mailing');
            $form->password = '---invisible---';
            $form->port = HSetting::Get('port', 'mailing');
            $form->encryption = HSetting::Get('encryption', 'mailing');
            $form->systemEmailAddress = HSetting::Get('systemEmailAddress', 'mailing');
            $form->systemEmailName = HSetting::Get('systemEmailName', 'mailing');
        }

        $encryptionTypes = array('' => 'None', 'ssl' => 'SSL');
        $transportTypes = array('php' => 'PHP', 'smtp' => 'SMTP');

        $this->render('mailing', array('model' => $form, 'encryptionTypes' => $encryptionTypes, 'transportTypes' => $transportTypes));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionDesign() {
        Yii::import('admin.forms.*');

        $form = new DesignSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'design-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['DesignSettingsForm'])) {
            $_POST['DesignSettingsForm'] = Yii::app()->input->stripClean($_POST['DesignSettingsForm']);
            $form->attributes = $_POST['DesignSettingsForm'];

            if ($form->validate()) {

                $form->theme = HSetting::Set('theme', $form->theme);
                $form->paginationSize = HSetting::Set('paginationSize', $form->paginationSize);
                $form->displayName = HSetting::Set('displayNameFormat', $form->displayName);

                $this->redirect(Yii::app()->createUrl('//admin/setting/design'));
            }
        } else {
            $form->theme = HSetting::Get('theme');
            $form->paginationSize = HSetting::Get('paginationSize');
            $form->displayName = HSetting::Get('displayNameFormat');
        }

        $themes = HTheme::getThemes();
        $themes[''] = Yii::t('AdminModule.base', 'No theme');
        $this->render('design', array('model' => $form, 'themes' => $themes));
    }

    /**
     * Security Settings
     */
    public function actionSecurity() {

        Yii::import('admin.forms.*');

        $form = new SecuritySettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'security-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['SecuritySettingsForm'])) {
            $_POST['SecuritySettingsForm'] = Yii::app()->input->stripClean($_POST['SecuritySettingsForm']);
            $form->attributes = $_POST['SecuritySettingsForm'];

            if ($form->validate()) {

                $form->canAdminAlwaysDeleteContent = HSetting::Set('canAdminAlwaysDeleteContent', $form->canAdminAlwaysDeleteContent, 'security');

                $this->redirect(Yii::app()->createUrl('//admin/setting/security'));
            }
        } else {
            $form->canAdminAlwaysDeleteContent = HSetting::Get('canAdminAlwaysDeleteContent', 'security');
        }

        $this->render('security', array('model' => $form));
    }

    /**
     * LDAP Settings
     */
    public function actionLDAP() {
        $form = "";

        $this->render('ldap', array('model' => $form));
    }

    /**
     * File Settings
     */
    public function actionFile() {
        Yii::import('admin.forms.*');

        $form = new FileSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'file-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['FileSettingsForm'])) {
            $_POST['FileSettingsForm'] = Yii::app()->input->stripClean($_POST['FileSettingsForm']);
            $form->attributes = $_POST['FileSettingsForm'];

            if ($form->validate()) {

                $form->imageMagickPath = HSetting::Set('imageMagickPath', $form->imageMagickPath, 'file');
                $form->maxFileSize = HSetting::Set('maxFileSize', $form->maxFileSize, 'file');
                $form->useXSendfile = HSetting::Set('useXSendfile', $form->useXSendfile, 'file');
                $form->forbiddenExtensions = HSetting::Set('forbiddenExtensions', strtolower($form->forbiddenExtensions), 'file');

                $this->redirect(Yii::app()->createUrl('//admin/setting/file'));
            }
        } else {
            $form->imageMagickPath = HSetting::Get('imageMagickPath', 'file');
            $form->maxFileSize = HSetting::Get('maxFileSize', 'file');
            $form->useXSendfile = HSetting::Get('useXSendfile', 'file');
            $form->forbiddenExtensions = HSetting::Get('forbiddenExtensions', 'file');
        }

        $this->render('file', array('model' => $form));
    }

    /**
     * Caching Options
     */
    public function actionCronJob() {

        $this->render('cronjob', array(
        ));
    }

    /**
     * Self Test
     */
    public function actionSelfTest() {
        Yii::import('application.commands.shell.ZMigrateCommand');
        $migrate = ZMigrateCommand::AutoMigrate();

        $this->render('selftest', array('checks' => SelfTest::getResults(), 'migrate' => $migrate,
        ));
    }

}
