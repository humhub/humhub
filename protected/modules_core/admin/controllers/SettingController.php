<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class SettingController extends Controller
{

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

    public function actionIndex()
    {
        $this->redirect($this->createUrl('basic'));
    }

    /**
     * Returns a List of Users
     */
    public function actionBasic()
    {
        Yii::import('admin.forms.*');

        $form = new BasicSettingsForm;
        $form->name = HSetting::Get('name');
        $form->baseUrl = HSetting::Get('baseUrl');
        $form->defaultLanguage = HSetting::Get('defaultLanguage');
        $form->dashboardShowProfilePostForm = HSetting::Get('showProfilePostForm', 'dashboard');
        $form->tour = HSetting::Get('enable', 'tour');
        
        $form->defaultSpaceGuid = "";
        foreach (Space::model()->findAllByAttributes(array('auto_add_new_members' => 1)) as $defaultSpace) {
            $form->defaultSpaceGuid .= $defaultSpace->guid . ",";
        }

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
                HSetting::Set('enable', $form->tour, 'tour');
                HSetting::Set('showProfilePostForm', $form->dashboardShowProfilePostForm, 'dashboard');

                $spaceGuids = explode(",", $form->defaultSpaceGuid);

                // Remove Old Default Spaces
                foreach (Space::model()->findAllByAttributes(array('auto_add_new_members' => 1)) as $space) {
                    if (!in_array($space->guid, $spaceGuids)) {
                        $space->auto_add_new_members = 0;
                        $space->save();
                    }
                }

                // Add new Default Spaces
                foreach ($spaceGuids as $spaceGuid) {
                    $space = Space::model()->findByAttributes(array('guid' => $spaceGuid));
                    if ($space != null && $space->auto_add_new_members != 1) {
                        $space->auto_add_new_members = 1;
                        $space->save();
                    }
                }

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
                $this->redirect(Yii::app()->createUrl('//admin/setting/basic'));
            }
        }
        $this->render('basic', array('model' => $form));
    }

    public function actionDeleteLogoImage()
    {
        $this->forcePostRequest();

        $image = NULL;

        $image = new LogoImage();

        if ($image->hasImage()) {
            $image->delete();
        }

        $this->renderJson(array());
    }

    /**
     * Returns a List of Users
     */
    public function actionAuthentication()
    {

        Yii::import('admin.forms.*');

        $form = new AuthenticationSettingsForm;
        $form->internalUsersCanInvite = HSetting::Get('internalUsersCanInvite', 'authentication_internal');
        $form->internalRequireApprovalAfterRegistration = HSetting::Get('needApproval', 'authentication_internal');
        $form->internalAllowAnonymousRegistration = HSetting::Get('anonymousRegistration', 'authentication_internal');
        $form->defaultUserGroup = HSetting::Get('defaultUserGroup', 'authentication_internal');
        $form->defaultUserIdleTimeoutSec = HSetting::Get('defaultUserIdleTimeoutSec', 'authentication_internal');
        $form->allowGuestAccess = HSetting::Get('allowGuestAccess', 'authentication_internal');
        $form->defaultUserProfileVisibility = HSetting::Get('defaultUserProfileVisibility', 'authentication_internal');

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'authentication-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['AuthenticationSettingsForm'])) {
            $_POST['AuthenticationSettingsForm'] = Yii::app()->input->stripClean($_POST['AuthenticationSettingsForm']);
            $form->attributes = $_POST['AuthenticationSettingsForm'];

            if ($form->validate()) {
                $form->internalUsersCanInvite = HSetting::Set('internalUsersCanInvite', $form->internalUsersCanInvite, 'authentication_internal');
                $form->internalRequireApprovalAfterRegistration = HSetting::Set('needApproval', $form->internalRequireApprovalAfterRegistration, 'authentication_internal');
                $form->internalAllowAnonymousRegistration = HSetting::Set('anonymousRegistration', $form->internalAllowAnonymousRegistration, 'authentication_internal');
                $form->defaultUserGroup = HSetting::Set('defaultUserGroup', $form->defaultUserGroup, 'authentication_internal');
                $form->defaultUserIdleTimeoutSec = HSetting::Set('defaultUserIdleTimeoutSec', $form->defaultUserIdleTimeoutSec, 'authentication_internal');
                $form->allowGuestAccess = HSetting::Set('allowGuestAccess', $form->allowGuestAccess, 'authentication_internal');

                if (HSetting::Get('allowGuestAccess', 'authentication_internal')) {
                    $form->defaultUserProfileVisibility = HSetting::Set('defaultUserProfileVisibility', $form->defaultUserProfileVisibility, 'authentication_internal');
                }

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/authentication'));
            }
        }

        // Build Group Dropdown
        $groups = array();
        $groups[''] = Yii::t('AdminModule.controllers_SettingController', 'None - shows dropdown in user registration.');
        foreach (Group::model()->findAll() as $group) {
            $groups[$group->id] = $group->name;
        }

        $this->render('authentication', array('model' => $form, 'groups' => $groups));
    }

    /**
     * Returns a List of Users
     */
    public function actionAuthenticationLdap()
    {

        Yii::import('admin.forms.*');

        $form = new AuthenticationLdapSettingsForm;

        // Load Defaults
        $form->enabled = HSetting::Get('enabled', 'authentication_ldap');
        $form->refreshUsers = HSetting::Get('refreshUsers', 'authentication_ldap');
        $form->username = HSetting::Get('username', 'authentication_ldap');
        $form->password = HSetting::Get('password', 'authentication_ldap');
        $form->hostname = HSetting::Get('hostname', 'authentication_ldap');
        $form->port = HSetting::Get('port', 'authentication_ldap');
        $form->encryption = HSetting::Get('encryption', 'authentication_ldap');
        $form->baseDn = HSetting::Get('baseDn', 'authentication_ldap');
        $form->loginFilter = HSetting::Get('loginFilter', 'authentication_ldap');
        $form->userFilter = HSetting::Get('userFilter', 'authentication_ldap');
        $form->usernameAttribute = HSetting::Get('usernameAttribute', 'authentication_ldap');

        if ($form->password != '')
            $form->password = '---hidden---';

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'authentication-ldap-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['AuthenticationLdapSettingsForm'])) {
            $_POST['AuthenticationLdapSettingsForm'] = Yii::app()->input->stripClean($_POST['AuthenticationLdapSettingsForm']);
            $form->attributes = $_POST['AuthenticationLdapSettingsForm'];

            if ($form->validate()) {
                HSetting::Set('enabled', $form->enabled, 'authentication_ldap');
                HSetting::Set('refreshUsers', $form->refreshUsers, 'authentication_ldap');
                HSetting::Set('hostname', $form->hostname, 'authentication_ldap');
                HSetting::Set('port', $form->port, 'authentication_ldap');
                HSetting::Set('encryption', $form->encryption, 'authentication_ldap');
                HSetting::Set('username', $form->username, 'authentication_ldap');
                if ($form->password != '---hidden---')
                    HSetting::Set('password', $form->password, 'authentication_ldap');
                HSetting::Set('baseDn', $form->baseDn, 'authentication_ldap');
                HSetting::Set('loginFilter', $form->loginFilter, 'authentication_ldap');
                HSetting::Set('userFilter', $form->userFilter, 'authentication_ldap');
                HSetting::Set('usernameAttribute', $form->usernameAttribute, 'authentication_ldap');

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/authenticationLdap'));
            }
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if (HSetting::Get('enabled', 'authentication_ldap')) {
            $enabled = true;
            try {
                if (HLdap::getInstance()->ldap !== null) {
                    $userCount = HLdap::getInstance()->ldap->count(HSetting::Get('userFilter', 'authentication_ldap'), HSetting::Get('baseDn', 'authentication_ldap'), Zend_Ldap::SEARCH_SCOPE_SUB);
                } else {
                    $errorMessage = Yii::t('AdminModule.controllers_SettingController', 'Could not load LDAP! - Check PHP Extension');
                }
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        $this->render('authentication_ldap', array('model' => $form, 'enabled' => $enabled, 'userCount' => $userCount, 'errorMessage' => $errorMessage));
    }

    /**
     * Caching Options
     */
    public function actionCaching()
    {

        Yii::import('admin.forms.*');

        $form = new CacheSettingsForm;
        $form->type = HSetting::Get('type', 'cache');
        $form->expireTime = HSetting::Get('expireTime', 'cache');

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'cache-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['CacheSettingsForm'])) {

            Yii::app()->cache->flush();
            ModuleManager::flushCache();

            // Delete also published assets
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Yii::app()->getAssetManager()->getBasePath(), FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {

                // Do not remove .gitignore in assets folder
                if ($path->getPathname() == Yii::app()->getAssetManager()->getBasePath() . DIRECTORY_SEPARATOR . '.gitignore') {
                    continue;
                }

                if ($path->isDir()) {
                    rmdir($path->getPathname());
                } else {
                    unlink($path->getPathname());
                }
            }


            $_POST['CacheSettingsForm'] = Yii::app()->input->stripClean($_POST['CacheSettingsForm']);
            $form->attributes = $_POST['CacheSettingsForm'];

            if ($form->validate()) {

                HSetting::Set('type', $form->type, 'cache');
                HSetting::Set('expireTime', $form->expireTime, 'cache');

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved and flushed cache'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/caching'));
            }
        }


        $cacheTypes = array(
            'CDummyCache' => Yii::t('AdminModule.controllers_SettingController', 'No caching (Testing only!)'),
            'CFileCache' => Yii::t('AdminModule.controllers_SettingController', 'File'),
            'CDbCache' => Yii::t('AdminModule.controllers_SettingController', 'Database'),
            'CApcCache' => Yii::t('AdminModule.controllers_SettingController', 'APC'),
        );

        $this->render('caching', array('model' => $form, 'cacheTypes' => $cacheTypes));
    }

    /**
     * Statistic Settings
     */
    public function actionStatistic()
    {
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

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

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
    public function actionMailing()
    {

        $model = new MailingDefaultsForm();

        $model->receive_email_activities = HSetting::Get("receive_email_activities", 'mailing');
        $model->receive_email_notifications = HSetting::Get("receive_email_notifications", 'mailing');

        if (isset($_POST['MailingDefaultsForm'])) {
            $model->attributes = Yii::app()->input->stripClean($_POST['MailingDefaultsForm']);

            if ($model->validate()) {

                HSetting::Set('receive_email_activities', $model->receive_email_activities, 'mailing');
                HSetting::Set('receive_email_notifications', $model->receive_email_notifications, 'mailing');

                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            }
        }


        $this->render('mailing', array('model' => $model));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailingServer()
    {
        Yii::import('admin.forms.*');

        $form = new MailingSettingsForm;
        $form->transportType = HSetting::Get('transportType', 'mailing');
        $form->hostname = HSetting::Get('hostname', 'mailing');
        $form->username = HSetting::Get('username', 'mailing');
        if (HSetting::Get('password', 'mailing') != '')
            $form->password = '---invisible---';

        $form->port = HSetting::Get('port', 'mailing');
        $form->encryption = HSetting::Get('encryption', 'mailing');
        $form->allowSelfSignedCerts = HSetting::Get('allowSelfSignedCerts', 'mailing');
        $form->systemEmailAddress = HSetting::Get('systemEmailAddress', 'mailing');
        $form->systemEmailName = HSetting::Get('systemEmailName', 'mailing');

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
                $form->allowSelfSignedCerts = HSetting::Set('allowSelfSignedCerts', $form->allowSelfSignedCerts, 'mailing');
                $form->systemEmailAddress = HSetting::Set('systemEmailAddress', $form->systemEmailAddress, 'mailing');
                $form->systemEmailName = HSetting::Set('systemEmailName', $form->systemEmailName, 'mailing');

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/mailingServer'));
            }
        }

        $encryptionTypes = array('' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS');
        $transportTypes = array('php' => 'PHP', 'smtp' => 'SMTP');

        $this->render('mailing_server', array('model' => $form, 'encryptionTypes' => $encryptionTypes, 'transportTypes' => $transportTypes));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionDesign()
    {
        Yii::import('admin.forms.*');

        $form = new DesignSettingsForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'design-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/uploadLogo.js');

        if (isset($_POST['DesignSettingsForm'])) {
            $_POST['DesignSettingsForm'] = Yii::app()->input->stripClean($_POST['DesignSettingsForm']);
            $form->attributes = $_POST['DesignSettingsForm'];

            $files = CUploadedFile::getInstancesByName('logo');
            if (count($files) != 0) {
                $file = $files[0];
                $form->logo = $file;
            }

            if ($form->validate()) {

                HSetting::Set('theme', $form->theme);
                HSetting::Set('paginationSize', $form->paginationSize);
                HSetting::Set('displayNameFormat', $form->displayName);
                HSetting::Set('spaceOrder', $form->spaceOrder, 'space');

                if ($form->logo) {
                    $logoImage = new LogoImage();
                    $logoImage->setNew($form->logo);
                }

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/design'));
            }
        } else {
            $form->theme = HSetting::Get('theme');
            $form->paginationSize = HSetting::Get('paginationSize');
            $form->displayName = HSetting::Get('displayNameFormat');
            $form->spaceOrder = HSetting::Get('spaceOrder', 'space');
        }

        $themes = HTheme::getThemes();
        //$themes[''] = Yii::t('AdminModule.controllers_SettingController', 'No theme');
        $this->render('design', array('model' => $form, 'themes' => $themes, 'logo' => new LogoImage()));
    }

    /**
     * Security Settings
     */
    public function actionSecurity()
    {

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
    public function actionLDAP()
    {
        $form = "";

        $this->render('ldap', array('model' => $form));
    }

    /**
     * File Settings
     */
    public function actionFile()
    {
        Yii::import('admin.forms.*');

        $form = new FileSettingsForm;
        $form->imageMagickPath = HSetting::Get('imageMagickPath', 'file');
        $form->maxFileSize = HSetting::Get('maxFileSize', 'file') / 1024 / 1024;
        $form->maxPreviewImageWidth = HSetting::Get('maxPreviewImageWidth', 'file');
        $form->maxPreviewImageHeight = HSetting::Get('maxPreviewImageHeight', 'file');
        $form->hideImageFileInfo = HSetting::Get('hideImageFileInfo', 'file');
        $form->useXSendfile = HSetting::Get('useXSendfile', 'file');
        $form->allowedExtensions = HSetting::GetText('allowedExtensions', 'file');
        $form->showFilesWidgetBlacklist = HSetting::GetText('showFilesWidgetBlacklist', 'file');

        // Ajax Validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'file-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['FileSettingsForm'])) {
            $form->attributes = $_POST['FileSettingsForm'];

            if ($form->validate()) {
                $form->imageMagickPath = HSetting::Set('imageMagickPath', $form->imageMagickPath, 'file');
                $form->maxFileSize = HSetting::Set('maxFileSize', $form->maxFileSize * 1024 * 1024, 'file');
                $form->maxPreviewImageWidth = HSetting::Set('maxPreviewImageWidth', $form->maxPreviewImageWidth, 'file');
                $form->maxPreviewImageHeight = HSetting::Set('maxPreviewImageHeight', $form->maxPreviewImageHeight, 'file');
                $form->hideImageFileInfo = HSetting::Set('hideImageFileInfo', $form->hideImageFileInfo, 'file');
                $form->useXSendfile = HSetting::Set('useXSendfile', $form->useXSendfile, 'file');
                $form->allowedExtensions = HSetting::SetText('allowedExtensions', strtolower($form->allowedExtensions), 'file');
                $form->showFilesWidgetBlacklist = HSetting::SetText('showFilesWidgetBlacklist', $form->showFilesWidgetBlacklist, 'file');

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/file'));
            }
        }

        // Determine PHP Upload Max FileSize
        $maxUploadSize = Helpers::GetBytesOfPHPIniValue(ini_get('upload_max_filesize'));
        if ($maxUploadSize > Helpers::GetBytesOfPHPIniValue(ini_get('post_max_size'))) {
            $maxUploadSize = Helpers::GetBytesOfPHPIniValue(ini_get('post_max_size'));
        }
        $maxUploadSize = floor($maxUploadSize / 1024 / 1024);

        // Determine currently used ImageLibary
        $currentImageLibary = 'GD';
        if (HSetting::Get('imageMagickPath', 'file'))
            $currentImageLibary = 'ImageMagick';

        $this->render('file', array('model' => $form, 'maxUploadSize' => $maxUploadSize, 'currentImageLibary' => $currentImageLibary));
    }

    /**
     * Caching Options
     */
    public function actionCronJob()
    {

        $this->render('cronjob', array(
        ));
    }

    /**
     * Proxy Settings
     */
    public function actionProxy()
    {
        Yii::import('admin.forms.*');

        $form = new ProxySettingsForm;

        // uncomment the following code to enable ajax-based validation
        /*        if (isset($_POST['ajax']) && $_POST['ajax'] === 'design-settings-form') {
          echo CActiveForm::validate($form);
          Yii::app()->end();
          } */

        if (isset($_POST['ProxySettingsForm'])) {
            $_POST['ProxySettingsForm'] = Yii::app()->input->stripClean($_POST['ProxySettingsForm']);
            $form->attributes = $_POST['ProxySettingsForm'];

            if ($form->validate()) {

                HSetting::Set('enabled', $form->enabled, 'proxy');
                HSetting::Set('server', $form->server, 'proxy');
                HSetting::Set('port', $form->port, 'proxy');
                HSetting::Set('user', $form->user, 'proxy');
                HSetting::Set('password', $form->password, 'proxy');
                HSetting::Set('noproxy', $form->noproxy, 'proxy');

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_ProxyController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//admin/setting/proxy'));
            }
        } else {
            $form->enabled = HSetting::Get('enabled', 'proxy');
            $form->server = HSetting::Get('server', 'proxy');
            $form->port = HSetting::Get('port', 'proxy');
            $form->user = HSetting::Get('user', 'proxy');
            $form->password = HSetting::Get('password', 'proxy');
            $form->noproxy = HSetting::Get('noproxy', 'proxy');
        }

        $this->render('proxy', array('model' => $form));
    }

    /**
     * List of OEmbed Providers
     */
    public function actionOEmbed()
    {
        $providers = UrlOembed::getProviders();
        $this->render('oembed', array('providers' => $providers));
    }

    /**
     * Add or edit an OEmbed Provider
     */
    public function actionOEmbedEdit()
    {

        $form = new OEmbedProviderForm;

        $prefix = Yii::app()->request->getParam('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            $form->prefix = $prefix;
            $form->endpoint = $providers[$prefix];
        }

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'oembed-edit-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['OEmbedProviderForm'])) {
            $_POST['OEmbedProviderForm'] = Yii::app()->input->stripClean($_POST['OEmbedProviderForm']);
            $form->attributes = $_POST['OEmbedProviderForm'];

            if ($form->validate()) {

                if ($prefix && isset($providers[$prefix])) {
                    unset($providers[$prefix]);
                }
                $providers[$form->prefix] = $form->endpoint;
                UrlOembed::setProviders($providers);

                $this->redirect(Yii::app()->createUrl('//admin/setting/oembed'));
            }
        }

        $this->render('oembed_edit', array('model' => $form, 'prefix' => $prefix));
    }

    /**
     * Deletes OEmbed Provider
     */
    public function actionOEmbedDelete()
    {

        $this->forcePostRequest();
        $prefix = Yii::app()->request->getParam('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            unset($providers[$prefix]);
            UrlOembed::setProviders($providers);
        }
        $this->redirect(Yii::app()->createUrl('//admin/setting/oembed'));
    }

    /**
     * Self Test
     */
    public function actionSelfTest()
    {
        Yii::import('application.commands.shell.HUpdateCommand');
        $migrate = HUpdateCommand::AutoUpdate();

        $this->render('selftest', array('checks' => SelfTest::getResults(), 'migrate' => $migrate,
        ));
    }

}
