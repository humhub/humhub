<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\libs\DynamicConfig;
use humhub\libs\ThemeHelper;
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'basic' => Yii::t('AdminModule.base', 'Basic'),
            'authentication' => Yii::t('AdminModule.base', 'Authentication'),
            'authentication-ldap' => Yii::t('AdminModule.base', 'Authentication'),
            'caching' => Yii::t('AdminModule.base', 'Caching'),
            'statistic' => Yii::t('AdminModule.base', 'Statistics'),
            'mailing' => Yii::t('AdminModule.base', 'Mailing'),
            'mailing-server' => Yii::t('AdminModule.base', 'Mailing'),
            'design' => Yii::t('AdminModule.base', 'Design'),
            'security' => Yii::t('AdminModule.base', 'Security'),
            'file' => Yii::t('AdminModule.base', 'Files'),
            'cronjobs' => Yii::t('AdminModule.base', 'Cronjobs'),
            'proxy' => Yii::t('AdminModule.base', 'Proxy'),
            'oembed' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'oembed-edit' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'self-test' => Yii::t('AdminModule.base', 'Self test'),
        ]);
        return parent::init();
    }

    public function actionIndex()
    {
        return $this->redirect(['basic']);
    }

    /**
     * Returns a List of Users
     */
    public function actionBasic()
    {
        $form = new \humhub\modules\admin\models\forms\BasicSettingsForm();
        $form->name = Yii::$app->settings->get('name');
        $form->baseUrl = Yii::$app->settings->get('baseUrl');
        $form->defaultLanguage = Yii::$app->settings->get('defaultLanguage');
        $form->timeZone = Yii::$app->settings->get('timeZone');
        $form->dashboardShowProfilePostForm = Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm');
        $form->tour = Yii::$app->getModule('tour')->settings->get('enable');
        $form->share = Yii::$app->getModule('dashboard')->settings->get('share.enable');
        $form->enableFriendshipModule = Yii::$app->getModule('friendship')->settings->get('enable');

        $form->defaultSpaceGuid = "";
        foreach (\humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]) as $defaultSpace) {
            $form->defaultSpaceGuid .= $defaultSpace->guid . ",";
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->settings->get('name', $form->name);
            Yii::$app->settings->get('baseUrl', $form->baseUrl);
            Yii::$app->settings->get('defaultLanguage', $form->defaultLanguage);
            Yii::$app->settings->get('timeZone', $form->timeZone);
            Yii::$app->getModule('dashboard')->settings->set('showProfilePostForm', $form->dashboardShowProfilePostForm);
            Yii::$app->getModule('tour')->settings->set('enable', $form->tour);
            Yii::$app->getModule('dashboard')->settings->set('share.enable', $form->share);
            Yii::$app->getModule('friendship')->settings->set('enable', $form->enableFriendshipModule);

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
            return $this->redirect(['/admin/setting/basic']);
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
        $form->internalUsersCanInvite = Setting::Get('auth.internalUsersCanInvite', 'user');
        $form->internalRequireApprovalAfterRegistration = Setting::Get('auth.needApproval', 'user');
        $form->internalAllowAnonymousRegistration = Setting::Get('auth.anonymousRegistration', 'user');
        $form->defaultUserGroup = Setting::Get('auth.defaultUserGroup', 'user');
        $form->defaultUserIdleTimeoutSec = Setting::Get('auth.defaultUserIdleTimeoutSec', 'user');
        $form->allowGuestAccess = Setting::Get('auth.allowGuestAccess', 'user');
        $form->defaultUserProfileVisibility = Setting::Get('auth.defaultUserProfileVisibility', 'user');


        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('auth.internalUsersCanInvite', $form->internalUsersCanInvite, 'user');
            Setting::Set('auth.needApproval', $form->internalRequireApprovalAfterRegistration, 'user');
            Setting::Set('auth.anonymousRegistration', $form->internalAllowAnonymousRegistration, 'user');
            Setting::Set('auth.defaultUserGroup', $form->defaultUserGroup, 'user');
            Setting::Set('auth.defaultUserIdleTimeoutSec', $form->defaultUserIdleTimeoutSec, 'user');
            Setting::Set('auth.allowGuestAccess', $form->allowGuestAccess, 'user');

            if (Setting::Get('auth.allowGuestAccess', 'user')) {
                Setting::Set('auth.defaultUserProfileVisibility', $form->defaultUserProfileVisibility, 'user');
            }

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            #Yii::$app->response->redirect(Url::toRoute('/admin/setting/authentication'));
        }

        // Build Group Dropdown
        $groups = array();
        $groups[''] = Yii::t('AdminModule.controllers_SettingController', 'None - shows dropdown in user registration.');
        foreach (\humhub\modules\user\models\Group::find()->all() as $group) {
            if (!$group->is_admin_group) {
                $groups[$group->id] = $group->name;
            }
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
        $form->enabled = Setting::Get('auth.ldap.enabled', 'user');
        $form->refreshUsers = Setting::Get('auth.ldap.refreshUsers', 'user');
        $form->username = Setting::Get('auth.ldap.username', 'user');
        $form->password = Setting::Get('auth.ldap.password', 'user');
        $form->hostname = Setting::Get('auth.ldap.hostname', 'user');
        $form->port = Setting::Get('auth.ldap.port', 'user');
        $form->encryption = Setting::Get('auth.ldap.encryption', 'user');
        $form->baseDn = Setting::Get('auth.ldap.baseDn', 'user');
        $form->loginFilter = Setting::Get('auth.ldap.loginFilter', 'user');
        $form->userFilter = Setting::Get('auth.ldap.userFilter', 'user');
        $form->usernameAttribute = Setting::Get('auth.ldap.usernameAttribute', 'user');
        $form->emailAttribute = Setting::Get('auth.ldap.emailAttribute', 'user');

        if ($form->password != '')
            $form->password = '---hidden---';

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('auth.ldap.enabled', $form->enabled, 'user');
            Setting::Set('auth.ldap.refreshUsers', $form->refreshUsers, 'user');
            Setting::Set('auth.ldap.hostname', $form->hostname, 'user');
            Setting::Set('auth.ldap.port', $form->port, 'user');
            Setting::Set('auth.ldap.encryption', $form->encryption, 'user');
            Setting::Set('auth.ldap.username', $form->username, 'user');
            if ($form->password != '---hidden---')
                Setting::Set('auth.ldap.password', $form->password, 'user');
            Setting::Set('auth.ldap.baseDn', $form->baseDn, 'user');
            Setting::Set('auth.ldap.loginFilter', $form->loginFilter, 'user');
            Setting::Set('auth.ldap.userFilter', $form->userFilter, 'user');
            Setting::Set('auth.ldap.usernameAttribute', $form->usernameAttribute, 'user');
            Setting::Set('auth.ldap.emailAttribute', $form->emailAttribute, 'user');

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            return $this->redirect(['/admin/setting/authentication-ldap']);
        }


        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if (Setting::Get('auth.ldap.enabled', 'user')) {
            $enabled = true;
            try {
                $ldapAuthClient = new \humhub\modules\user\authclient\ZendLdapClient();
                $ldap = $ldapAuthClient->getLdap();
                $userCount = $ldap->count(Setting::Get('auth.ldap.userFilter', 'user'), Setting::Get('auth.ldap.baseDn', 'user'), \Zend\Ldap\Ldap::SEARCH_SCOPE_SUB);
            } catch (\Zend\Ldap\Exception\LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('authentication_ldap', array('model' => $form, 'enabled' => $enabled, 'userCount' => $userCount, 'errorMessage' => $errorMessage));
    }

    public function actionLdapRefresh()
    {
        Ldap::getInstance()->refreshUsers();
        return $this->redirect(['/admin/setting/authentication-ldap']);
    }

    /**
     * Caching Options
     */
    public function actionCaching()
    {
        $form = new \humhub\modules\admin\models\forms\CacheSettingsForm;
        $form->type = Setting::Get('cache.class');
        $form->expireTime = Setting::Get('cache.expireTime');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            Yii::$app->cache->flush();
            Setting::Set('cache.class', $form->type);
            Setting::Set('cache.expireTime', $form->expireTime);

            \humhub\libs\DynamicConfig::rewrite();

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved and flushed cache'));

            return $this->redirect(['/admin/setting/caching']);
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
            return $this->redirect(['/admin/setting/statistic']);
        }

        return $this->render('statistic', array('model' => $form));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailing()
    {
        $model = new \humhub\modules\admin\models\forms\MailingDefaultsForm();

        $model->receive_email_activities = Setting::Get('receive_email_activities', 'activity');
        $model->receive_email_notifications = Setting::Get('receive_email_notifications', 'notification');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            Setting::Set('receive_email_activities', $model->receive_email_activities, 'activity');
            Setting::Set('receive_email_notifications', $model->receive_email_notifications, 'notification');

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
        $form->transportType = Setting::Get('mailer.transportType');
        $form->hostname = Setting::Get('mailer.hostname');
        $form->username = Setting::Get('mailer.username');
        if (Setting::Get('mailer.password') != '')
            $form->password = '---invisible---';

        $form->port = Setting::Get('mailer.port');
        $form->encryption = Setting::Get('mailer.encryption');
        $form->allowSelfSignedCerts = Setting::Get('mailer.allowSelfSignedCerts');
        $form->systemEmailAddress = Setting::Get('mailer.systemEmailAddress');
        $form->systemEmailName = Setting::Get('mailer.systemEmailName');


        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->transportType = Setting::Set('mailer.transportType', $form->transportType);
            $form->hostname = Setting::Set('mailer.hostname', $form->hostname);
            $form->username = Setting::Set('mailer.username', $form->username);
            if ($form->password != '---invisible---')
                $form->password = Setting::Set('mailer.password', $form->password);
            $form->port = Setting::Set('mailer.port', $form->port);
            $form->encryption = Setting::Set('mailer.encryption', $form->encryption);
            $form->allowSelfSignedCerts = Setting::Set('mailer.allowSelfSignedCerts', $form->allowSelfSignedCerts);
            $form->systemEmailAddress = Setting::Set('mailer.systemEmailAddress', $form->systemEmailAddress);
            $form->systemEmailName = Setting::Set('mailer.systemEmailName', $form->systemEmailName);

            DynamicConfig::rewrite();

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

            return $this->redirect(['/admin/setting/mailing-server']);
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

                DynamicConfig::rewrite();

                Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
                return $this->redirect(['/admin/setting/design']);
            }
        }

        $themes = [];
        foreach (ThemeHelper::getThemes() as $theme) {
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
        $form->canAdminAlwaysDeleteContent = Setting::Get('canAdminAlwaysDeleteContent', 'content');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->canAdminAlwaysDeleteContent = Setting::Set('canAdminAlwaysDeleteContent', $form->canAdminAlwaysDeleteContent, 'content');
            return $this->redirect(['/admin/setting/security']);
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

            return $this->redirect(['/admin/setting/file']);
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
        $form->enabled = Setting::Get('proxy.enabled');
        $form->server = Setting::Get('proxy.server');
        $form->port = Setting::Get('proxy.port');
        $form->user = Setting::Get('proxy.user');
        $form->password = Setting::Get('proxy.password');
        $form->noproxy = Setting::Get('proxy.noproxy');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Setting::Set('proxy.enabled', $form->enabled);
            Setting::Set('proxy.server', $form->server);
            Setting::Set('proxy.port', $form->port);
            Setting::Set('proxy.user', $form->user);
            Setting::Set('proxy.password', $form->password);
            Setting::Set('proxy.noproxy', $form->noproxy);

            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_ProxyController', 'Saved'));
            return $this->redirect(['/admin/setting/proxy']);
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

            return $this->redirect(['/admin/setting/oembed']);
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
        return $this->redirect(['/admin/setting/oembed']);
    }

    /**
     * Self Test
     */
    public function actionSelfTest()
    {
        return $this->render('selftest', array('checks' => \humhub\libs\SelfTest::getResults(), 'migrate' => \humhub\commands\MigrateController::webMigrateAll()));
    }

}
