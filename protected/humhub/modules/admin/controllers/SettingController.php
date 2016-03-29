<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\libs\DynamicConfig;
use humhub\models\Setting;
use humhub\models\UrlOembed;
use humhub\modules\admin\components\Controller;
use humhub\modules\user\libs\Ldap;

/**
 * SettingController 
 * 
 * @since 0.5
 */
class SettingController extends Controller
{

    public function actionIndex()
    {
        Yii::$app->response->redirect(Url::toRoute('basic'));
    }

    /**
     * Returns a List of Users
     */
    public function actionBasic()
    {
        $form = new \humhub\modules\admin\models\forms\BasicSettingsForm;
        $form->name = Setting::Get('name');
        $form->baseUrl = Setting::Get('baseUrl');
        $form->defaultLanguage = Setting::Get('defaultLanguage');
        $form->timeZone = Setting::Get('timeZone');
        $form->dashboardShowProfilePostForm = Setting::Get('showProfilePostForm', 'dashboard');
        $form->tour = Setting::Get('enable', 'tour');
        $form->share = Setting::Get('enable', 'share');

        $form->defaultSpaceGuid = "";
        foreach (\humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]) as $defaultSpace) {
            $form->defaultSpaceGuid .= $defaultSpace->guid . ",";
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('name', $form->name);
            Setting::Set('baseUrl', $form->baseUrl);
            Setting::Set('defaultLanguage', $form->defaultLanguage);
            Setting::Set('timeZone', $form->timeZone);
            Setting::Set('enable', $form->tour, 'tour');
            Setting::Set('enable', $form->share, 'share');
            Setting::Set('showProfilePostForm', $form->dashboardShowProfilePostForm, 'dashboard');

            $spaceGuids = explode(",", $form->defaultSpaceGuid);

            // Remove Old Default Spaces
            foreach (\humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]) as $space) {
                if (!in_array($space->guid, $spaceGuids)) {
                    $space->auto_add_new_members = 0;
                    $space->save();
                }
            }

            // Add new Default Spaces
            foreach ($spaceGuids as $spaceGuid) {
                $space = \humhub\modules\space\models\Space::findOne(['guid' => $spaceGuid]);
                if ($space != null && $space->auto_add_new_members != 1) {
                    $space->auto_add_new_members = 1;
                    $space->save();
                }
            }
            DynamicConfig::rewrite();

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return Yii::$app->response->redirect(Url::toRoute('/admin/setting/basic'));
        }

        return $this->render('basic', array('model' => $form));
    }

    public function actionDeleteLogoImage()
    {
        $this->forcePostRequest();
        $image = new \humhub\libs\LogoImage();

        if ($image->hasImage()) {
            $image->delete();
        }

        \Yii::$app->response->format = 'json';
        return [];
    }

    /**
     * Returns a List of Users
     */
    public function actionAuthentication()
    {
        $form = new \humhub\modules\admin\models\forms\AuthenticationSettingsForm;
        $form->internalUsersCanInvite = Setting::Get('internalUsersCanInvite', 'authentication_internal');
        $form->internalRequireApprovalAfterRegistration = Setting::Get('needApproval', 'authentication_internal');
        $form->internalAllowAnonymousRegistration = Setting::Get('anonymousRegistration', 'authentication_internal');
        $form->defaultUserGroup = Setting::Get('defaultUserGroup', 'authentication_internal');
        $form->defaultUserIdleTimeoutSec = Setting::Get('defaultUserIdleTimeoutSec', 'authentication_internal');
        $form->allowGuestAccess = Setting::Get('allowGuestAccess', 'authentication_internal');
        $form->defaultUserProfileVisibility = Setting::Get('defaultUserProfileVisibility', 'authentication_internal');


        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('internalUsersCanInvite', $form->internalUsersCanInvite, 'authentication_internal');
            Setting::Set('needApproval', $form->internalRequireApprovalAfterRegistration, 'authentication_internal');
            Setting::Set('anonymousRegistration', $form->internalAllowAnonymousRegistration, 'authentication_internal');
            Setting::Set('defaultUserGroup', $form->defaultUserGroup, 'authentication_internal');
            Setting::Set('defaultUserIdleTimeoutSec', $form->defaultUserIdleTimeoutSec, 'authentication_internal');
            Setting::Set('allowGuestAccess', $form->allowGuestAccess, 'authentication_internal');

            if (Setting::Get('allowGuestAccess', 'authentication_internal')) {
                Setting::Set('defaultUserProfileVisibility', $form->defaultUserProfileVisibility, 'authentication_internal');
            }

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            #Yii::$app->response->redirect(Url::toRoute('/admin/setting/authentication'));
        }

        // Build Group Dropdown
        $groups = array();
        $groups[''] = Yii::t('AdminModule.controllers_SettingController', 'None - shows dropdown in user registration.');
        foreach (\humhub\modules\user\models\Group::find()->all() as $group) {
            $groups[$group->id] = $group->name;
        }

        return $this->render('authentication', array('model' => $form, 'groups' => $groups));
    }

    /**
     * Returns a List of Users
     */
    public function actionAuthenticationLdap()
    {

        $form = new \humhub\modules\admin\models\forms\AuthenticationLdapSettingsForm;

        // Load Defaults
        $form->enabled = Setting::Get('enabled', 'authentication_ldap');
        $form->refreshUsers = Setting::Get('refreshUsers', 'authentication_ldap');
        $form->username = Setting::Get('username', 'authentication_ldap');
        $form->password = Setting::Get('password', 'authentication_ldap');
        $form->hostname = Setting::Get('hostname', 'authentication_ldap');
        $form->port = Setting::Get('port', 'authentication_ldap');
        $form->encryption = Setting::Get('encryption', 'authentication_ldap');
        $form->baseDn = Setting::Get('baseDn', 'authentication_ldap');
        $form->loginFilter = Setting::Get('loginFilter', 'authentication_ldap');
        $form->userFilter = Setting::Get('userFilter', 'authentication_ldap');
        $form->usernameAttribute = Setting::Get('usernameAttribute', 'authentication_ldap');
        $form->emailAttribute = Setting::Get('emailAttribute', 'authentication_ldap');

        if ($form->password != '')
            $form->password = '---hidden---';

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('enabled', $form->enabled, 'authentication_ldap');
            Setting::Set('refreshUsers', $form->refreshUsers, 'authentication_ldap');
            Setting::Set('hostname', $form->hostname, 'authentication_ldap');
            Setting::Set('port', $form->port, 'authentication_ldap');
            Setting::Set('encryption', $form->encryption, 'authentication_ldap');
            Setting::Set('username', $form->username, 'authentication_ldap');
            if ($form->password != '---hidden---')
                Setting::Set('password', $form->password, 'authentication_ldap');
            Setting::Set('baseDn', $form->baseDn, 'authentication_ldap');
            Setting::Set('loginFilter', $form->loginFilter, 'authentication_ldap');
            Setting::Set('userFilter', $form->userFilter, 'authentication_ldap');
            Setting::Set('usernameAttribute', $form->usernameAttribute, 'authentication_ldap');
            Setting::Set('emailAttribute', $form->emailAttribute, 'authentication_ldap');

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            Yii::$app->response->redirect(Url::toRoute('/admin/setting/authentication-ldap'));
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if (Setting::Get('enabled', 'authentication_ldap')) {
            $enabled = true;
            try {
                if (Ldap::getInstance()->ldap !== null) {
                    $userCount = Ldap::getInstance()->ldap->count(Setting::Get('userFilter', 'authentication_ldap'), Setting::Get('baseDn', 'authentication_ldap'), \Zend\Ldap\Ldap::SEARCH_SCOPE_SUB);
                } else {
                    $errorMessage = Yii::t('AdminModule.controllers_SettingController', 'Could not load LDAP! - Check PHP Extension');
                }
            } catch (\Zend\Ldap\Exception\LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('authentication_ldap', array('model' => $form, 'enabled' => $enabled, 'userCount' => $userCount, 'errorMessage' => $errorMessage));
    }

    public function actionLdapRefresh() {
        Ldap::getInstance()->refreshUsers();
        Yii::$app->response->redirect(Url::toRoute('/admin/setting/authentication-ldap'));
    }
    
    /**
     * Caching Options
     */
    public function actionCaching()
    {
        $form = new \humhub\modules\admin\models\forms\CacheSettingsForm;
        $form->type = Setting::Get('type', 'cache');
        $form->expireTime = Setting::Get('expireTime', 'cache');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            Yii::$app->cache->flush();
            Setting::Set('type', $form->type, 'cache');
            Setting::Set('expireTime', $form->expireTime, 'cache');

            \humhub\libs\DynamicConfig::rewrite();

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved and flushed cache'));

            return Yii::$app->response->redirect(Url::toRoute('/admin/setting/caching'));
        }

        $cacheTypes = array(
            'yii\caching\DummyCache' => Yii::t('AdminModule.controllers_SettingController', 'No caching (Testing only!)'),
            'yii\caching\FileCache' => Yii::t('AdminModule.controllers_SettingController', 'File'),
            'yii\caching\ApcCache' => Yii::t('AdminModule.controllers_SettingController', 'APC'),
        );

        return $this->render('caching', array('model' => $form, 'cacheTypes' => $cacheTypes));
    }

    /**
     * Statistic Settings
     */
    public function actionStatistic()
    {
        $form = new \humhub\modules\admin\models\forms\StatisticSettingsForm;
        $form->trackingHtmlCode = Setting::GetText('trackingHtmlCode');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->trackingHtmlCode = Setting::SetText('trackingHtmlCode', $form->trackingHtmlCode);
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            Yii::$app->response->redirect(Url::toRoute('/admin/setting/statistic'));
        }

        return $this->render('statistic', array('model' => $form));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailing()
    {
        $model = new \humhub\modules\admin\models\forms\MailingDefaultsForm();

        $model->receive_email_activities = Setting::Get("receive_email_activities", 'mailing');
        $model->receive_email_notifications = Setting::Get("receive_email_notifications", 'mailing');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            Setting::Set('receive_email_activities', $model->receive_email_activities, 'mailing');
            Setting::Set('receive_email_notifications', $model->receive_email_notifications, 'mailing');

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
        }

        return $this->render('mailing', array('model' => $model));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailingServer()
    {
        $form = new \humhub\modules\admin\models\forms\MailingSettingsForm;
        $form->transportType = Setting::Get('transportType', 'mailing');
        $form->hostname = Setting::Get('hostname', 'mailing');
        $form->username = Setting::Get('username', 'mailing');
        if (Setting::Get('password', 'mailing') != '')
            $form->password = '---invisible---';

        $form->port = Setting::Get('port', 'mailing');
        $form->encryption = Setting::Get('encryption', 'mailing');
        $form->allowSelfSignedCerts = Setting::Get('allowSelfSignedCerts', 'mailing');
        $form->systemEmailAddress = Setting::Get('systemEmailAddress', 'mailing');
        $form->systemEmailName = Setting::Get('systemEmailName', 'mailing');


        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->transportType = Setting::Set('transportType', $form->transportType, 'mailing');
            $form->hostname = Setting::Set('hostname', $form->hostname, 'mailing');
            $form->username = Setting::Set('username', $form->username, 'mailing');
            if ($form->password != '---invisible---')
                $form->password = Setting::Set('password', $form->password, 'mailing');
            $form->port = Setting::Set('port', $form->port, 'mailing');
            $form->encryption = Setting::Set('encryption', $form->encryption, 'mailing');
            $form->allowSelfSignedCerts = Setting::Set('allowSelfSignedCerts', $form->allowSelfSignedCerts, 'mailing');
            $form->systemEmailAddress = Setting::Set('systemEmailAddress', $form->systemEmailAddress, 'mailing');
            $form->systemEmailName = Setting::Set('systemEmailName', $form->systemEmailName, 'mailing');

            DynamicConfig::rewrite();

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            Yii::$app->response->redirect(Url::toRoute('/admin/setting/mailing-server'));
        }

        $encryptionTypes = array('' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS');
        $transportTypes = array('file' => 'File (Use for testing/development)', 'php' => 'PHP', 'smtp' => 'SMTP');

        return $this->render('mailing_server', array('model' => $form, 'encryptionTypes' => $encryptionTypes, 'transportTypes' => $transportTypes));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionDesign()
    {
        $form = new \humhub\modules\admin\models\forms\DesignSettingsForm;
        $form->theme = Setting::Get('theme');
        $form->paginationSize = Setting::Get('paginationSize');
        $form->displayName = Setting::Get('displayNameFormat');
        $form->spaceOrder = Setting::Get('spaceOrder', 'space');
        $form->dateInputDisplayFormat = Setting::Get('defaultDateInputFormat', 'admin');

        if ($form->load(Yii::$app->request->post())) {

            $files = \yii\web\UploadedFile::getInstancesByName('logo');
            if (count($files) != 0) {
                $file = $files[0];
                $form->logo = $file;
            }

            if ($form->validate()) {
                Setting::Set('theme', $form->theme);
                Setting::Set('paginationSize', $form->paginationSize);
                Setting::Set('displayNameFormat', $form->displayName);
                Setting::Set('spaceOrder', $form->spaceOrder, 'space');
                Setting::Set('defaultDateInputFormat', $form->dateInputDisplayFormat, 'admin');

                if ($form->logo) {
                    $logoImage = new \humhub\libs\LogoImage();
                    $logoImage->setNew($form->logo);
                }

                // read and save colors from current theme
                \humhub\components\Theme::setColorVariables($form->theme);

                DynamicConfig::rewrite();

                Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
                Yii::$app->response->redirect(Url::toRoute('/admin/setting/design'));
            }
        }

        $themes = [];
        foreach (\humhub\components\Theme::getThemes() as $theme) {
            $themes[$theme->name] = $theme->name;
        }

        return $this->render('design', array('model' => $form, 'themes' => $themes, 'logo' => new \humhub\libs\LogoImage()));
    }

    /**
     * Security Settings
     */
    public function actionSecurity()
    {
        $form = new \humhub\modules\admin\models\forms\SecuritySettingsForm;
        $form->canAdminAlwaysDeleteContent = Setting::Get('canAdminAlwaysDeleteContent', 'security');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->canAdminAlwaysDeleteContent = Setting::Set('canAdminAlwaysDeleteContent', $form->canAdminAlwaysDeleteContent, 'security');
            Yii::$app->response->redirect(Url::toRoute('/admin/setting/security'));
        }
        return $this->render('security', array('model' => $form));
    }

    /**
     * File Settings
     */
    public function actionFile()
    {
        $form = new \humhub\modules\admin\models\forms\FileSettingsForm;
        $form->imageMagickPath = Setting::Get('imageMagickPath', 'file');
        $form->maxFileSize = Setting::Get('maxFileSize', 'file') / 1024 / 1024;
        $form->maxPreviewImageWidth = Setting::Get('maxPreviewImageWidth', 'file');
        $form->maxPreviewImageHeight = Setting::Get('maxPreviewImageHeight', 'file');
        $form->hideImageFileInfo = Setting::Get('hideImageFileInfo', 'file');
        $form->useXSendfile = Setting::Get('useXSendfile', 'file');
        $form->allowedExtensions = Setting::GetText('allowedExtensions', 'file');
        $form->showFilesWidgetBlacklist = Setting::GetText('showFilesWidgetBlacklist', 'file');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $new = $form->maxFileSize * 1024 * 1024;
            Setting::Set('imageMagickPath', $form->imageMagickPath, 'file');
            Setting::Set('maxFileSize', $new, 'file');
            Setting::Set('maxPreviewImageWidth', $form->maxPreviewImageWidth, 'file');
            Setting::Set('maxPreviewImageHeight', $form->maxPreviewImageHeight, 'file');
            Setting::Set('hideImageFileInfo', $form->hideImageFileInfo, 'file');
            Setting::Set('useXSendfile', $form->useXSendfile, 'file');
            Setting::SetText('allowedExtensions', strtolower($form->allowedExtensions), 'file');
            Setting::SetText('showFilesWidgetBlacklist', $form->showFilesWidgetBlacklist, 'file');

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            return Yii::$app->response->redirect(Url::toRoute('/admin/setting/file'));
        }

        // Determine PHP Upload Max FileSize
        $maxUploadSize = \humhub\libs\Helpers::GetBytesOfPHPIniValue(ini_get('upload_max_filesize'));
        if ($maxUploadSize > \humhub\libs\Helpers::GetBytesOfPHPIniValue(ini_get('post_max_size'))) {
            $maxUploadSize = \humhub\libs\Helpers::GetBytesOfPHPIniValue(ini_get('post_max_size'));
        }
        $maxUploadSize = floor($maxUploadSize / 1024 / 1024);

        // Determine currently used ImageLibary
        $currentImageLibary = 'GD';
        if (Setting::Get('imageMagickPath', 'file'))
            $currentImageLibary = 'ImageMagick';

        return $this->render('file', array('model' => $form, 'maxUploadSize' => $maxUploadSize, 'currentImageLibary' => $currentImageLibary));
    }

    /**
     * Caching Options
     */
    public function actionCronjob()
    {
        return $this->render('cronjob', array());
    }

    /**
     * Proxy Settings
     */
    public function actionProxy()
    {
        $form = new \humhub\modules\admin\models\forms\ProxySettingsForm;
        $form->enabled = Setting::Get('enabled', 'proxy');
        $form->server = Setting::Get('server', 'proxy');
        $form->port = Setting::Get('port', 'proxy');
        $form->user = Setting::Get('user', 'proxy');
        $form->password = Setting::Get('password', 'proxy');
        $form->noproxy = Setting::Get('noproxy', 'proxy');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('enabled', $form->enabled, 'proxy');
            Setting::Set('server', $form->server, 'proxy');
            Setting::Set('port', $form->port, 'proxy');
            Setting::Set('user', $form->user, 'proxy');
            Setting::Set('password', $form->password, 'proxy');
            Setting::Set('noproxy', $form->noproxy, 'proxy');

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_ProxyController', 'Saved'));
            return Yii::$app->response->redirect(Url::toRoute('/admin/setting/proxy'));
        }

        return $this->render('proxy', array('model' => $form));
    }

    /**
     * List of OEmbed Providers
     */
    public function actionOembed()
    {
        $providers = UrlOembed::getProviders();
        return $this->render('oembed', array('providers' => $providers));
    }

    /**
     * Add or edit an OEmbed Provider
     */
    public function actionOembedEdit()
    {

        $form = new \humhub\modules\admin\models\forms\OEmbedProviderForm;

        $prefix = Yii::$app->request->get('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            $form->prefix = $prefix;
            $form->endpoint = $providers[$prefix];
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($prefix && isset($providers[$prefix])) {
                unset($providers[$prefix]);
            }
            $providers[$form->prefix] = $form->endpoint;
            UrlOembed::setProviders($providers);

            return Yii::$app->response->redirect(Url::toRoute('/admin/setting/oembed'));
        }

        return $this->render('oembed_edit', array('model' => $form, 'prefix' => $prefix));
    }

    /**
     * Deletes OEmbed Provider
     */
    public function actionOembedDelete()
    {

        $this->forcePostRequest();
        $prefix = Yii::$app->request->get('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            unset($providers[$prefix]);
            UrlOembed::setProviders($providers);
        }
        return Yii::$app->response->redirect(Url::toRoute('/admin/setting/oembed'));
    }

    /**
     * Self Test
     */
    public function actionSelfTest()
    {
        return $this->render('selftest', array('checks' => \humhub\libs\SelfTest::getResults(), 'migrate' => \humhub\commands\MigrateController::webMigrateAll()));
    }

}
